<?php

    /**
     * PHY
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@kinopio.net so we can send you a copy immediately.
     *
     */

    namespace PHY\View;

    /**
     * Handles the hierarchy of the DOM and makes sure elements and their
     * children are rendered to the page.
     *
     * @package PHY\View\Layout
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Layout extends \PHY\View\AView
    {

        protected $controller = null;
        protected $design = [];
        protected $blocks = [];
        protected $variables = [];
        protected $rendered = false;

        public function __call($method, $parameters)
        {
            return call_user_func_array([$this->getController(), $method], $parameters);
        }

        public function addVariables($block = '', array $variables = [])
        {
            if (!array_key_exists($block, $this->variables)) {
                $this->variables[$block] = [];
            }
            $this->variables[$block] = array_merge_recursive($this->variables[$block], $variables);
            return $this;
        }

        public function getBlock($label)
        {
            return array_key_exists($label, $this->blocks)
                ? $this->blocks[$label]
                : NULL;
        }

        public function removeBlock($label)
        {
            unset($this->config[$label]);
            return $this;
        }

        public function addTier($tier)
        {
            $this->tier[] = $tier;
        }

        public function getTier()
        {
            return $this->tier;
        }

        public function removeTier($level)
        {
            $tier = array_reverse($this->tier);
            $max = array_search($level, $tier);
            $tier = array_splice($tier, 0, $max);
            $this->tier = array_reverse($tier);
            return $this;
        }

        public function getTierConfig()
        {
            $values = $this->getTier();
            $temp = $this->config;
            foreach ($values as $value) {
                if (!isset($temp[$value])) {
                    return;
                } elseif ($temp) {
                    $temp = $temp[$value];
                }
            }
            return $temp;
        }

        public function addBlock($label, $config)
        {
            $this->config[$label] = $config;
            return $this;
        }

        public function addChild($parent, $child, $variables)
        {
            $this->getBlock($parent);
            $this->addVariables($parent, [
                'children' => [$child => $variables]
            ]);
            return $this;
        }

        public function block($block = '', array $variables = [], $return = true)
        {
            if (!isset($variables['remove']) || !$variables['remove']) {
                if (array_key_exists($block, $this->variables)) {
                    $defined = $this->variables[$block];
                    if (array_key_exists('children', $defined)) {
                        foreach ($defined['children'] as $child => $children) {
                            $variables['children'][$child] = $children;
                        }
                        unset($defined['children']);
                        $variables = array_merge($variables, $defined);
                    } else {
                        $variables = array_merge($variables, $this->variables[$block]);
                    }
                }
                if (array_key_exists('viewClass', $variables) && $variables['viewClass']) {
                    $element = '\PHY\View\\'.str_replace('/', '\\', $variables['viewClass']);
                    \PHY\Event::dispatch('controller/block/before', [
                        'controller' => $this,
                        'element' => $element
                    ]);
                    $element = new $element($block, [
                        'variables' => $variables,
                        'parameters' => $this->getRequest()->get(),
                        'layout' => $this
                        ]);
                    \PHY\Event::dispatch('controller/block/after', [
                        'controller' => $this,
                        'element' => $element
                    ]);
                } else {
                    $element = new Block;
                    $element->setVariables($variables);
                    $element->setLayout($this);
                    $element->setTheme($this->getTheme());
                    $element->setMedium($this->getMedium());
                }
                $this->blocks[$block] = $element;
            }
            if (array_key_exists($block, $this->blocks)) {
                if ($return) {
                    return $this->blocks[$block];
                } else {
                    echo $this->blocks[$block];
                }
                return $this->blocks[$block];
            }
        }

        public function setController(\PHY\Controller $Controller)
        {
            $this->setResource('controller', $Controller);
            return $this;
        }

        public function getController()
        {
            return $this->getResource('controller');
        }

        public function setConfig($config = NULL)
        {
            \PHY\Event::dispatch('controller/config/before', [
                'controller' => $this,
                'config' => &$config
            ]);
            $this->config = $config;
            \PHY\Event::dispatch('controller/config/after', [
                'controller' => $this,
                'config' => &$this->config
            ]);
            return $this;
        }

        public function mergeConfig($config = NULL)
        {
            return $this->_mergeConfig($this->getConfig(), $config);
        }

        public function _mergeConfig($current, $merge)
        {
            if (!is_array($current) || !is_array($merge)) {
                return;
            }
            $config = $current;
            foreach ($current as $key => $value) {
                if (array_key_exists('children', $value)) {
                    foreach ($value['children'] as $child => $children) {
                        $config[$key]['children'] = $this->_mergeConfig($value['children'], $merge);
                    }
                }
                if (array_key_exists($key, $merge)) {
                    $config[$key] = array_merge($config[$key], $merge[$key]);
                }
            }
            return $config;
        }

        public function getConfig()
        {
            return $this->config;
        }

        public function child($child = '')
        {
            $this->addTier($child);
            if (array_key_exists($child, $this->config)) {
                $this->blocks[$child] = $this->block($child, $this->config[$child]);
            }
            $this->removeTier($child);
        }

        public function config($key = NULL, $graceful = false)
        {
            if (!array_key_exists($this->theme, $this->design)) {
                $this->design[$this->theme] = [];
            }
            if (!array_key_exists($key, $this->design[$this->theme])) {
                $file = false;
                $paths = $this->getPath()->getPaths(
                    'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.$this->medium.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json', 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$this->medium.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json', 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json', 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json'
                );
                foreach ($paths as $check) {
                    if (is_file($check)) {
                        $file = $check;
                        break;
                    }
                };
                if (!$file) {
                    if (!$graceful) {
                        throw new Exception('Design "'.$key.'" was not found.', E_USER_WARNING);
                    }
                    return false;
                }
                $FILE = fopen($file, 'r');
                $content = fread($FILE, filesize($file));
                fclose($FILE);
                $content = preg_replace(['#/\*.+?\*/#is'], '', $content);
                $content = @json_decode($content);
                if ($content !== NULL) {
                    $content = (new \PHY\Variable\Obj($content))->toArray();
                    $this->design[$this->theme][$key] = $content;
                } else {
                    if (!$graceful) {
                        throw new Exception('Design "'.$key.'" empty or malformed.', E_USER_WARNING);
                    }
                    return false;
                }
            }

            return $this->design[$this->theme][$key];
        }

        public function toHtml()
        {
            return $this->child('layout');
        }

        public function render()
        {
            echo $this->toHtml();
            $this->rendered = true;
        }

        public function __destruct()
        {
            if (!$this->rendered) {
                $this->render();
            }
            echo implode('', $this->blocks);
        }

    }
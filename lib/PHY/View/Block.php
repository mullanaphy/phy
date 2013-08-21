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
     * Individual view block.
     *
     * @package PHY\View\Block
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Block extends \PHY\View\AView
    {

        protected $name = '';
        protected $children = [];

        public function __construct($name = '', array $variables = [])
        {
            $this->setName($name);
            $this->setVariables($variables);
        }

        public function __call($method, $parameters)
        {
            return call_user_func_array([$this->getLayout(), $method], $parameters);
        }

        public function __toString()
        {
            try {
                return $this->toHtml();
            }
            catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        public function setVariables(array $variables) {
            $this->variables = $variables;
            if(array_key_exists('children', $variables)) {
                foreach($variables['children'] as $child => $config) {
                    $this->setChild($child, $config);
                }
            }
        }

        public function setChild($child, $config)
        {
            $this->children[$child] = new \PHY\View\Block($child, $config);
            return $this;
        }

        public function toHtml()
        {
            $source = $this->getVariable('template');
            if (!$source || $this->getVariable('remove')) {
                return '';
            }
            $file = false;
            $paths = $this->getPath()->getPaths(
                'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.$this->medium.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source), 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$this->medium.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source), 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source), 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source)
            );
            foreach ($paths as $check) {
                if (is_file($check) && is_readable($check)) {
                    $file = $check;
                    break;
                }
            }
            if (!$file) {
                throw new \Exception('Source file "'.$file.'" was not found.');
            }

            ob_start();
            extract($this->variables);
            include $file;
            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        public function toJSON()
        {
            return json_encode($this->toArray());
        }

        public function toArray()
        {
            return [
                'theme' => $this->theme,
                'source' => $this->source,
                'variables' => $this->variables
            ];
        }

        public function setName($name = '')
        {
            $this->name = $name;
            return $this;
        }

        public function getName()
        {
            return $this->name;
        }

    }
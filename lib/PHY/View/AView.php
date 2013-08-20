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
     * Abstract view class. Defines generic methods for various types of views.
     *
     * @package PHY\View\_Abstract
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    abstract class AView
    {

        use \PHY\TResources;

        protected $name = '',
            $theme = 'default',
            $medium = 'www',
            $variables = [];

        /**
         * Method for templates and HTML output.
         */
        abstract public function toHtml();

        /**
         * 
         * @param \PHY\Markup\AMarkup $tag
         * @return \PHY\View\AView
         */
        public function setMarkupBuilder(\PHY\Markup\AMarkup $markup)
        {
            \PHY\Event::dispatch('view/tag/before', [
                'object' => $this,
                'markup' => $markup
            ]);
            if (method_exists($this, 'beforeMarkupBuilder')) {
                $this->beforeMarkupBuilder();
            }
            $this->setResource('_markup_builder', $tag);
            \PHY\Event::dispatch('view/tag/after', [
                'object' => $this,
                'tag' => $markup
            ]);
            if (method_exists($this, 'afterMarkupBuilder')) {
                $this->afterMarkupBuilder();
            }
            return $this;
        }

        /**
         * Return our Markup Builder.
         * 
         * @return \PHY\Markup\AMarkup
         */
        public function getMarkupBuilder()
        {
            if (!$this->hasResource('_markup_builder')) {
                $this->setMarkupBuilder(new \PHY\Markup\HTML5);
            }
            return $this->getResource('_markup_builder');
        }

        /**
         * Alias for \PHY\Markup\AView::getMarkupBuilder()
         *
         * @return \PHY\Markup\AMarkup
         */
        public function tag()
        {
            return $this->getMarkupBuilder();
        }

        /**
         * Dumps layout class into this object.
         *
         * @return \PHY\View
         */
        public function setLayout(\PHY\View\Layout $layout)
        {
            \PHY\Event::dispatch('view/layout/before', [
                'object' => $this,
                'layout' => $layout
            ]);
            if (method_exists($this, 'beforeLayout')) {
                $this->beforeLayout();
            }
            $this->setResource('layout', $layout);
            \PHY\Event::dispatch('view/layout/after', [
                'object' => $this,
                'layout' => $layout
            ]);
            if (method_exists($this, 'afterLayout')) {
                $this->afterLayout();
            }
            return $this;
        }

        /**
         * Get the Layout class.
         *
         * @return \PHY\View\Layout
         */
        public function getLayout()
        {
            if (!$this->hasResource('layout')) {
                $layout = new \PHY\Layout;
                $this->setResource('layout', $layout);
            }
            return $this->getResource('layout');
        }

        /**
         * Set our Request.
         *
         * @param \PHY\Request
         * @return \PHY\View\AView
         */
        public function setRequest(\PHY\Request $request)
        {
            \PHY\Event::dispatch('view/request/before', [
                'view' => $this,
                'request' => $request
            ]);
            $this->setResource('_request', $request);
            \PHY\Event::dispatch('view/request/before', [
                'view' => $this,
                'request' => $request
            ]);
            return $this;
        }

        /**
         * Get our Request. If one hasn't been set, we'll create one from our
         * global variables.
         *
         * @return \PHY\Request
         */
        public function getRequest()
        {
            if (!$this->hasResource('_request')) {
                $this->setRequest(\PHY\Request::createFromGlobal());
            }
            return $this->getResource('_request');
        }

        /**
         * Clean up a string.
         *
         * @param string $string
         * @param int $flags
         * @param string $encoding
         * @param boolean $double_encode
         * @return string
         */
        public function clean($string = '', $flags = ENT_QUOTES, $encoding = 'utf-8', $double_encode = false)
        {
            return htmlentities($string, $flags, $encoding, $double_encode);
        }

        /**
         * Get an appropriate url path.
         *
         * @param string $url
         * @param string $location
         * @return string
         */
        public function url($url = '', $location = false)
        {
            if (!$url) {
                return str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH.'/');
            }

            if (is_array($url)) {
                $parameters = $url;
                $url = array_shift($parameters);
                $url .= '?'.http_build_query($parameters, '', '&amp;');
            }

            if ($location) {
                $path = $this->getPath();
                foreach ($path->getPaths('resources'.DIRECTORY_SEPARATOR.$this->getTheme().DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR.$url, 'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR.$url) as $check => $source) {
                    if (is_readable($check)) {
                        return $source;
                    }
                }
            }

            return str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH.$url);
        }

        public function setTheme($theme = '')
        {
            $this->theme = $theme;
            return $this;
        }

        public function getTheme()
        {
            return $this->theme;
        }

        public function setMedium($medium = '')
        {
            $this->medium = $medium;
            return $this;
        }

        public function getMedium()
        {
            return $this->medium;
        }

        public function setVariables(array $variables = [])
        {
            $this->variables = array_replace($this->variables, $variables);
            return $this;
        }

        public function getVariables()
        {
            return $this->variables;
        }

        public function setVariable($key = '', $value = '')
        {
            $this->variables[$key] = $value;
            return $this;
        }

        public function getVariable($key = '')
        {
            return array_key_exists($key, $this->variables)
                ? $this->variables[$key]
                : null;
        }

        public function hasVariable($key = '')
        {
            return array_key_exists($key, $this->variables);
        }

        public function setTemplate($template = '')
        {
            $this->setVariable('template', $template);
            return $this;
        }

        public function setPath(\PHY\path $path)
        {
            $this->setResource('_path', $path);
            return $this;
        }

        /**
         * Get our path. Grab it from the global registry if one hasn't been
         * injected.
         *
         * @return \PHY\Path
         */
        public function getPath()
        {
            if (!$this->hasResource('_path')) {
                $path = $this->getApp()->getPath();
                $this->setPath($path);
            }
            return $this->getResource('_path');
        }

        /**
         * Parse this out to HTML and return it's string.
         *
         * @return string
         */
        public function __toString()
        {
            return $this->toHtml();
        }

    }
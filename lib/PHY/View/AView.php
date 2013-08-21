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
     * @package PHY\View\AView
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)2
     * @author John Mullanaphy
     */
    abstract class AView implements \PHY\View\IView
    {

        use \PHY\TResources;

        protected $app = null;
        protected $name = '';
        protected $theme = 'default';
        protected $config = [];

        /**
         * 
         * @param \PHY\Markup\AMarkup $tag
         * @return \PHY\View\AView
         */
        public function setMarkup(\PHY\Markup\AMarkup $markup)
        {
            \PHY\Event::dispatch('view/markup/before', [
                'object' => $this,
                'markup' => $markup
            ]);
            $this->setResource('_markup', $tag);
            \PHY\Event::dispatch('view/markup/after', [
                'object' => $this,
                'tag' => $markup
            ]);
            return $this;
        }

        /**
         * Return our Markup Builder.
         * 
         * @return \PHY\Markup\AMarkup
         */
        public function getMarkup()
        {
            if (!$this->hasResource('_markup')) {
                $this->setMarkupBuilder(new \PHY\Markup\HTML5);
            }
            return $this->getResource('_markup');
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
         * @param \PHY\View\ILayout
         * @return \PHY\View
         */
        public function setLayout(\PHY\View\ILayout $layout)
        {
            \PHY\Event::dispatch('view/layout/before', [
                'object' => $this,
                'layout' => $layout
            ]);
            $this->setResource('layout', $layout);
            \PHY\Event::dispatch('view/layout/after', [
                'object' => $this,
                'layout' => $layout
            ]);
            return $this;
        }

        /**
         * Get the Layout class.
         *
         * @return \PHY\View\ILayout
         */
        public function getLayout()
        {
            if (!$this->hasResource('layout')) {
                throw new Exception('Missing a \PHY\View\Layout layout class.');
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
                throw new Exception('Missing a \PHY\Request request class.');
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

        public function setConfig(array $config = [])
        {
            $this->config = array_replace($this->config, $config);
            return $this;
        }

        public function getConfig()
        {
            return $this->config;
        }

        public function setVariable($key = '', $value = '')
        {
            $this->config[$key] = $value;
            return $this;
        }

        public function getVariable($key = '')
        {
            return array_key_exists($key, $this->config)
                ? $this->config[$key]
                : null;
        }

        public function hasVariable($key = '')
        {
            return array_key_exists($key, $this->config);
        }

        public function setTemplate($template = '')
        {
            $this->setVariable('template', $template);
            return $this;
        }

        public function setApp(\PHY\App $app)
        {
            $this->app = $app;
            return $this;
        }

        public function getApp()
        {
            if ($this->app === null) {
                throw new Exception('Missing a \PHY\View\Layout layout class.');
            }
            return $this->app;
        }

        /**
         * Set our path to use.
         *
         * @param \PHY\Path $path
         * @return \PHY\View\AView
         */
        public function setPath(\PHY\Path $path)
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
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

    namespace PHY;

    /**
     * Abstract view class. Defines generic methods for various types of views.
     *
     * @package PHY\View
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class View extends \PHY\View\AView
    {

        protected $name = '',
            $content = [];

        /**
         * Load a new Page view.
         *
         * @param string $name
         * @param array $config
         */
        public function __construct($name = '', array $config = [])
        {
            $this->name = $name;
            if (isset($config['variables'])) {
                $this->setVariables($config['variables']);
            }
            if (isset($config['layout'])) {
                $this->setLayout($config['layout']);
            }
            $this->parse();
        }

        /**
         * Method for templates and HTML output.
         */
        public function structure()
        {
            
        }

        /**
         * Manually parse a page with this class. Use it when you must surpress the __construct.
         */
        protected function parse()
        {
            $event = new \PHY\Event\Item('view/parse/before', [
                'object' => $this
                ]);
            Event::dispatch($event);
            if (method_exists($this, 'beforeParse')) {
                $this->beforeParse();
            }
            $this->structure();
            $event = new \PHY\Event\Item('view/parse/after', [
                'object' => $this
                ]);
            Event::dispatch($event);
            if (method_exists($this, 'afterParse')) {
                $this->afterParse();
            }
        }

        /**
         * Parse this view into HTML.
         *
         * @return string
         */
        public function toHtml()
        {
            return implode('', $this->content);
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

        public function addVariables(array $variables = [])
        {
            $this->getLayout()->block($this->name, $variables, false);
            return $this;
        }

        public function addVariable($key, $value)
        {
            return $this->addVariables([$key => $value]);
        }

    }
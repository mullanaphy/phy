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
     * Contract for view class.
     *
     * @package PHY\View\IView
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    abstract class IView
    {

        /**
         * Method for templates and HTML output.
         */
        public function toHtml();

        /**
         * 
         * @param \PHY\Markup\AMarkup $tag
         * @return \PHY\View\IView
         */
        public function setMarkup(\PHY\Markup\AMarkup $markup);

        /**
         * Return our Markup Builder.
         * 
         * @return \PHY\Markup\AMarkup
         */
        public function getMarkup();

        /**
         * Dumps layout class into this object.
         *
         * @param \PHY\View\ILayout
         * @return \PHY\View
         */
        public function setLayout(\PHY\View\ILayout $layout);

        /**
         * Get the Layout class.
         *
         * @return \PHY\View\ILayout
         */
        public function getLayout();

        /**
         * Clean up a string.
         *
         * @param string $string
         * @param int $flags
         * @param string $encoding
         * @param boolean $double_encode
         * @return string
         */
        public function clean($string = '', $flags = ENT_QUOTES, $encoding = 'utf-8', $double_encode = false);

        /**
         * Get an appropriate url path.
         *
         * @param string $url
         * @param string $location
         * @return string
         */
        public function url($url = '', $location = false);

        public function setTheme($theme = '');

        public function getTheme();

        public function setConfig(array $config = []);

        public function getConfig();

        public function setVariable($key = '', $value = '');

        public function getVariable($key = '');

        public function hasVariable($key = '');

        public function setTemplate($template = '');

        /**
         * Set our path to use.
         *
         * @param \PHY\Path $path
         * @return \PHY\View\IView
         */
        public function setPath(\PHY\Path $path);

        /**
         * Get our path. Grab it from the global registry if one hasn't been
         * injected.
         *
         * @return \PHY\Path
         */
        public function getPath();

        /**
         * Parse this out to HTML and return it's string.
         *
         * @return string
         */
        public function __toString();
    }
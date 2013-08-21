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

    namespace PHY\Controller;

    /**
     * Interface for Controllers.
     *
     * @package PHY\Controller\IController
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    interface IController
    {

        /**
         * Set our global app state.
         *
         * @param \PHY\App
         * @return \PHY\Controller\IController
         */
        public function setApp(\PHY\App $app);

        /**
         * Grab our global app state.
         *
         * @return \PHY\App
         */
        public function getApp();

        /**
         * Method to call.
         *
         * @param string $method
         */
        public function action($method = 'index');

        public function setRequest(\PHY\Request $Request);

        public function getRequest();

        public function setLayout(\PHY\View\Layout $Layout);

        public function getLayout();

        public function index_get();

        public function render();
    }
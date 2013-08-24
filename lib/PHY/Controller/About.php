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
     * About page.
     *
     * @package PHY\Controller\About
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class About extends \PHY\Controller\AController
    {

        /**
         * GET /about
         */
        public function index_get()
        {
            $this->getLayout()->addVariables('head', [
                'title' => 'About'
            ]);
        }

        /**
         * GET /about/friends
         */
        public function friends_get()
        {
            $layout = $this->getLayout();
            $config = $layout->config('about/friends');
            $layout->setConfig($config);
        }

        /**
         * GET /about/resume
         */
        public function resume_get()
        {
            $layout = $this->getLayout();
            $config = $layout->config('about/resume');
            $layout->setConfig($config);
        }

    }
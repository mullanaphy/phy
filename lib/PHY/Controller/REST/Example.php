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

    namespace PHY\Controller\REST;

    /**
     * Example of a REST controller.
     *
     * @package PHY\Controller\REST\Example
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Example extends \PHY\Controller\ARest
    {

        /**
         * An example of a successful request.
         * GET /rest/example/success
         *
         * @return array Response
         */
        protected function success_get()
        {
            return $this->success([
                    'content' => 'Here is the content',
                    'variable' => 'Some arbitrary variable'
                ]);
        }

        /**
         * Example of a failed request.
         * GET /rest/example/error
         *
         * @return array Response
         */
        protected function error_get()
        {
            return $this->error('This failed for some reason', 500);
        }

    }
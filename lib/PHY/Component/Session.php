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

    namespace PHY\Component;

    /**
     * Global Session class.
     *
     * @package PHY\Component\Session
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Session extends \PHY\Component\AComponent
    {

        /**
         * {@inheritDoc}
         */
        public function __construct(\PHY\App $app = null)
        {
            if (!array_key_exists('PHY', $_SESSION)) {
                $_SESSION['PHY'] = [];
            }
            parent::__construct($app);
        }

        /**
         * {@inheritDoc}
         */
        public function delete($key)
        {
            if (array_key_exists($key, $_SESSION['PHY'])) {
                unset($_SESSION['PHY'][$key]);
                return true;
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function get($key)
        {
            return array_key_exists($key, $_SESSION['PHY'])
                ? $_SESSION['PHY'][$key]
                : null;
        }

        /**
         * {@inheritDoc}
         */
        public function has($key)
        {
            return array_key_exists($key, $_SESSION['PHY']);
        }

        /**
         * {@inheritDoc}
         */
        public function set($key, $value)
        {
            if (!is_string($key)) {
                throw new Exception('A session key must be a string.');
            }
            $_SESSION['PHY'][$key] = $value;
            return true;
        }

    }
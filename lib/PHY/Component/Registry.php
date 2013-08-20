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
     * Global Registry class.
     *
     * @package PHY\Component\Registry
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     * @todo Try and minimize this class' importance.
     */
    class Registry extends \PHY\Component\AComponent
    {

        /**
         * {@inheritDoc}
         */
        public function delete($key)
        {
            if (array_key_exists($key, $this->resources)) {
                unset($this->resources[$key]);
                return true;
            }
            return false;
        }

        /**
         * {@inheritDoc}
         */
        public function get($key)
        {
            return array_key_exists($key, $this->resources)
                ? $this->resources[$key]
                : null;
        }

        /**
         * {@inheritDoc}
         */
        public function has($key)
        {
            return array_key_exists($key, $this->resources);
        }

        /**
         * {@inheritDoc}
         */
        public function set($key, $value)
        {
            if (!is_string($key)) {
                throw new Exception('A registry key must be a string.');
            } else if (array_key_exists($key, $this->resources)) {
                throw new Exception('A registry key already exists for "'.$key.'".');
            }
            $this->resources[$key] = $value;
            return true;
        }

    }
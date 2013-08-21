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
     * Resources
     *
     * @package PHY\TResources
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    trait TResources
    {

        protected static $_keys = [];
        protected $resources = [];
        private static $_checks = ['get', 'set', 'has'];

        public function __call($method, $parameters)
        {
            $check = strtolower(substr($method, 0, 3));
            if (in_array($check, self::$_checks)) {
                $resource = $this->_getKey(substr($method, 3));
                $action = $check.'Resource';
                return $this->$action($resource);
            } else if ($check === 'uns' && strtolower(substr($method, 0, 5)) === 'unset') {
                return $this->unsetResource($this->getKey(substr($method, 5)));
            }
        }

        /**
         * See if a resource by the name of $key exists.
         *
         * @param string $key
         * @return bool
         */
        public function hasResource($key)
        {
            return array_key_exists($key, $this->resources);
        }

        /**
         *
         *
         * @param string $key
         * @param mixed $value
         * @return static
         */
        public function setResource($key = '', $value = null)
        {
            $this->resources[$key] = $value;
            return $this;
        }

        /**
         * Returns the value if it exists. If it doesn't then you'll get null
         * back. Note, null can also be a value for a resource, use
         * static::hasResource() if you must be 100% sure.
         *
         * @param string $key
         * @return mixed
         */
        public function getResource($key = '')
        {
            return $this->hasResource($key)
                ? $this->resources[$key]
                : null;
        }

        /**
         * Unset resource $key if it exists.
         *
         * @param string $key
         * @return static
         */
        public function unsetResource($key = '')
        {
            if ($this->hasResource($key)) {
                $this->resources[$key] = null;
                unset($this->resources[$key]);
            }
            return $this;
        }

        /**
         * Clear all the currently loaded resources.
         *
         * @return static
         */
        public function clearResources()
        {
            $this->resources = [];
            return $this;
        }

        /**
         * Convert camelCase to underscores.
         *
         * @param string $name
         * @return string
         * @internal
         */
        private function _getKey($key)
        {
            if (array_key_exists($key, self::$_keys)) {
                return self::$_keys[$key];
            }
            self::$_keys[$key] = (new \PHY\Variable\Str($key))->toUnderscore();
            return self::$_keys[$key];
        }

        public function __destruct()
        {
            foreach ($this->resources as $resource => $value) {
                $this->resources[$resource] = null;
                unset($this->resources[$resource]);
            }
            unset($this->resources);
            gc_collect_cycles();
        }

    }
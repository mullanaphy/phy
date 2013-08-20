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
     * Handles all the request data.
     *
     * @package PHY\Request
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Request
    {

        protected $path = '';
        protected $parameters = [];
        protected $method = 'GET';
        protected $environmentals = [];
        protected static $defaultEnvironmentals = [
            'REQUEST_METHOD' => 'GET'
        ];
        protected $methods = [];
        protected static $_defaultMethods = ['DELETE', 'GET', 'HEADERS', 'PATCH', 'POST', 'PUT'];
        protected $headers = [];
        protected static $_defaultHeaders = [];

        public function __construct($path, array $parameters = [], array $environmentals = [], $headers = [])
        {
            $this->path = $path;
            $this->parameters = $parameters;
            $this->setEnvironmentals($environmentals);
            $this->environmentals = array_replace([], $environmentals);
            $this->headers = array_replace(static::$_defaultHeaders, $headers);
        }

        public static function createFromGlobal()
        {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                case 'HEAD':
                    $parameters = $_GET;
                    break;
                case 'POST':
                    $parameters = array_merge($_GET, $_POST);
                    break;
                default:
                    parse_str(file_get_contents('php://input'), $parameters);
                    array_merge($_GET, $_POST, $parameters);
                    break;
            }
            $path = $_SERVER['REQUEST_URI'];
            return new static($path, $parameters, array_merge($_ENV, $_SERVER));
        }

        /**
         * Return a value from the REQUEST if it exists.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed|null
         */
        public function get($key, $default = null)
        {
            return array_key_exists($key, $this->parameters)
                ? $this->parameters[$key]
                : $default;
        }

        /**
         * Return a value from our environmentals if it exists.
         *
         * @param string $key
         * @param mixed $default
         * @return mixed|null
         */
        public function getEnvironmental($key, $default = null)
        {
            return array_key_exists($key, $this->environmentals)
                ? $this->environmentals[$key]
                : $default;
        }

        public function getEnvironmentals()
        {
            return $this->envirmonetals;
        }

        public function getDefaultEnvironmentals()
        {
            return static::$_defaultEnvironmentals;
        }

        public function getDefaultHeaders()
        {
            return static::$_defaultHeaders;
        }

        public function getDefaultMethods()
        {
            return static::$_defaultMethods;
        }

        /**
         * Return the current request method.
         *
         * @return type string|null
         */
        public function getMethod()
        {
            return $this->method;
        }

        public function getMethods()
        {
            return $this->methods;
        }

        /**
         * Returns an array of allowed request method calls.
         *
         * @return array
         * @static
         */
        public function getParameters()
        {
            return $this->parameters;
        }

        public function isMethod($method)
        {
            return $this->getMethod() === strtoupper($method);
        }

        public function setEnvironmentals(array $environmentals = [])
        {
            $this->environmentals = array_replace($this->getDefaultEnvironmentals(), $environmentals);
            $this->method = strtoupper($this->getEnvironmental('REQUEST_METHOD', 'GET'));
            return $this;
        }

        public function setHeaders(array $headers = [])
        {
            $this->headers = array_replace($this->getDefaultHeaders(), $headers);
            return $this;
        }

        public function setMethods(array $methods = [])
        {
            $this->methods = array_merge($this->getDefaultMethods(), array_map('strtoupper', $methods));
            $this->methods = array_unique($this->methods);
            return $this;
        }

        public function setParameters(array $parameters = [])
        {
            $this->parameters = $parameters;
        }

    }
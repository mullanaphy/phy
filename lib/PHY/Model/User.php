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

    namespace PHY\Model;

    /**
     * The oh so generic user model.
     *
     * @package PHY\Model\User
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class User extends \PHY\Model\Entity
    {

        protected $_password = '';
        protected static $_source = [
            'primary' => [
                'table' => 'user',
                'columns' => [
                    'username' => 'variable',
                    'email' => 'variable',
                    'password' => 'variable',
                    'group' => 'variable',
                    'activity' => 'date',
                    'updated' => 'date',
                    'created' => 'date',
                    'deleted' => 'boolean'
                ],
                'keys' => [
                    'local' => [
                        'username' => 'unique',
                        'email' => 'unique'
                    ]
                ]
            ]
        ];

        public function __sleep()
        {
            $this->data['password'] = null;
            parent::__sleep();
        }

        public function __wakeup()
        {
            parent::__wakeup();
            $this->data['password'] = $this->getDatabase()
                    ->select('password')
                    ->find([static::getPrimaryKey(static::$_source) => $this->id()])
                    ->get()['password'];
        }

        /**
         * Log a User in.
         *
         * @param string $name
         * @param string $password
         * @return self
         */
        public function login($name = null, $password = null)
        {
            $collection = $this->getCollection();
            $collection->filter([
                'or' => [
                    'username' => $name,
                    'email' => $name
                ]
            ]);
            $data = $collection->getFirstItem();
            if ($data) {
                $this->set($data);
            }
            return $this;
        }

        /**
         * Check to see if a password matches what it should.
         *
         * @param type $password
         * @param type $checkPassword
         * @return bool
         * @throws \Exception
         */
        public function checkPassword($password = '', $checkPassword = null)
        {
            if ($checkPassword === null) {
                if (!$this->exists()) {
                    throw new \Exception('No password to check against. Please provide a second parameter or use an initiated User class.');
                } else {
                    $checkPassword = $this->data['password'];
                }
            }
            return (bool)$this->getEncoder()->checkPassword($password, $checkPassword);
        }

        /**
         * Save changes to the database.
         *
         * @return array Response array.
         */
        public function save()
        {
            if ($this->_password) {
                $encoder = $this->getEncoder();
                $this->data['password'] = $encoder->hashPassword($this->_password);
            }
            $return = parent::save();
            $this->_password = '';
            return $return;
        }

        /**
         * If key is set you'll get the value back. Otherwise NULL.
         *
         * @param string $key
         * @return mixed
         */
        public function get($key = '')
        {
            if ($key === 'password') {
                return;
            } else if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            } else if (array_key_exists($key, $this->_additional)) {
                return $this->_additional[$key];
            } else {
                $attributes = $this->getAttributes();
                if (array_key_exists($key, $attributes)) {
                    return $attributes[$key];
                }
            }
        }

        /**
         * Set a key to it's corresponding value if it's allowed
         *
         * @param string $key
         * @param mixed $value
         * @return self
         */
        public function set($key = '', $value = '')
        {
            if ($key === 'password') {
                $this->_different = true;
                $this->_password = $value;
            } else {
                parent::set($key, $value);
            }
            return $this;
        }

        /**
         * Set our password encoder.
         *
         * @param \PHY\Encoder\IEncoder $encoder
         * @return self
         */
        public function setEncoder(\PHY\Encoder\IEncoder $encoder)
        {
            $this->setResource('encoder', $encoder);
            return $this;
        }

        /**
         * Grab our password encoder.
         *
         * @return \PHY\Encoder\IEncoder
         */
        public function getEncoder()
        {
            if (!$this->hasResource('encoder')) {
                $this->setResource('encoder', new \PHY\Encoder\PHPass);
            }
            return $this->getResource('encoder');
        }

    }
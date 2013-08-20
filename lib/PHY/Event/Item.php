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

    namespace PHY\Event;

    /**
     * Our actual event item that gets pushed along.
     *
     * @package PHY\Event\Item
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Item
    {

        protected $name = 'event';
        protected $values = [];
        protected $dispatcher;
        protected $time = 0;
        protected $children = 0;
        protected $triggered = 0;

        public function __construct($name = 'event', array $values = [])
        {
            $this->setName($name);
            $this->setValues($values);
        }

        public function setName($name = 'event')
        {
            $this->name = $name;
            return $this;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setValues($values = [])
        {
            $this->values = $values;
            return $this;
        }

        public function getValues()
        {
            return $this->values;
        }

        public function setDispatcher(\PHY\Event\Dispatcher $dispatcher)
        {
            $this->dispatcher = $dispacher;
            return $this;
        }

        public function getDispatcher()
        {
            return $this->dispatcher;
        }

        public function setTime($time = 0)
        {
            $this->time = $time;
            return $this;
        }

        public function getTime()
        {
            return $this->time;
        }

        public function trigger()
        {
            ++$this->triggered;
            return $this;
        }

        public function getTriggered()
        {
            return $this->triggered;
        }

        public function setChildren($children = 0)
        {
            $this->children = $children;
            return $this;
        }

        public function getChildren()
        {
            return $this->children;
        }

    }
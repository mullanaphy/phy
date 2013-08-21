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

    namespace PHY\Cache;

    /**
     * Singular disk/local caching items. Keeps track of their own expirations and
     * values.
     *
     * @package PHY\Cache\Node
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Node
    {

        protected $label = '',
            $created = '',
            $expires = '',
            $content = '';

        /**
         * Initialize a node.
         *
         * @param string $label
         * @param mixed $content
         * @param int $expires
         */
        public function __construct($label = '', $content = '', $expires = '')
        {
            $this->created = time();
            $this->setLabel($label);
            $this->setExpires($expires);
            $this->setContent($content);
        }

        /**
         * Set a node's label.
         *
         * @param string $label
         * @return \PHY\Cache\Node
         */
        public function setLabel($label = '')
        {
            $this->label = (string)$label;
            return $this;
        }

        /**
         * Set a node's expiration date.
         *
         * @param string $expires
         * @return \PHY\Cache\Node
         */
        public function setExpires($expires = '')
        {
            if (!$expires) {
                return $this;
            } else if (is_numeric($expires)) {
                $this->expires = $this->created + $expires;
            } else {
                $this->expires = strtotime($expires, $this->created);
            }
            return $this;
        }

        /**
         * Set a node's content.
         *
         * @param mixed $content
         * @return \PHY\Cache\Node
         */
        public function setContent($content = '')
        {
            $this->content = $content;
            return $this;
        }

        /**
         * Get a node's label.
         *
         * @return string
         */
        public function getLabel()
        {
            return $this->label;
        }

        /**
         * Get a node's expiration.
         *
         * @return int
         */
        public function getExpires()
        {
            return $this->expires;
        }

        /**
         * Get a node's content.
         *
         * @return type
         */
        public function getContent()
        {
            return $this->content;
        }

        /**
         * See if a node has expired
         *
         * @return bool
         */
        public function hasExpired()
        {
            return $this->expires && $this->expires < time();
        }

    }
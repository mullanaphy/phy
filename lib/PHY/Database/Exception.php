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

    namespace PHY\Database;

    /**
     * For Database related exceptions.
     *
     * @package PHY\Database\Exception
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Exception extends \Exception
    {

        protected $query = '';

        public function __construct($message = '', $code = NULL, $query = '', $previous = NULL)
        {
            $this->query = $query;
            parent::__construct($message);
        }

        /**
         * Get the query that caused the Exception.
         *
         * @return string
         */
        public function getQuery()
        {
            return $this->query;
        }

    }
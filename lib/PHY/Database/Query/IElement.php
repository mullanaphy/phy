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

    namespace PHY\Database\Query;

    /**
     * Contract for all Query elements.
     *
     * @package PHY\Database\Query\IElement
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    interface IElement
    {

        /**
         * Convert our portion of an element block into a query.
         *
         * @return string
         */
        public function __toString();

        /**
         * Set a manager to use with our objects.
         * 
         * @param \PHY\Database\IManager $manager
         * @return \PHY\Database\Query\IElement
         */
        public function setManager(\PHY\Database\IManager $manager);

        /**
         * Return our manager, if none is set then throw an exception.
         *
         * @return \PHY\Database\IManager
         * @throws \PHY\Database\Query\Exception
         */
        public function getManager();

        /**
         * Clean scalars and numbers.
         *
         * @param scalar $scalar
         * @return scalar
         */
        public function clean($scalar);
    }
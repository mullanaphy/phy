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

    namespace PHY\Model\Authorize;

    /**
     * Authorization collection.
     *
     * @package PHY\Model\Authorize\Collcetion
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Collection extends \PHY\Model\ACollection
    {

        protected static $_source = '\PHY\Model\Authorize';

        /**
         * Set up what User to use along side this Collection.
         * If none is provided then Modules will be all willy-nilly.
         *
         * @param \PHY\Model\User $User
         * @return \PHY\Model\Module\Collection
         */
        public function setUser(\PHY\Model\User $User)
        {
            $this->setResource('User', $User);
            return $this;
        }

        /**
         * Get a defined user.
         *
         * @return type
         */
        public function getUser()
        {
            return $this->getResource('User');
        }

    }
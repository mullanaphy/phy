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

    namespace PHY\Database\MySQLi\Query;

    /**
     * Our Select classes should all have the same query building functions.
     *
     * @package PHY\Database\MySQLi\Query\Insert
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Insert extends \PHY\Database\MySQLi\Query\Operation implements \PHY\Database\Query\IInsert
    {

        protected $insert = [];

        /**
         * {@inheritDoc}
         */
        public function execute()
        {
            $schema = $this->getSchema();

            foreach ($this->insert as $key => $value) {

            }
        }

    }
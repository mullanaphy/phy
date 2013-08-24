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
     * Our Select classes should all have the same query building functions.
     *
     * @package PHY\Database\Query\ISelect
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    interface ISelect extends \PHY\Database\Query\IElement
    {

        /**
         * Set a field to compare against.
         *
         * @param string $field
         * @param string $alias
         * @return $this
         */
        public function field($field, $alias = '');

        /**
         * Field to do a count against.
         * 
         * @param string $field
         * @param string $alias
         * @return $this
         */
        public function count($field = '*', $alias = '');

        /**
         * Field to do a min against.
         *
         * @param string $field
         * @param string $alias
         * @return $this
         */
        public function min($field = '_id', $alias = '');

        /**
         * Field to do a max against.
         *
         * @param string $field
         * @param string $alias
         * @return $this
         */
        public function max($field = '_id', $alias = '');

        /**
         * Set a raw field to add.
         * 
         * @param type $raw
         */
        public function raw($raw);
    }
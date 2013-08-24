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
     * Our MySQLi From object.
     *
     * @package PHY\Database\MySQLi\Query\From
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class From extends \PHY\Database\MySQLi\Query\Element implements \PHY\Database\Query\IFrom
    {

        protected $string = '';
        protected $table = [];

        /**
         * {@inheritDoc}
         */
        public function __toString()
        {
            return $this->toString();
        }

        /**
         * {@inheritDoc}
         */
        public function from($table = '', $alias = false)
        {
            $this->string = '';
            if (!is_string($alias)) {
                $alias = 'primary';
            }
            $this->table = [
                $alias => [
                    'table' => $table,
                    'alias' => $alias,
                    'on' => ''
                ]
            ];
            $this->alias = $alias;
            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function innerJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'inner');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function join($type = 'left', $table = '', $alias = false, $mapping = [])
        {
            $this->string = '';
            if (is_array($alias)) {
                $rightAlias = reset($alias);
                $leftAlias = key($alias);
            } else {
                $leftAlias = $this->alias;
                $rightAlias = $alias
                    ? $alias
                    : $table;
            }
            $alias = $rightAlias;
            if ($rightAlias) {
                $rightAlias = '`'.$this->clean($rightAlias).'`';
            }
            if ($leftAlias) {
                $leftAlias = '`'.$this->clean($leftAlias).'`';
            }
            $on = [];
            $mappings = array_slice(func_get_args(), 3);
            foreach ($mappings as $mapping) {
                $ors = [];
                foreach ($mapping as $key => $value) {
                    $ors[] = ($leftAlias
                            ? $leftAlias.'.'
                            : '').$this->clean($key).' = '.($rightAlias
                            ? $rightAlias.'.'
                            : '').$this->clean($value);
                }
                $on[] = implode(' OR ', $ors);
            }
            $this->table[$alias] = [
                'table' => $table,
                'alias' => $alias,
                'type' => $type,
                'on' => implode(' AND ', $on)
            ];

            return $this;
        }

        /**
         * {@inheritDoc}
         */
        public function leftJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'left');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function outerJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'outer');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function rightJoin($table = '', $alias = false, array $mapping = [])
        {
            $parameters = func_get_args();
            array_unshift($parameters, 'right');
            return call_user_func_array([$this, 'join'], $parameters);
        }

        /**
         * {@inheritDoc}
         */
        public function toArray()
        {
            return $this->table;
        }

        /**
         * {@inheritDoc}
         */
        public function toJSON($flags = 0)
        {
            return json_encode($this->toArray(), $flags);
        }

        /**
         * {@inheritDoc}
         */
        public function toString()
        {
            if (!$this->string) {
                $this->string = ' FROM ';
                $tables = $this->table;
                $primary = array_shift($tables);
                $this->string .= '`'.$this->clean($primary['table']).'` '.$this->alias;
                foreach (array_slice($tables, 1) as $alias => $table) {
                    $this->string .= ' '.strtoupper($table['type']).' JOIN `'.$this->clean($table['table']).'`'.$alias.' ON ('.$table['on'].') ';
                }
                $this->string .= ' ';
            }
            return $this->string;
        }

    }
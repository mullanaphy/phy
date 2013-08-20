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

    namespace PHY\Model\DataMapper;

    /**
     * Map our Queries to Entities and vice versa.
     *
     * @package PHY\Model\DataMapper\Item
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Item
    {

        use \PHY\TResources;

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

        public function setModel(\PHY\Model\Entity $model)
        {
            $this->setResource('source', $this->generateSourceFromSchema($model));
            $this->setResource('model', $model);
            return $this;
        }

        public function getModel()
        {
            return $this->getResource('model');
        }

        protected function generateSourceFromSchema(\PHY\Model\Entity $model)
        {
            $schema = $model->getSchema();
            $source = new \stdClass;
            $source->from = $schema['primary']['table'];
            $source->join = [];
            if (count($schema) > 1) {
                foreach (array_slice($schema, 1) as $key => $join) {
                    $source->join[$key] = $join['table'];
                    $source->columns[] = $join['columns'];
                }
            }
            return $source;
        }

        public function getSource()
        {
            if (!$this->hasResource('source')) {
                $this->setResource('source', $this->parseSource($this->getModel()));
            }
            return $this->getResource('source');
        }

        public function setQuery(\PHY\Database\IQuery $query)
        {
            $this->setResource('query', $query);
            return $this;
        }

        public function getQuery()
        {
            if (!$this->hasResource('query')) {
                $this->setResource('query', $this->getDatabase()->newQuery());
            }
            return $this->getResource('query');
        }

        public function setDatabase(\PHY\Database\IDatabase $database)
        {
            $this->setResource('database', $database);
            return $this;
        }

        public function getDatabase()
        {
            if (!$this->hasResource('database')) {
                throw new Exception('Missing a \PHY\Database\IDatabase entity for this item.');
            }
            return $this->getResource('database');
        }

        public function load($id)
        {
            $source = $this->getSource();
            $query = $this->getQuery();

            $query->reset();

            $from = $query->from();
            $from->from($source->from);
            foreach ($source->join as $join) {
                $from->join();
            }

            $where = $query->where();
            $where->where($source->id, $id);

            $query->limit()->limit(1);

            $result = $this->getDatabase()->query($query);
            $this->model->set($result);
        }

        public function save()
        {
            $query = $this->getQuery();
            $query->reset();

            $query->upsert($this->model->toArray());
            $query->table($source->table);
        }

    }
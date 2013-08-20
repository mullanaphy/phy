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

    namespace PHY\Tests\Model\DataMapper\Query\MySQLi;

use \PHY\Model\DataMapper\Query\MySQLi\Select;

    /**
     * Test our MySQLi Select class.
     *
     * @package PHY\Tests\Model\DataMapper\Query\MySQLi\SelectTest
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class SelectTest extends \PHPUnit_Framework_TestCase
    {

        public function testCountStatement()
        {
            $select = new Select;
            $this->assertEquals('COUNT(`id`)', $select->count('id')->getLastExpression());
        }

        public function testCountStatementAlias()
        {
            $select = new Select;
            $this->assertEquals('COUNT(`a`.`id`)', $select->count('id', 'a')->getLastExpression());
        }

        public function testFieldStatement()
        {
            $select = new Select;
            $this->assertEquals('`id`', $select->field('id')->getLastExpression());
        }

        public function testFieldStatementAlias()
        {
            $select = new Select;
            $this->assertEquals('`a`.`id`', $select->field('id', 'a')->getLastExpression());
        }

        public function testMaxStatement()
        {
            $select = new Select;
            $this->assertEquals('MAX(`id`)', $select->max('id')->getLastExpression());
        }

        public function testMaxStatementAlias()
        {
            $select = new Select;
            $this->assertEquals('MAX(`a`.`id`)', $select->max('id', 'a')->getLastExpression());
        }

        public function testMinStatement()
        {
            $select = new Select;
            $this->assertEquals('MIN(`id`)', $select->min('id')->getLastExpression());
        }

        public function testMinStatementAlias()
        {
            $select = new Select;
            $this->assertEquals('MIN(`a`.`id`)', $select->min('id', 'a')->getLastExpression());
        }

        public function testRawStatement()
        {
            $select = new Select;
            $this->assertEquals(' SELECT BANANA RAMA ', $select->raw(' BANANA RAMA ')->getLastExpression());
        }

        public function testToArray()
        {
            $select = new Select;
            $select->field('id');
            $select->field('name');
            $select->count('*', 'posts');
            $this->assertEquals([
                '`id`',
                '`name`',
                'COUNT(`posts`.*)'
                ], $select->toArray());
        }

        public function testToString()
        {
            $select = new Select;
            $select->field('id');
            $select->field('name');
            $select->count('*', 'posts');
            $this->assertEquals(' SELECT `id`, `name`, COUNT(`posts`.*) ', $select->toString());
            $this->assertEquals((string)$select, $select->toString());
        }

        public function testGetLastExpression()
        {
            $select = new Select;
            $select->field('id');
            $this->assertEquals('`id`', $select->getLastExpression());
        }

        public function testToJson()
        {
            $select = new Select;
            $select->field('id');
            $select->field('name');
            $select->count('*', 'posts');
            $this->assertEquals(json_encode([
                    '`id`',
                    '`name`',
                    'COUNT(`posts`.*)'
                    ], JSON_PRETTY_PRINT), $select->toJson(JSON_PRETTY_PRINT));
        }

    }
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
     * Go through the Authorize model and make sure everything is fine.
     *
     * @package PHY\Model\Authorize\Test
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Test extends \PHPUnit_Framework_TestCase
    {

        public function setUp()
        {
            $testAuthorize = \PHY\Model\User::loadByRequest('test/test');
            if ($testAuthorize->exists())
                $testAuthorize->delete(true);
            $testUser = \PHY\Model\User::loadByEmail('test@example.com');
            if ($testUser->exists())
                $testUser->delete(true);
        }

        /**
         * Save a request.
         *
         * @see \PHY\Model\User::save()
         */
        public function testCreate()
        {
            $testAuthorize = new \PHY\Model\Authorize(\PHY\Registry::get('database/localhost'));
            $testAuthorize->request = 'test/test';
            $testAuthorize->allow = 'nobody';
            $testAuthorize->deny = 'nobody';
            $response = $testAuthorize->save();

            $this->assertEquals(200, $response['status']);
            $this->assertGreaterThan(0, $response['response']);
            $this->assertTrue($testAuthorize->isNew());
            $this->assertGreaterThan(0, $testAuthorize->id);
            return $testAuthorize;
        }

        /**
         * Don't save on unchanged data.
         *
         * @depends testCreate
         * @param \PHY\Model\Authorize
         */
        public function testSaveNoDifference(\PHY\Model\Authorize $testAuthorize)
        {
            $success = [
                'status' => 200,
                'response' => 'Nothing is different.'
            ];
            $response = $testAuthorize->save();
            $this->assertSame($success, $response);
            return $testAuthorize;
        }

        /**
         * Save a user successfully.
         *
         * @depends testSaveNoDifference
         * @param \PHY\Model\Authorize
         */
        public function testSave(\PHY\Model\Authorize $testAuthorize)
        {
            $success = ['status' => 204];
            $testAuthorize->allow = 'all';
            $response = $testAuthorize->save();
            $this->assertSame($success, $response);
            return $testAuthorize;
        }

        /**
         * Test setting a User.
         *
         * @depends testSave
         * @param \PHY\Model\Authorize
         */
        public function testSetAndGetAuthorize(\PHY\Model\Authorize $testAuthorize)
        {
            $testUser = new \PHY\Model\User([
                    'id' => 1,
                    'name' => 'John',
                    'group' => 'test'
                ]);
            $testAuthorize->setUser($testUser);

            $this->assertTrue($testAuthorize->getUser() === $testUser);
            return $testAuthorize;
        }

        /**
         * Check a password successfully.
         *
         * @depends testSetAndGetAuthorize
         * @param \PHY\Model\Authorize
         */
        public function testCheckAllow(\PHY\Model\Authorize $testAuthorize)
        {
            $testUser = new \PHY\Model\User([
                    'id' => 1,
                    'name' => 'John',
                    'group' => 'test'
                ]);
            $testAuthorize->setUser($testUser);
            $testAuthorize->allow = 'all';
            $this->assertTrue($testAuthorize->isAllowed());
            $testAuthorize->allow = 'group';
            $testAuthorize->deny = 'all';
            $this->assertTrue($testAuthorize->isAllowed());
            $testAuthorize->allow = 1;
            $this->assertTrue($testAuthorize->isAllowed());
            return $testAuthorize;
        }

        /**
         * Check a password successfully.
         *
         * @depends testCheckAllow
         * @param \PHY\Model\Authorize
         */
        public function testCheckDeny(\PHY\Model\Authorize $testAuthorize)
        {
            $testAuthorize->allow = 'nobody';
            $testAuthorize->deny = 'all';
            $this->assertTrue($testAuthorize->isDenied());
            $testAuthorize->allow = 'all';
            $testAuthorize->deny = 'test';
            $this->assertTrue($testAuthorize->isDenied());
            $testAuthorize->allow = 1;
            $this->assertTrue($testAuthorize->isDenied());
            return $testAuthorize;
        }

        /**
         * See if the exists and deleted functions work.
         *
         * @depends testCheckDeny
         * @param \PHY\Model\Authorize
         */
        public function testExistsAndIsDeleted(\PHY\Model\Authorize $testAuthorize)
        {
            $authorize = new \PHY\Model\Authorize;
            $this->assertFalse($authorize->exists());
            $this->assertTrue($authorize->isDeleted());

            $this->assertTrue($testAuthorize->exists());
            $this->assertFalse($testAuthorize->isDeleted());

            $testUser->deleted = true;
            $this->assertTrue($testAuthorize->exists());
            $this->assertTrue($testAuthorize->isDeleted());
            $testUser->deleted = false;
            return $testAuthorize;
        }

        /**
         *
         * @depends testExistsAndIsDeleted
         * @param \PHY\Model\Authorize
         */
        public function testDelete(\PHY\Model\Authorize $testAuthorize)
        {
            $response = $testAuthorize->delete();
            $this->assertEquals(204, $response['status']);
            $this->assertTrue($testAuthorize->exists());
            $this->assertTrue($testAuthorize->isDeleted());

            $response = $testAuthorize->delete(true);
            $this->assertEquals(204, $response['status']);
            $this->assertTrue($testAuthorize->isDeleted());
            $this->assertFalse($testAuthorize->exists());
        }

    }
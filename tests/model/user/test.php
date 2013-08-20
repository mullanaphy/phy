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

    namespace PHY\Model\User;

    /**
     * Test our User model.
     *
     * @package PHY\Model\User\Test
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Test extends \PHPUnit_Framework_TestCase
    {

        public function setUp()
        {
            $testUser = \PHY\Model\User::loadByEmail('test@example.com');
            if ($testUser->exists())
                $testUser->delete(true);
        }

        /**
         * See if we cannot login with bad data.
         *
         * @param \PHY\Model\User
         */
        public function testLoginPoorData()
        {
            $user = new \PHY\Model\User;
            $user->login('fakedata', 'connor');
            $this->assertFalse($user->exists());

            $user = new \PHY\Model\User;
            $user->login(0, 'fakepassword');
            $this->assertFalse($user->exists());
        }

        /**
         * See if we can login successfully with an email
         *
         */
        public function testLoginEmail()
        {
            $user = new \PHY\Model\User;
            $user->login('mullanaphy@gmail.com', 'password');
            $this->assertTrue($user->exists());

            $user = new \PHY\Model\User;
            $user->login('mullanaphy@gmail.com', 'fakepassword');
            $this->assertFalse($user->exists());
        }

        /**
         * See if we can login successfully with the username
         *
         * @param \PHY\Model\User
         */
        public function testLoginUsername()
        {
            $user = new \PHY\Model\User;
            $user->login('mullanaphy', 'password');
            $this->assertTrue($user->exists());

            $user = new \PHY\Model\User;
            $user->login('mullanaphy', 'fakepassword');
            $this->assertFalse($user->exists());
        }

        /**
         * Load a User instance by phone number.
         *
         * @param \PHY\Model\User
         */
        public function testLoadByUsername()
        {
            $user = \PHY\Model\User::loadByUsername('mullanaphy');
            $this->assertTrue($user->exists());
        }

        /**
         * Load a User instance by email address.
         *
         * @param \PHY\Model\User
         */
        public function testLoadByEmail()
        {
            $user = \PHY\Model\User::loadByEmail('mullanaphy@gmail.com');
            $this->assertTrue($user->exists());
        }

        /**
         * Save a user successfully.
         *
         * @see \PHY\Model\User::save()
         */
        public function testCreate()
        {
            $testUser = new \PHY\Model\User(\PHY\Registry::get('database/localhost'));
            $testUser->set([
                'username' => 'test',
                'email' => 'test@example.com',
                'password' => 'password',
                'name' => 'Test',
                'group' => 'user'
            ]);
            $response = $testUser->save();

            $this->assertEquals(200, $response['status']);
            $this->assertGreaterThan(0, $response['response']);
            $this->assertTrue($testUser->isNew());
            $this->assertGreaterThan(0, $testUser->id);
            return $testUser;
        }

        /**
         * Don't save on unchanged data.
         *
         * @depends testCreate
         * @param \PHY\Model\User
         */
        public function testSaveNoDifference($testUser)
        {
            $success = [
                'status' => 200,
                'response' => 'Nothing is different.'
            ];
            $response = $testUser->save();
            $this->assertSame($success, $response);
            return $testUser;
        }

        /**
         * Save a user successfully.
         *
         * @depends testSaveNoDifference
         * @param \PHY\Model\User
         */
        public function testSave($testUser)
        {
            $success = ['status' => 204];
            $testUser->name = 'John';
            $response = $testUser->save();
            $this->assertSame($success, $response);
            return $testUser;
        }

        /**
         * Check a password successfully.
         *
         * @depends testSave
         * @param \PHY\Model\User
         */
        public function testCheckPassword($testUser)
        {
            $this->assertTrue($testUser->checkPassword('connor'));
            $this->assertFalse($testUser->checkPassword('fakepassword'));
            return $testUser;
        }

        /**
         * See if we can change the password accurately.
         *
         * @depends testIsAdmin
         * @param \PHY\Model\User
         */
        public function testGetAndSetPassword($testUser)
        {
            $testUser->password = 'newpassword';
            $this->assertTrue($testUser->isDifferent());
            $this->assertNull($testUser->password);
            return $testUser;
        }

        /**
         * See if the exists and deleted functions work.
         *
         * @depends testGetAndSetPassword
         * @param \PHY\Model\User
         */
        public function testExistsAndIsDeleted($testUser)
        {
            $user = new \PHY\Model\User;
            $this->assertFalse($user->exists());
            $this->assertTrue($user->isDeleted());

            $this->assertTrue($testUser->exists());
            $this->assertFalse($testUser->isDeleted());

            $testUser->deleted = true;
            $this->assertTrue($testUser->exists());
            $this->assertTrue($testUser->isDeleted());
            $testUser->deleted = false;
            return $testUser;
        }

        /**
         *
         * @depends testGetAndSetReport
         * @param \PHY\Model\User
         */
        public function testDelete($testUser)
        {
            $response = $testUser->delete();
            $this->assertEquals(204, $response['status']);
            $this->assertTrue($testUser->exists());
            $this->assertTrue($testUser->isDeleted());

            $response = $testUser->delete(true);
            $this->assertEquals(204, $response['status']);
            $this->assertTrue($testUser->isDeleted());
            $this->assertFalse($testUser->exists());
        }

    }
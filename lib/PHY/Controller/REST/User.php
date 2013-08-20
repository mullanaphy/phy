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

    namespace PHY\Controller\REST;

    /**
     * Default User REST controller.
     *
     * @package PHY\Controller\REST\User
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class User extends \PHY\Controller\ARest
    {

        protected $user;

        /**
         * Set the local User item to work with.
         */
        protected function afterParameters()
        {
            $app = $this->getApp();
            $user = $app->get('model/user')->load($this->getRequest()->get('id'));
            $session = $app->get('session/user');
            if (!$session->exists()) {
                $this->user = new Model\User;
            } else if ($user->id == $session->id || $session->isAdmin()) {
                $this->user = $user;
            } else {
                $this->user = $session;
            }
        }

        /**
         * GET /user/id/[%id]
         *
         * @return array Response array
         */
        protected function index_get()
        {
            if ($this->user->exists()) {
                return $this->success($this->user->toArray());
            } else {
                return $this->error('User was not found.', 404);
            }
        }

        /**
         * DELETE /user/id/[%id]
         *
         * @return array Response array
         */
        protected function index_delete()
        {
            $app = $this->getApp();
            $authorize = $app->get('model/authorize')->loadByRequest('model/user/delete');
            if (!$authorize->exists()) {
                $authorize->request = 'model/user/delete';
                $authorize->allow = 'admin super-admin '.$this->user->id;
                $authorize->deny = 'all';
                $authorize->save();
            }
            $authorize->setUser($app->getUser());
            if (!$authorize->isAllowed()) {
                return $this->error('You do not have the credentials to delete this user.', 403);
            }
            return $this->user->delete();
        }

        /**
         * POST /user/id/[%id]
         *
         * @return array Response array
         */
        protected function index_post()
        {
            $app = $this->getApp();
            $authorize = $app->get('model/authorize')->loadByRequest('model/user/save');
            if (!$authorize->exists()) {
                $authorize->request = 'model/user/edit';
                $authorize->allow = 'admin super-admin '.$this->user->id;
                $authorize->deny = 'all';
                $authorize->save();
            }
            $authorize->setUser($app->getUser());
            if (!$authorize->isAllowed()) {
                return $this->error('You do not have the credentials to edit this user.', 403);
            }
            foreach ($this->parameters as $key => $value) {
                $this->user->set($key, $value);
            }
            return $this->user->save();
        }

        /**
         * PUT /user/index
         *
         * @return array Response array
         */
        protected function index_put()
        {
            return $this->index_post();
        }

    }
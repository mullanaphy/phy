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
     * Default Authorize REST controller.
     *
     * @package PHY\Controller\REST\Authorize
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Authorize extends \PHY\Controller\ARest
    {

        protected $authorize;

        /**
         * Set the local Authorize item to work with.
         */
        protected function afterParameters()
        {
            $this->authorize = $this->getApp()->get('model/authorize')->load($this->getRequest()->get('id'));
        }

        /**
         * GET /authorize/id/[%id]
         *
         * @return array Response array
         */
        protected function index_get()
        {
            if ($this->authorize->exists()) {
                return $this->success($this->authorize->toArray());
            } else {
                return $this->error('User was not found.', 403);
            }
        }

        /**
         * DELETE /authorize/id/[%id]
         *
         * @return array Response array
         */
        protected function index_delete()
        {
            $app = $this->getApp();
            $authorize = $app->get('model/authorize')->loadByRequest('model/authorize/delete');
            if (!$authorize->exists()) {
                $authorize->request = 'model/authorize/delete';
                $authorize->allow = 'admin super-admin';
                $authorize->deny = 'all';
                $authorize->save();
            }
            $authorize->setUser($app->getUser());
            if (!$authorize->isAllowed()) {
                $this->error('You do not have the credentials to reset this authorize.', 403);
            }
            return $this->authorize->delete(true);
        }

        /**
         * POST /authorize
         *
         * @return array Response array
         */
        protected function index_post()
        {
            $app = $this->getApp();
            $authorize = $app->get('model/authorize')->loadByRequest('model/authorize/save');
            if (!$authorize->exists()):
                $authorize->request = 'model/authorize/save';
                $authorize->allow = 'admin super-admin';
                $authorize->deny = 'all';
                $authorize->save();
            endif;
            $authorize->setUser($app->getUser());
            foreach ($this->parameters as $key => $value)
                $this->authorize->set($key, $value);
            return $this->authorize->save();
        }

        /**
         * PUT /authorize/id/[%id]
         *
         * @return array Response array
         */
        protected function index_put()
        {
            return $this->index_post();
        }

    }
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

    namespace PHY\Controller;

    /**
     * Boilerplate abstract class for Controllers.
     *
     * @package PHY\Controller\AController
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    abstract class AController implements \PHY\Controller\IController
    {

        protected $config = null;
        protected $request = null;
        protected $redirect = null;
        protected $response = null;
        protected $layout = null;
        protected $parsed = false;
        protected static $_design = [];
        protected static $_theme = 'default';

        public function __construct($method = null, $config = null)
        {
            if (is_string($method)) {
                $this->action($method);
            }
            if ($config) {
                $this->getLayout()->setConfig($config);
            }
        }

        public function __destruct()
        {
            if (!$this->parsed) {
                $this->render();
            }
        }

        public function index_get()
        {

        }

        public function action($method = 'index')
        {
            if (method_exists($this, 'beforeAction')) {
                $this->beforeAction();
            }
            $app = $this->getApp();

            $event = new \PHY\Event\Item('controller/action/before', [
                'controller' => $this,
                'method' => $method
                ]);
            \PHY\Event::dispatch($event);
            $method = $event->method;
            $request = $this->getRequest();

            /* See which route we should go with, depending on whether those methods exist or not. */
            $methods = [
                $method.'_'.$request->method(),
                $method.'_get',
                'index_'.$request->method()
            ];
            $method = 'index_get';
            foreach ($methods as $check) {
                if (method_exists($this, $check)) {
                    $method = $check;
                    break;
                }
            }

            /* Check our ACL table to see if this user can view the action/method or not. */
            $check = trim(strtolower(str_replace([__NAMESPACE__, '\\'], ['', '/'], get_class($this))), '/');
            $authorize = $app->get('model/authorize')->loadByRequest($check.'/'.$method);
            $authorize->setUser($app->getUser());
            if (!$authorize->isAllowed()) {
                $this->redirect('unauthorized');
            }
            $authorize->loadByRequest($check);
            $authorize->setUser($app->getUser());
            if (!$authorize->isAllowed()) {
                $this->redirect('unauthorized');
            }

            /* If everything is good, let's call the correct route. */
            $this->$method();
            if (method_exists($this, 'afterAction')) {
                $this->afterAction();
            }
            $event = new \PHY\Event\Item('controller/action/after', [
                'controller' => $this,
                'method' => $method
                ]);
            \PHY\Event::dispatch($event);
        }

        /**
         * Lazy load our Request. If one doesn't exist, we'll create a globally
         * based Request.
         *
         * @return \PHY\Request
         */
        public function getRequest()
        {
            if ($this->request === null) {
                $event = new \PHY\Event\Item('controller/request/before', [
                    'controller' => $this
                    ]);
                \PHY\Event::dispatch($event);
                $this->request = \PHY\Request::createFromGlobal();
                $event = new \PHY\Event\Item('controller/request/after', [
                    'controller' => $this,
                    'request' => $this->request
                    ]);
                \PHY\Event::dispatch($event);
            }
            return $this->request;
        }

        /**
         * Manually set our Request.
         *
         * @param \PHY\Request $request
         * @return \PHY\Controller\AController
         */
        public function setRequest(\PHY\Request $request)
        {
            $this->request = $request;
            return $this;
        }

        /**
         * Lazy load our Response. If one doesn't exist, we'll create a globally
         * based Request.
         *
         * @return \PHY\Response
         */
        public function getResponse()
        {
            if ($this->request === null) {
                $event = new \PHY\Event\Item('controller/response/before', [
                    'controller' => $this
                    ]);
                \PHY\Event::dispatch($event);
                $this->request = new \PHY\Response;
                $event = new \PHY\Event\Item('controller/response/after', [
                    'controller' => $this,
                    'request' => $this->request
                    ]);
                \PHY\Event::dispatch($event);
            }
            return $this->request;
        }

        /**
         * Manually set our response.
         *
         * @param \PHY\Response $response
         * @return \PHY\Controller\AController
         */
        public function setResponse(\PHY\Response $response)
        {
            $this->response = $response;
            return $this;
        }

        /**
         * Create a new Layout if one hasn't been set yet.
         *
         * @return \PHY\View\Layout
         */
        public function getLayout()
        {
            if ($this->layout === null) {
                $event = new \PHY\Event\Item('controller/layout/before', [
                    'controller' => $this
                    ]);
                \PHY\Event::dispatch($event);
                $this->layout = new \PHY\View\Layout;
                $this->layout->setController($this);
                $event = new \PHY\Event\Item('controller/layout/after', [
                    'controller' => $this,
                    'layout' => $this->layout
                    ]);
                \PHY\Event::dispatch($event);
            }
            return $this->layout;
        }

        /**
         * Inject our Layout to use.
         *
         * @param \PHY\View\Layout $Layout
         * @return \PHY\Controller\AController
         */
        public function setLayout(\PHY\View\Layout $Layout)
        {
            $this->layout = $Layout;
            return $this;
        }

        /**
         * Generate a pathed url. Localtion
         *
         * @param string $url
         * @param string $location
         * @return string
         */
        public function url($url = '', $location = false)
        {
            if (!$url) {
                return str_replace($this->getRequest()->getEnvironmental('DOCUMENT_ROOT', ''), '', $this->getApp()->getRootDir().'/');
            }

            if (is_array($url)) {
                $parameters = $url;
                $url = array_shift($parameters);
                $url .= '?'.http_build_query($parameters, '', '&amp;');
            }

            if ($location) {
                $paths = [
                    ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.$this->getLayout()->getTheme().DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR.$url => str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.$this->getLayout()->getTheme().DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR.$url),
                    ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR.$url => str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR.$url)
                ];
                foreach ($paths as $check => $source) {
                    if (is_readable($check)) {
                        return $source;
                    }
                }
            }

            return str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH.$url);
        }

        /**
         * Set a redirect instead of rendering the page.
         *
         * @param string|array $redirect
         * @return \PHY\Response
         */
        public function redirect($redirect = '')
        {
            $response = new \PHY\Response;
            if (is_array($redirect)) {
                $parameters = $redirect;
                $redirect = array_shift($parameters);
                $redirect .= '?'.http_build_query($parameters);
            }
            $response->redirect($redirect);
            return $response;
        }

        public function render()
        {
            $this->parsed = true;
            $response = $this->getResponse();
            $response->addContent($this->getLayout());
            $this->getResponse()->render();
        }

        public function buildBlocks()
        {

        }

    }
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

    namespace PHY;

    /**
     * Core APP class. This holds all global states and pieces everything
     * together.
     *
     * @package PHY\APP
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     * @todo Make this make sense. Try and break up what should be global and what should be in the registry class.
     */
    final class App
    {

        private $namespace = 'default';
        private $components = [];
        private $path = null;
        private $debugger = null;
        private $registry = null;

        /**
         * Return a value from the Registry if it exists.
         *
         * @param string $key
         * @return mixed|null
         */
        public function get($key = null)
        {
            if (is_string($key)) {
                if ($component = $this->parseComponent($key)) {
                    return $component[0]->get($component[1]);
                } else if ($this->hasComponent($key)) {
                    return $this->getComponent($key);
                } else {
                    return $this->getRegistry()->get($key);
                }
            }
        }

        /**
         * Set a Registry value. If the value already exists then it will fail
         * and a warning will be printed if $graceful is false.
         *
         * @param string $key
         * @param mixed $value
         * @param bool $graceful
         * @return type
         * @throws \PHY\Exception
         */
        public function set($key = null, $value = null)
        {
            if (!is_string($key)) {
                throw new Exception('A registry key must be a string.');
            } else if ($component = $this->parseComponent($key)) {
                return $component[0]->set($component[1], $value);
            } else {
                return $this->getRegistry()->set($key, $value);
            }
        }

        /**
         * Delete this registry key if it exists.
         *
         * @param string $key
         * @param bool $graceful
         * @return bool
         */
        public function delete($key = null, $graceful = false)
        {
            if ($component = $this->parseComponent($key)) {
                return $component[0]->delete($component[1]);
            } else {
                return $this->getRegistry()->delete($key);
            }
        }

        /**
         * Check to see if a key exists. Useful if the ::get you might be using can be
         * false\null and you want to make sure that it was set false and not just a null.
         *
         * @param string $key
         * @return bool
         */
        public function has($key = null)
        {
            if ($component = $this->parseComponent($key)) {
                return $component[0]->has($component[1]);
            } else {
                return $this->getRegistry()->has($key);
            }
        }

        /**
         * Set our registry class to use for our App.
         * 
         * @param \PHY\Component\IComponent $registry
         * @return \PHY\App
         */
        public function setRegistry(\PHY\Component\IComponent $registry)
        {
            $this->registry = $registry;
            $this->registry->setApp($this);
            return $this;
        }

        /**
         * Grab our Registry. If one hasn't been defined, we'll start a new one.
         *
         * @return \PHY\Component\Registry
         */
        public function getRegistry()
        {
            if ($this->registry === null) {
                $this->setRegistry(new \PHY\Component\Registry);
            }
            return $this->registry;
        }

        /**
         * Set our Path class to use in our App.
         *
         * @param \PHY\Path $path
         * @return \PHY\APP
         */
        public function setPath(Path $path)
        {
            $this->path = $path;
            return $this;
        }

        /**
         * Return our global Path.
         *
         * @return type
         */
        public function getPath()
        {
            if ($this->path === null) {
                $this->setPath(new Path);
            }
            return $this->path;
        }

        /**
         * Set  our global Debugger.
         *
         * @param \PHY\Debugger $debugger
         */
        public function setDebugger(Debugger $debugger)
        {
            $this->debugger = $debugger;
        }

        /**
         * Grab our global Debugger class.
         *
         * @return \PHY\Debugger
         */
        public function getDebugger()
        {
            if ($this->debugger === null) {
                $this->setDebugger(new Debugger);
            }
            return $this->debugger;
        }

        /**
         * Change which namespace to use.
         *
         * @param string $namespace
         * @return string
         */
        public function setNamespace($namespace = null)
        {
            if ($namespace !== null) {
                $this->namespace = $namespace;
                $this->getRegisry()->setNamespace($namespace);
            }
            return $this->namespace;
        }

        /**
         * Get the currently defined namespace to use.
         *
         * @return string
         */
        public function getNamespace()
        {
            return $this->namespace;
        }

        /**
         * Check for a programmed component. Things like databases, cache, or
         * config files.
         *
         * @param string $component
         * @return \PHY\Registry\Component or (bool)false if Component isn't found
         */
        private function parseComponent($component)
        {
            if (strpos($key, '/') !== false) {
                $key = explode('/');
                $component = array_shift($key);
                $key = implode('/', $key);
            } else {
                $component = $key;
                $key = 'default';
            }
            if ($this->hasComponent($component)) {
                return [$this->getComponent($component), $key];
            } else {
                $className = '\PHY\Component\\'.$component;
                if (class_exists($className)) {
                    $this->addComponent(new $className);
                    return [$this->getComponent($component), $key];
                }
            }
            return false;
        }

        /**
         * Get a compontent if it exists.
         * 
         * @param string $component
         * @return \PHY\Component\IComponent|false
         */
        public function getComponent($key)
        {
            $key = strtolower($key);
            return array_key_exists($key, $this->components)
                ? $this->components[$key]
                : false;
        }

        /**
         * See if a component exists.
         *
         * @param string $component
         * @return boolean
         */
        public function hasComponent($key)
        {
            return array_key_exists(strtolower($key), $this->components);
        }

        /**
         * Add a component to our App.
         *
         * @param string $key
         * @param \PHY\Component\IComponent $component
         * @return \PHY\App
         */
        public function addComponent(\PHY\Component\IComponent $component)
        {
            $this->components[$compontent->getName()] = $component;
            return $this;
        }

        /**
         * Set our user.
         * 
         * @param \PHY\Model\User $user
         */
        public function setUser(\PHY\Model\User $user)
        {
            $this->user = $user;
            return $this;
        }

        /**
         * Get our logged in user.
         * 
         * @return \PHY\Model\User
         */
        public function getUser()
        {
            if ($this->user === null) {
                $this->setUser($this->get('session/user'));
            }
            return $this->user;
        }

        /**
         *
         * @return type
         */
        public function render()
        {

            $file = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__));
            if ($file === '/') {
                $file = '';
            }

            /* Look for a rewrite rule */
            try {
                $rewrite = Model\Rewrite::loadByRequest($path, $request->getMethod());
                if ($rewrite->exists()) {
                    if ($rewrite->isRedirect()) {
                        $response = new \PHY\Response;
                        $response->redirect($rewrite->destination, $rewrite->redirect);
                        $response->pushHeaders();
                        exit;
                    } else {
                        $path = $rewrite->destination;
                    }
                }

                $pathParameters = explode('/', strtolower(trim($path, '/')));
                if (count($pathParameters) >= 2) {
                    $controller = array_shift($pathParameters);
                    $method = array_shift($pathParameters);
                    if (count($pathParameters)) {
                        $parameters = [
                            [],
                            []
                        ];
                        $i = 1;
                        foreach ($pathParameters as $key) {
                            $parameters[$i === 0
                                    ? $i = 1
                                    : $i = 0][] = $key;
                        }
                        if (count($parameters[1]) !== count($parameters[0])) {
                            $parameters[1][] = null;
                        }
                        $request->add(array_combine($parameters[0], $parameters[1]));
                    }
                } else if (count($pathParameters)) {
                    $controller = current($pathParameters);
                    if (!$controller) {
                        $controller = 'index';
                    }
                    $method = 'index';
                } else {
                    $controller = 'index';
                    $method = 'index';
                }

                if (class_exists('\PHY\Controller\\'.$controller)) {
                    $_ = '\PHY\Controller\\'.$controller;
                    $controller = new $_;
                } else {
                    $controller = new Controller\Index;
                }

                $controller->setApp($this);
                $controller->setRequest($request);

                $controller->setLayout(new \PHY\View\Layout('default', $controller.'/'.$method));

                $controller->action($method);
                $response = $controller->render();
            } catch (\PHY\Database\Exception $exception) {
                $this->logException($exception);
                $controller = new Controller\Error;
                $controller->action(500);
                $controller->setMessage('Sorry, yet there was an issue trying to connect to our database. Please try again in a bit');
                $response = $controller->render();
            } catch (\PHY\Exception $exception) {
                $this->logException($exception);
                $controller = new Controller\Error($exception);
                $controller->action(500);
                $controller->setMessage('Sorry, but something happened PHY related. Could have been us or our framework. Looking in to it...');
                $response = $controller->render();
            } catch (\PHY\Exception\HTTP $exception) {
                $this->logException($exception);
                $controller = new Controller\Error($exception);
                $controller->action($exception->getStatusCode());
                $controller->setMessage($exception->getMessage());
                $response = $controller->render();
            } catch (\Exception $exception) {
                $this->logException($exception);
                $controller = new Controller\Error($exception);
                $controller->action(500);
                $controller->setMessage('Seems there was general error. We are checking it out.');
                $response = $controller->render();
            }

            $response->pushHeaders();
            if (!$response->isRedirect()) {
                $response->pushContent();
            }
        }

    }
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

    namespace PHY\Cron;

    /**
     * Tasks to run when called.
     *
     * @package PHY\Cron\Task
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Task
    {

        protected $response = [
            'status' => 404,
            'response' => 'Cron was not found.'
        ];
        protected $settings = [
            'enabled' => false,
            'expr' => '',
            'label' => '',
            'controller' => 'null',
            'method' => '__construct',
            'parameters' => []
        ];

        public function __construct(array $task = [])
        {
            $this->set($task);
        }

        public function set($key = NULL, $value = '')
        {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    if (array_key_exists($k, $this->settings)) {
                        $this->settings[$k] = $v;
                    }
                }
            } elseif (is_string($key) & array_key_exists($key, $this->settings)) {
                $this->settings[$key] = $value;
            } else {
                throw new Exception('Key `'.$key.'` does not exist in Cron\Task.');
            }
            return $this;
        }

        public function get($key = NULL)
        {
            if (array_key_exists($key, $this->settings)) {
                return $this->settings[$key];
            } else {
                throw new Exception('Key `'.$key.'` does not exist in Cron\Task.');
            }
        }

        public function response()
        {
            return $this->response;
        }

        public function run()
        {
            if (!$this->settings['enabled']) {
                $this->response = [
                    'status' => 403,
                    'response' => 'This task is currently disabled. #'.__LINE__
                ];
                return $this->response;
            }
            $Expr = new \PHY\Cron\Expr($this->settings['expr']);
            if (!$Expr->check(time())) {
                $this->response = [
                    'status' => 500,
                    'response' => 'Task not scheduled to run at this time. #'.__LINE__
                ];
                return $this->response;
            }
            $response = call_user_func_array([str_replace('/', '\\', $this->settings['controller']), $this->settings['method']], $this->settings['parameters']);
            switch (gettype($response)) {
                case 'bool':
                    $this->response = [
                        'status' => 200,
                        'response' => (int)$response
                    ];
                    break;
                case 'array':
                    if (array_key_exists('status', $response) && $response['status'] >= 200 && $response < 600) {
                        $this->response = $response;
                    } else {
                        $this->response = [
                            'status' => 200,
                            'response' => $response
                        ];
                    }
                    break;
                default:
                    $this->response = [
                        'status' => 200,
                        'response' => $response
                    ];
            }
            return $this->response;
        }

    }
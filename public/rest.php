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
     * Handles RESTful requests.
     *
     * @package PHY
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     * @todo Turn REST into an actual controller.
     */
    call_user_func(
        function() {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                case 'HEAD':
                    $parameters = $_GET;
                    break;
                case 'POST':
                    $parameters = array_merge($_GET, $_POST);
                    break;
                case 'PUT':
                case 'DELETE':
                    parse_str(file_get_contents('php://input'), $parameters);
                    $parameters = array_merge($_GET, $_POST, $parameters);
                    Request::add($parameters);
                    break;
                default:
                    header('HTTP/1.1 501 '.Registry::get('config/status_code/501'));
                    header('Allow: '.implode(', ', REST::methods()), true, 501);
                    echo 'Unauthorized';
                    exit;
            }

            $parameters['method'] = isset($parameters['method']) && in_array(strtoupper($parameters['method']), REST::methods())
                ? strtoupper($parameters['method'])
                : $_SERVER['REQUEST_METHOD'];

            if (!isset($parameters['controller']) && !isset($parameters['_caller'])) {
                header('HTTP/1.1 404 '.Registry::get('config/status_code/404'));
                header('Content-type: application/json; charset=utf-8');
                echo json_encode('Controller was not provided. #'.__LINE__);
                new REST\Error('null', $parameters['method'], 'Controller was not provided. #'.(__LINE__ - 1), $parameters);
                exit;
            }

            if (isset($parameters['_caller']) && $parameters['_caller'] === 'module') {
                if (isset($parameters['id'])) {
                    $Module = new Module($parameters['id'], $parameters);
                    $run = $Module->run($parameters['method'], $parameters);
                } else {
                    $run = array(
                        'status' => 400,
                        'url' => '/modules',
                        'response' => 'Missing a controller id. #'.__LINE__
                    );
                }
                if ($run['status'] >= 300 || $run['status'] < 200) {
                    new REST\Error('module', $parameters['method'], $run, $parameters);
                }
            } else {
                $REST = new REST($parameters['controller'], $parameters);
                $run = $REST->run(isset($parameters['action'])
                        ? $parameters['action']
                        : strtolower($parameters['method']), $parameters);
                if (!$run) {
                    $run = array(
                        'status' => 404,
                        'response' => 'Action was not found. #'.__LINE__
                    );
                }
                if ($run['status'] >= 300 || $run['status'] < 200) {
                    new REST\Error($parameters['controller'], isset($parameters['action'])
                            ? $parameters['action']
                            : 'get', $run, $parameters);
                }
            }

            # Redirect or display nothing on a 204.
            if (isset($run['status']) && $run['status'] == 204) {
                if (!isset($parameters['_ajax'])) {
                    Cookie::set('xsrf_id', md5(String::random(16)), INT_YEAR);
                    if (isset($run['url'])) {
                        header('Location: '.$run['url']);
                    } else {
                        header('HTTP/1.1 204 '.Registry::get('config/status_code/204'));
                    }
                    exit;
                }
                header('HTTP/1.1 204 '.Registry::get('config/status_code/204'));
                exit;

                # set the status.
            } elseif (!isset($run['status']) || ($run['status'] != 204 && !isset($run['response']))) {
                header('HTTP/1.1 500 '.Registry::get('config/status_code/500'));
                header('Content-type: application/json; charset=utf-8');
                echo json_encode('Missing a status or a response. #'.__LINE__);
                exit;
            } else {
                header('HTTP/1.1 '.$run['status'].' '.Registry::get('config/status_code/'.$run['status']));
            }

            if (isset($parameters['_ajax'])) {
                header('Content-type: application/json; charset=utf-8');
                if (is_array($run['response']) && isset($run['response']['content']) && is_object($run['response']['content']) && preg_match('#Markup|Container|View#i', get_class($run['response']['content']))) {
                    $run['response']['console'] = 'Generation: '.Debug::timer().'; Elements: '.Markup::elements().'; Server: '.$_SERVER['SERVER_ADDR'];
                    $run['response']['content'] = (string)$run['response']['content'];
                    $run['response'] = json_encode($run['response']);
                } elseif (is_object($run['response']) && preg_match('#Markup|Container#i', get_class($run['response']))) {
                    $run['response'] = array(
                        'console' => 'Generation: '.Debug::timer().'; Elements: '.Markup::elements().'; Server: '.$_SERVER['SERVER_ADDR'],
                        'content' => (string)$run['response']
                    );
                    $run['response'] = json_encode($run['response']);
                } elseif (is_object($run['response']) && method_exists($run['response'], '__toString')) {
                    $run['response'] = (string)$run['response'];
                } else {
                    if (is_array($run['response']))
                        $run['response']['console'] = 'Generation: '.Debug::timer().'; Elements: '.Markup::elements().'; Server: '.$_SERVER['SERVER_ADDR'];
                    $run['response'] = json_encode($run['response']);
                }
                echo 'while(1);'.$run['response'];
            } elseif (isset($parameters['_iframe'])) {
                header('HTTP/1.1 '.$run['status'].' '.Registry::get('config/status_code/'.$run['status']));
                header('Content-type: text/html; charset=utf-8');
                echo $run['response'];
            } elseif (isset($parameters['xml'])) {
                header('Content-type:text/xml;charset=utf-8');
                echo $run['response'];
                exit;
            } else {
                header('Content-type: text/javascript; charset=utf-8');
                if (isset($run['response']['content']))
                    $run['response']['content'] = (string)$run['response']['content'];
                $encode = json_encode($run['response']);
                $return = '';
                $indented = 0;
                $string = false;
                for ($i = 0, $count = strlen($encode); $i <= $count; ++$i) {
                    $_ = substr($encode, $i, 1);
                    switch ($_) {
                        case '\\':
                            if ($string)
                                $_ = '';
                            break;
                        case '"':
                            if (!$string) {
                                for ($ident = 0; $ident < $indented; ++$ident) {
                                    $_ = $_;
                                }
                                $string = true;
                            } else {
                                $string = false;
                            }
                            break;
                        case '{':
                        case '[':
                            if ($string) {
                                break;
                            }
                            ++$indented;
                            $_ .= "\n";
                            for ($ident = 0; $ident < $indented; ++$ident) {
                                $_ .= "   ";
                            }
                            break;
                        case '}':
                        case ']':
                            if ($string) {
                                break;
                            }
                            --$indented;
                            for ($ident = 0; $ident < $indented; ++$ident) {
                                $_ = "   ".$_;
                            }
                            $_ = "\n".$_;
                            break;
                        case ',':
                            if ($string) {
                                break;
                            }
                            $_ .= "\n";
                            for ($ident = 0; $ident < $indented; ++$ident) {
                                $_ .= "   ";
                            }
                            break;
                        case ':':
                            if ($string) {
                                break;
                            }
                            $_ .= ' ';
                            break;
                    }
                    $return .= $_;
                }
                echo preg_replace('#"(-?\d+\.?\d*)"#', '$1', $return);
            }
        }
    );
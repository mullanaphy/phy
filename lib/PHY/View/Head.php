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

    namespace PHY\View;

    /**
     * Head block.
     *
     * @package PHY\View\Head
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Head extends \PHY\View
    {

        public function structure()
        {
            $class = get_class($this->getLayout());
            $class = explode('\\', $class);
            $class = array_slice($class, 2)[0];
            $live = false;
            $app = $this->getApp();
            $cache = $app->get('cache');
            $theme = $app->get('registry/site')->theme;
            $key = $_SERVER['DOCUMENT_ROOT'].'/'.$theme.'/'.$class.'/block/core/head';
            $cache->delete($key);
            if (!($files = $cache->get($key))) {
                $_files = $this->getVariable('files');
                $files = [
                    'css' => [],
                    'js' => []
                ];
                $merge = [];
                $defaults = [
                    'css' => [
                        'rel' => 'stylesheet',
                        'type' => 'text/css'
                    ],
                    'js' => [
                        'type' => 'text/javascript'
                    ],
                    'key' => [
                        'css' => 'href',
                        'js' => 'src'
                    ]
                ];
                foreach (array_keys($_files) as $type) {
                    foreach ($_files[$type] as $file) {
                        if (is_array($file) || is_object($file)) {
                            $file = (array)$file;
                            $source = $file[$defaults['key'][$type]];
                            if (strpos($source, '?') !== false) {
                                $source = explode('?', $source)[0];
                            }

                            if (is_file(ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source))) {
                                $source = ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source);
                            } else if (is_file(ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source))) {
                                $source = ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source);
                            } else {
                                continue;
                            }
                            $file[$defaults['key'][$type]] = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $source));
                            $files[$type][] = array_merge($defaults[$type], $file);
                            continue;
                        } else if (substr($file, 0, 4) === 'http' || substr($file, 0, 2) === '//') {
                            $files[$type][] = array_merge($defaults[$type], [
                                $defaults['key'][$type] => $file
                                ]);
                        } else {
                            $source = $file;
                            if (strpos($source, '?') !== false) {
                                $source = explode('?', $source)[0];
                            }
                            if (is_file(ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source))) {
                                $source = ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source);
                            } else if (is_file(ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source))) {
                                $source = ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $source);
                            } else {
                                continue;
                            }
                            $merge[$type][$source] = filemtime($source);
                        }
                    }
                }
                if ($live) {
                    foreach ($merge as $type => $items) {
                        $cached_file = ROOT_PATH.'resources'.DIRECTORY_SEPARATOR.'cached'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.md5(implode(array_keys($items)).implode($items)).'.'.$type;
                        if (!is_file($cached_file)) {
                            $files_content = '';
                            foreach ($items as $item => $time) {
                                $FILE = fopen($item, 'r');
                                $files_content .= fread($FILE, filesize($item));
                                fclose($FILE);
                            }
                            if (strlen($files_content) > 0) {
                                $FILE = fopen($cached_file, 'w');
                                $minifier = '\PHY\Minify\\'.strtoupper($type);
                                fwrite($FILE, $minifier::minify($files_content));
                                fclose($FILE);
                            }
                        }
                        $files[$type][] = array_merge($defaults[$type], [
                            $defaults['key'][$type] => str_replace(DIRECTORY_SEPARATOR, '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $cached_file))
                            ]);
                    }
                    $cache->set($key, $files, time() + 3600);
                } else {
                    foreach ($merge as $type => $items) {
                        foreach ($items as $item => $time) {
                            $files[$type][] = array_merge($defaults[$type], [
                                $defaults['key'][$type] => str_replace(DIRECTORY_SEPARATOR, '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $item))
                                ]);
                        }
                    }
                }
            }
            $event = new \PHY\Event\Item('block/core/head', [
                'files' => $files,
                'xsrf_id' => false
                ]);
            \PHY\Event::dispatch($event);
            $files = $event->files;
            $this->getLayout()->block('head', [
                'template' => 'core/sections/head.phtml',
                'css' => $files['css'],
                'js' => $files['js'],
                'xsrf_id' => $event->xsrf_id,
                'title' => $this->getVariable('title')
                ], false
            );
        }

        /**
         * Add files to the header.
         *
         * @param string [, ...] $files
         * @return \PHY\View\Head
         */
        public function add()
        {
            $files = func_get_args();
            $_files = $this->getVariable('files');
            foreach ($files as $file) {
                if (is_array($file)) {
                    call_user_func_array([$this, 'add'], $file);
                } else {
                    $extension = explode('.', $file);
                    $_files[$extension[count($extension) - 1]][] = $file;
                }
            }
            $this->setVariable('files', $_files);
            return $this;
        }

    }
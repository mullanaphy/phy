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
     * Handles all the response data.
     *
     * @package PHY\Response
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Response
    {

        protected $headers = [];
        protected $content = [];
        protected $layout = null;
        protected $redirect = false;
        protected $redirectStatus = 301;
        protected static $_defaultHeaders = [];

        public function isRedirect()
        {
            return (bool)$this->redirect;
        }

        public function redirect($redirect = false, $redirectStatus = 301)
        {
            $this->redirect = $redirect;
            $this->redirectStatus = $redirectStatus;
            return $this;
        }

        public function renderHeaders()
        {
            if ($this->isRedirect()) {
                header('Location: '.$this->redirect, $this->redirectStatus);
            } else if ($this->hasHeaders()) {
                foreach ($this->getHeaders() as $key => $value) {
                    header($key.': '.$value);
                }
            }
            flush();
        }

        public function hasHeaders()
        {
            return (bool)count($this->headers);
        }

        public function getHeaders()
        {
            return $this->headers;
        }

        public function renderContent()
        {
            if ($this->hasContent()) {
                echo $this->getContent();
            }
        }

        public function hasContent()
        {
            return (bool)count($this->content);
        }

        public function getContent()
        {
            return $this->content;
        }

        public function render()
        {
            $this->renderHeaders();
            $this->renderContent();
        }

    }
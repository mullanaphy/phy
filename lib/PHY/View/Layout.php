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
     * Handles the hierarchy of the DOM and makes sure elements and their
     * children are rendered to the page.
     *
     * @package PHY\View\Layout
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Layout
    {

        protected $controller = null;
        protected $blocks = [];
        protected $variables = [];
        protected $rendered = false;

        public function addBlocks()
        {
            $configs = func_get_args();
            foreach ($configs as $key) {
                $file = false;
                foreach ($this->getController()->getPath()->getPaths(
                    'design'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.$this->medium.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json', 'design'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.$this->medium.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json', 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json', 'phy'.DIRECTORY_SEPARATOR.'design'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$key.'.json'
                ) as $check) {
                    if (is_file($check)) {
                        $file = $check;
                        break;
                    }
                }
                if (!$file) {
                    throw new \PHY\View\Layout\Exception('Cannot load layout config '.$key);
                }
                $FILE = fopen($file, 'r');
                $content = fread($FILE, filesize($file));
                fclose($FILE);
                $content = preg_replace(['#/\*.+?\*/#is'], '', $content);
                $content = json_decode($content);
                $content = (new \PHY\Variable\Obj($content))->toArray();
                foreach ($content as $key => $value) {
                    $this->blocks[$key] = new \PHY\View\Block($key, $value);
                }
            }
        }

        /**
         * Set our controller.
         *
         * @param \PHY\Controller\AController $controller
         * @return \PHY\View\Layout
         */
        public function setController(\PHY\Controller\AController $controller)
        {
            $event = new \PHY\Event\Item('layout/controller/before', [
                'view' => $this,
                'controller' => $controller
                ]);
            \PHY\Event::dispatch($event);
            $this->controller = $controller;
            $event->setName('layout/controller/after');
            \PHY\Event::dispatch($event);
            return $this;
        }

        /**
         * Get our working controller.
         * 
         * @return \PHY\Controller\AController
         */
        public function getController()
        {
            return $this->controller;
        }

        public function toHtml()
        {
            return (string)$this->block('layout');
        }

        public function render()
        {
            return $this->toHtml();
        }

    }
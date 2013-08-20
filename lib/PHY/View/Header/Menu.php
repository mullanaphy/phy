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

    namespace PHY\View\Header;

    /**
     * Header menu links.
     *
     * @package PHY\View\Header\Menu
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Menu extends \PHY\View
    {

        public function structure()
        {
            $links = $this->getVariable('links');
            $root = $this->getRequest()->page();
            $root = explode('/', $root);
            $root = $root[1];
            $authorize = \PHY\Model\Authorize::loadByRequest('controller/admin');
            if (!$authorize->exists()) {
                $authorize->request = 'controller/admin';
                $authorize->allow = 'admin super-admin';
                $authorize->deny = 'all';
                $authorize->save();
            }
            $authorize->setUser($this->getApp()->getUser());
            if ($authorize->isAllowed()) {
                $links[] = [
                    'title' => 'Admin',
                    'url' => 'admin',
                    'attributes' => [
                        'a' => [
                            'class' => 'admin'
                        ]
                    ]
                ];
            }

            $event = new \PHY\Event\Item('block/core/menu', [
                'links' => $links
                ]);
            \PHY\Event::dispatch($event);
            $links = $event->links;
            $this->getLayout()->block('menu', [
                'template' => 'core/sections/header/menu.phtml',
                'links' => $links,
                'root' => $root
                ], false);
        }

    }
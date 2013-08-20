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
     * Class gets called on page initiation.
     *
     * @package PHY\Init
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    final class Init
    {

        public function __construct()
        {
            /*
             * Initiate a path.
             */
            $path = new Path;
            $path->addRoute('root', ROOT_PATH);
            $path->addRoute('base', BASE_PATH);
            Registry::setPath($path);

            /*
             * Set whether we're on a development or production server.
             */
            $development = array_key_exists('DEVELOPMENT', $_SERVER) && $_SERVER['DEVELOPMENT'];

            /*
             * Find our site config.
             */
            $site = Session::get('site');
            if ($site && (array_key_exists('SITE_NAMESPACE', $_SERVER) && array_key_exists('namespace', $site) && $_SERVER['SITE_NAMESPACE'] === $site['namespace'])) {
                $site = new Model\Site($site);
            } else {
                $site = new Model\Site;
                $site->set('namespace', array_key_exists('SITE_NAMESPACE', $_SERVER)
                        ? $_SERVER['SITE_NAMESPACE']
                        : 'default');
                $site->set('development', $development);
            }
            Registry::set('site', $site);
            Registry::setNamespace($site->namespace);
            Session::set('site', $site->toArray());

            /*
             * Register any logged in user.
             */
            if (Session::has('user')) {
                $user = new Model\User(Session::get('user'));
            } else {
                $user = new Model\User;
            }
            Registry::set('user/session', $user);
        }

    }
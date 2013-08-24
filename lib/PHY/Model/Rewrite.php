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

    namespace PHY\Model;

    /**
     * Handle RewriteRules. .htaccess is for suckers stuck on Apache.
     *
     * @package PHY\Model\Rewrite
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Rewrite extends \PHY\Model\Entity
    {

        protected static $_source = [
            'primary' => [
                'table' => 'rewrite',
                'columns' => [
                    'request_method' => 'variable',
                    'request_uri' => 'variable',
                    'destination' => 'variable',
                    'redirect' => 'boolean',
                    'updated' => 'date',
                    'created' => 'date',
                    'deleted' => 'boolean'
                ],
                'id' => 'id'
            ]
        ];

        /**
         * Load a RewriteRule by its Request.
         *
         * @param string $uri
         * @param string $method
         * @return \PHY\Model\Rewrite
         */
        public function loadByRequest($uri, $method = 'GET')
        {
            return $this->load([
                    'uri' => strtolower($uri),
                    'method' => strtoupper($method)
                ]);
        }

    }
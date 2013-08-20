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
     * Get the currently running sites config data and such nots.
     *
     * @package PHY\Model\Site
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Site extends \PHY\Model\Entity
    {

        protected static $_source = [
            'primary' => [
                'table' => 'authorize',
                'columns' => [
                    'theme' => 'variable',
                    'medium' => 'variable',
                    'development' => 'boolean',
                    'updated' => 'date',
                    'created' => 'date',
                    'deleted' => 'boolean'
                ],
                'filler' => [
                    'theme' => 'default',
                    'medium' => 'www',
                    'development' => true,
                    'updated' => '0000-00-00 00:00:00',
                    'created' => '0000-00-00 00:00:00',
                    'deleted' => false
                ]
            ]
        ];

        /**
         * Initiate the Site class.
         *
         * @param array $settings
         * @return \PHY\Model\Site
         */
        public function __construct(array $settings = [])
        {
            $this->__invoke($settings);
        }

    }
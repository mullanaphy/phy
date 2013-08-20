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
     * Generating items.
     *
     * @package PHY\View\Items
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Items extends \PHY\View
    {

        /**
         * Generate the menu.
         */
        public function structure()
        {
            $items = array_keys($this->getVariable('children'));
            $this->getLayout()->block('items', [
                'template' => 'generic/items.phtml',
                'items' => $items
                ], false);
        }

    }
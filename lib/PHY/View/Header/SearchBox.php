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
     * Search box.
     *
     * @package PHY\View\Header\Searchbox
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class SearchBox extends \PHY\View
    {

        public function structure()
        {
            $search = $this->getRequest()->get('q', false);
            $this->getLayout()->block('menu', [
                'template' => 'core/sections/header/searchbox.phtml',
                'search' => $search
                ], false);
        }

    }
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
     * For ACL Authorization.
     *
     * @package PHY\Model\Authorize
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     */
    class Authorize extends \PHY\Model\Entity
    {

        protected static $_source = [
            'primary' => [
                'table' => 'authorize',
                'columns' => [
                    'request' => 'variable',
                    'allow' => [
                        'type' => 'variable',
                        'comment' => 'There is a space at the beginning and end only in Table view. It is for search reasons.'
                    ],
                    'deny' => [
                        'type' => 'variable',
                        'comment' => 'There is a space at the beginning and end only in Table view. It is for search reasons.'
                    ],
                    'updated' => 'date',
                    'created' => 'date',
                    'deleted' => 'boolean'
                ]
            ]
        ];

        /**
         * See if a Request is allowed to be made.
         *
         * @return boolean
         */
        public function isAllowed(\PHY\Model\User $User)
        {
            $allow = explode(' ', $this->data['allow']);
            $deny = explode(' ', $this->data['deny']);

            if ($User->exists()) {
                if (!$User->group) {
                    $User->group = 'all';
                }

                /* If it's root it has full access */
                if ($User->group === 'root') {
                    $allowed = true;
                } else if (in_array($User->id, $allow) && !in_array($User->id, $deny)) {
                    /* See if a User's ID is in the approved list and not in the denied list. */
                    $allowed = true;
                } else if (in_array($User->id, $deny)) {
                    /* If not, see if he's in the denied list only. */
                    $allowed = false;
                } else if (in_array($User->group, $allow) && !in_array($User->group, $deny)) {
                    /* If not, let's see if his group is in the allowed list and it's not in the denied list. */
                    $allowed = true;
                } else if (in_array($User->group, $deny)) {
                    /* If not, let's see if his group is in the denied list. */
                    $allowed = false;
                } else if (in_array('all', $allow) && !in_array('all', $deny)) {
                    /* If not, well let's see if everyone is allowed access and not in the denied list. */
                    $allowed = true;
                } else if (in_array('all', $deny)) {
                    /* If not, we can see if everyone is denied. */
                    $allowed = false;
                } else {
                    /* Finally, if there isn't an explicit DENY all then they should have access. */
                    $allowed = true;
                }
            } else {
                if (in_array('all', $allow) && !in_array('all', $deny)) {
                    /* There's no logged in user, let's see if everyone is allowed access and not in the denied list. */
                    $allowed = true;
                } else if (in_array('all', $deny)) {
                    /* If not, we can see if everyone is denied. */
                    $allowed = false;
                } else {
                    /* Finally, if there isn't an explicit DENY all then they should have access. */
                    $allowed = true;
                }
            }
            return $allowed;
        }

        /**
         * See if a user is denied to do set action.
         *
         * @return bool
         */
        public function isDenied()
        {
            return !$this->isAllowed();
        }

        /**
         * Save changes to the database.
         *
         * @return array Response array.
         */
        public function save()
        {
            if (!$this->isDifferent()) {
                return [
                    'status' => 200,
                    'response' => 'Nothing is different.'
                ];
            }
            $this->set('allow', ' '.$this->get('allow').' ');
            $this->set('deny', ' '.$this->get('deny').' ');
            $save = parent::save();
            $this->set('allow', trim($this->get('allow')));
            $this->set('deny', trim($this->get('deny')));
            return $save;
        }

    }
<?php

    namespace PHY\View;

    /**
     * 
     */
    class Group extends \PHY\View\AView
    {

        public function structure()
        {
            if (isset($this->data['children'])) {
                foreach ($this->data['children'] as $child) {
                    $this->content[] = $this->child($child['children']);
                }
            }
        }

    }
<?php

	namespace PHY\Interfaces\Page;

	/**
	 * Ajax pages must have an ajax method.
	 * 
	 * @category Interfaces
	 * @package Interfacts\Page
	 * @author John Mullanaphy
	 * @return array
	 */
	interface Ajax {
		public function ajax();
	}
<?php

	namespace PHY\Interfaces\Page;

	/**
	 * Ajax pages must have an ajax method.
	 * 
	 * @return array
	 */
	interface Ajax {
		public function ajax();
	}
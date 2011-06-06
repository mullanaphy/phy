<?php

	namespace PHY;

	/**
	 * You can use this to set up a Singleton if you don't want to use the
	 * Registry for whatever reason. Up to you really although I'd suggest using
	 * the Registry instead.
	 * 
	 * @category Singleton
	 * @package Singleton
	 * @author John Mullanaphy
	 */
	final class Singleton {

		private static $_instances = array();

		private function __construct() {
			
		}

		public function __clone() {
			\PHY\Debug::error('Singleton Classes cannot be cloned.',E_USER_ERROR);
		}

		public function instance($Class=false,$id=0) {
			if(!$Class) return false;
			elseif(!isset($this->_instances[$Class][$id])) self::$_instances[$Class][$id] = new $Class($id);

			return self::$_instances[$Class][$id];
		}

	}
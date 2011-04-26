<?php

	final class Singleton {

		private static $_instances = array();

		private function __construct() {
			
		}

		public function __clone() {
			trigger_error('Singleton Classes cannot be cloned',E_USER_ERROR);
		}

		public function instance($Class=false,$id=0) {
			if(!$Class) return false;
			elseif(!isset($this->_instances[$Class][$id])) self::$_instances[$Class][$id] = new $Class($id);

			return self::$_instances[$Class][$id];
		}

	}
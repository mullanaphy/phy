<?php

	namespace PHY;

	final class Registry {

		private $_values = array();

		/**
		 * Registry cannot be initiated.
		 */
		private function __construct() {
			
		}

		/**
		 * Registry cannot be cloned.
		 */
		public function __clone() {
			\PHY\Debug::error('Registry cannot be cloned.',E_USER_ERROR);
		}

		/**
		 * Return a value from the Registry if it exists.
		 * 
		 * @param string $key
		 * @return mixed|NULL
		 */
		static public function get($key=NULL) {
			return is_string($key) && isset(self::$_registry[$key])?self::$_registry[$key]:NULL;
		}

		/**
		 * Set a Registry value. If the value already exists then it will fail
		 * and a warning will be printed if $alert is true.
		 * 
		 * @param string $key
		 * @param mixed $value
		 * @param bool $alert
		 * @return type
		 */
		static public function set($key=NULL,$value=NULL,$alert=true) {
			if(!is_string($key)):
				if($alert) \PHY\Debug::error('A registry key must be a string.',E_USER_WARNING);
				return false;
			elseif(isset(self::$_registry[$key])):
				if($alert) \PHY\Debug::error('A registry key already exists for "'.$key.'".',E_USER_WARNING);
				return false;
			else:
				self::$_registry[$key] = $value;
				return true;
			endif;
		}

		/**
		 * Delete this registry key if it exists.
		 * 
		 * @param string $key
		 * @return bool 
		 */
		static public function delete($key=NULL) {
			if(isset(self::$_registry[$key])):
				unset(self::$_registry[$key]);
				return true;
			endif;
			return false;
		}

		/**
		 * Resets the Registry.
		 */
		static public function reset() {
			self::$_registry = array();
		}

	}
<?php

	namespace PHY;

	final class Cookie {

		private function __construct() {
			
		}

		public function __clone() {
			\PHY\Debug::error('Cannot clone the static class Cookie.',E_USER_ERROR);
		}

		/**
		 *
		 * @param mixed $key If an array is sent then cookies will be set key => value.
		 * @param mixed $value
		 * @param int $expires
		 * @param mixed $domain
		 * @return <type>
		 */
		static public function set($key='',$value=NULL,$expires=NULL,$domain=NULL) {
			# If the headers have already been sent then we must crash out now.
			if(headers_sent() || !$key) return false;

			if(is_array($key)) foreach($key as $k => $v) self::set($k,$v,$value,$expires);

			# If a datetime was sent then strototime it first.
			if($expires && !is_numeric($expires)) $expires = strtotime($expires);

			# Set the cookie.
			setcookie($key,$value,$expires,'/',$domain);
			$_COOKIE[$key] = $value;

			# Return its new value.
			return $_COOKIE[$key];
		}

		/**
		 * Get a value for a cookie. NULL is returned for cookies not found.
		 *
		 * @param string $key Cookie to grab.
		 * @return mixed
		 */
		static public function get($key) {
			return isset($_COOKIE[$key])?$_COOKIE[$key]:NULL;
		}

		/**
		 * Delete an arbitrary number of Cookies.
		 *
		 * @param $key,... Keys to delete out of Cookies.
		 * @return bool
		 */
		static public function delete() {
			# If headers were already sent or if we don't have any arguments return false.
			if(headers_sent() || (count(func_get_args()) === 0)) return false;

			# Iterate through all the values and unset as appropriate.
			foreach(func_get_args() as $key):
				if(isset($_COOKIE[$key])) unset($_COOKIE[$key]);
				setcookie($key,false,time() - 1);
			endforeach;

			return true;
		}

		/**
		 * Clear out all cookies.
		 *
		 * @return bool
		 */
		static public function clear() {
			# Headers sent then crash out.
			if(headers_sent()) return false;

			# Iterate through all cookies and delete each on.
			foreach($_COOKIE as $key => $value) self::delete($key);

			return true;
		}

	}
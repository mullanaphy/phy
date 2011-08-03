<?php

	namespace PHY;

	/**
	 * Registry for storing all important and\or global key => values.
	 * 
	 * Registry::get('config/*'); works slightly different then the rest.
	 * 
	 * @category Registry
	 * @package Registry
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	final class Request {

		static private $_method = NULL,
			$_methods = array('DELETE','GET','HEAD','POST','PUT'),
			$_parameters = array();

		/**
		 * Class cannot be constructed.
		 */
		private function __construct() {
			
		}

		/**
		 * Class cannot be cloned.
		 */
		public function __clone() {
			\PHY\Debug::error('Cannot clone the static class Registry.',E_USER_ERROR);
		}

		/**
		 * Allow shorter calls for parameters.
		 * 
		 * @return mixed|NULL
		 */
		static public function __callStatic($key,$parameters) {
			return call_user_func_array(array('self','get'),$parameters);
		}
		
		/**
		 * Return the number of parameters defined.
		 * 
		 * @return int Count
		 */
		static public function count() {
			if($key === NULL) self::init();
			return count(self::$_parameters);
		}

		/**
		 * Return a value from the Registry if it exists.
		 * 
		 * @param string $key
		 * @return mixed|NULL
		 */
		static public function get($key=NULL,$default=NULL) {
			if($key === NULL) self::init();
			if(self::$_method === NULL) self::init();
			return array_key_exists($key,self::$_parameters)?self::$_parameters[$key]:$default;
		}

		/**
		 * Initiate\parse incoming parameters.
		 * 
		 * @internal
		 * @ignore
		 */
		static private function init() {
			switch($_SERVER['REQUEST_METHOD']):
				case 'GET':
				case 'HEAD':
					self::$_parameters = $_GET;
					break;
				case 'POST':
					self::$_parameters = array_merge($_GET,$_POST);
					break;
				case 'PUT':
				case 'DELETE':
					parse_str(file_get_contents('php://input'),$parameters);
					self::$_parameters = array_merge($_GET,$_POST,$parameters);
					break;
				default:
					parse_str(file_get_contents('php://input'),$_parameters);
					self::$_parameters = array_merge($_GET,$_POST,$parameters);
					break;
			endswitch;
			self::$_method = $_SERVER['REQUEST_METHOD'];
		}

		/**
		 * Return the current request method.
		 * 
		 * @return type string|NULL
		 */
		static public function method() {
			if(self::$_method === NULL) self::init();
			return self::$_method;
		}

		/**
		 * Returns an array of allowed request method calls.
		 *
		 * @return array
		 * @static
		 */
		static public function methods() {
			return self::$_methods;
		}

		/**
		 * Return an array of all parameters.
		 * 
		 * @return array
		 */
		static public function getArray() {
			if(self::$_method === NULL) self::init();
			return self::$_parameters;
		}

		/**
		 * Return an object of all parameters.
		 * 
		 * @return stdClass
		 */
		static public function getObject() {
			if(self::$_method === NULL) self::init();
			return (object)self::$_parameters;
		}

		/**
		 * Return a JSON string of all parameters.
		 * 
		 * @return string JSON
		 */
		static public function getJSON() {
			if(self::$_method === NULL) self::init();
			return json_encode(self::$_parameters);
		}

	}
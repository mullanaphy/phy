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

		static private $method = NULL,
		$parameters = array();

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
		 * Return the number of parameters defined.
		 * 
		 * @return int Count
		 */
		static public function count() {
			if($key === NULL) self::init();
			return count(self::$parameters);
		}

		/**
		 * Return a value from the Registry if it exists.
		 * 
		 * @param string $key
		 * @return mixed|NULL
		 */
		static public function get($key=NULL,$default=NULL) {
			if($key === NULL) self::init();
			if(self::$method === NULL) self::init();
			return array_key_exists($key,self::$parameters)?self::$parameters[$key]:$default;
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
					self::$parameters = $_GET;
					break;
				case 'POST':
					self::$parameters = array_merge($_GET,$_POST);
					break;
				case 'PUT':
				case 'DELETE':
					parse_str(file_get_contents('php://input'),$parameters);
					self::$parameters = array_merge($_GET,$_POST,$parameters);
					break;
				default:
					parse_str(file_get_contents('php://input'),$parameters);
					self::$parameters = array_merge($_GET,$_POST,$parameters);
					break;
			endswitch;
			self::$method = $_SERVER['REQUEST_METHOD'];
		}

		/**
		 * Return the current request method.
		 * 
		 * @return type string|NULL
		 */
		static public function method() {
			if(self::$method === NULL) self::init();
			return self::$method;
		}

		/**
		 * Return an array of all parameters.
		 * 
		 * @return array
		 */
		static public function toArray() {
			if(self::$method === NULL) self::init();
			return self::$parameters;
		}

		/**
		 * Return an object of all parameters.
		 * 
		 * @return stdClass
		 */
		static public function toArray() {
			if(self::$method === NULL) self::init();
			return (object)self::$parameters;
		}

		/**
		 * Return a JSON string of all parameters.
		 * 
		 * @return string JSON
		 */
		static public function toJSON() {
			if(self::$method === NULL) self::init();
			return json_encode(self::$parameters);
		}

	}
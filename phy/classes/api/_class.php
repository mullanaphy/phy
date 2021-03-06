<?php

	namespace PHY;

	/**
	 * API Router.
	 *
	 * @category API
	 * @package API
	 * @final
	 * @author John Mullanaphy
	 */
	final class API {

		private $API = NULL,
		$_action = '',
		$_controller = '',
		$_method = '',
		$_response = array(
			'status' => 404,
			'url' => '/rest.php',
			'response' => 'This API has been uninitiated.'
		);
		private static $_credentials = NULL;

		/**
		 * Runs a RESTful $method.
		 */
		public function __call($function,$parameters) {
			return $this->run($function,isset($parameters[0]) && is_array($parameters[0])?$parameters[0]:array());
		}

		/**
		 * Initiate an API Class.
		 *
		 * @param string $controller Controller to use.
		 * @param array $parameters parameters to send to your Controller.
		 * @return bool|NULL
		 */
		public function __construct($controller=NULL,array $parameters=array()) {
			if(!is_string($controller) || !$controller) return;
			$this->_controller = $controller;
			$Class = $this->_controller;
			$Class = str_replace('/','\\',$Class);
			if(class_exists('\PHY\API\\'.$Class,true)):
				$Class = '\PHY\API\\'.$Class;
				$this->API = new $Class($parameters);
				if($this->API instanceof \PHY\API\_Abstract):
					return true;
				else:
					$this->API = false;
					$this->_response = array(
						'status' => 500,
						'response' => 'This controller class does extend API_Abstract. #'.__LINE__
					);
					return false;
				endif;
			else:
				$Class = '\PHY\\'.$Class;
				$Reflection = new \ReflectionClass($Class);
				if($Reflection->implementsInterface('PHY\API\_Interface')):
					try {
						$this->API = new $Class($parameters);
					}
					catch(Exception $e) {
						return false;
					}
					return true;
				else:
					$this->_response = array(
						'status' => 500,
						'response' => 'This controller class does not implement API_Interface. #'.__LINE__
					);
					return false;
				endif;
			endif;
		}

		/**
		 * Retrieve read only values.
		 *
		 * @return mixed
		 */
		public function __get($key=NULL) {
			if(isset($this->_response[$key])) return $this->_response[$key];
			elseif(in_array($key,array('_action','_controller','_method','_response'))) return $this->{'_'.$key};
		}

		/**
		 * JSON Encodes the last successful response (status and all).
		 *
		 * @return string
		 */
		public function __toString() {
			return json_encode($this->_response);
		}

		/**
		 * Run a method\action.
		 *
		 * @param string $action
		 * @param array $parameters
		 * @return array
		 */
		public function run($action=NULL,array $parameters=array()) {
			if(!is_string($action)) return false;
			$this->_action = $action;
			$this->_method = isset($parameters['method']) && in_array(strtoupper($parameters['method']),Request::methods())?strtoupper($parameters['method']):'GET';
			if(is_object($this->API)):
				if(method_exists(get_class($this->API),'api')) $this->_response = $this->API->run($action,$parameters);
				else $this->_response['response'] = 'Action `'.$action.'` was not found within Controller `'.$this->_controller.'`. #'.__LINE__;
			endif;
			return $this->_response;
		}

		/**
		 * Returns last run action\method.
		 *
		 * @return string
		 */
		public function action() {
			return $this->_action;
		}

		/**
		 * Returns the current controller's name.
		 *
		 * @return string
		 */
		public function controller() {
			return $this->_controller;
		}

		/**
		 * Returns the last response if the last run action failed.
		 *
		 * @return mixed|false
		 */
		public function error() {
			return!self::success($this->_response)?$this->_response['response']:false;
		}

		/**
		 * Returns the last run's method type.
		 *
		 * @return string
		 */
		public function method() {
			return $this->_method;
		}

		/**
		 * Returns the response of the last run call.
		 *
		 * @return mixed
		 */
		public function response() {
			return $this->_response['response'];
		}

		/**
		 * Returns the HTTP status code of the last run call.
		 *
		 * @return int
		 */
		public function status() {
			return $this->_response['status'];
		}

		/**
		 * Can be called statically by providing a $response. Returns a boolean on whether the $response is a success or not.
		 *
		 * @param array $response
		 * @return bool
		 */
		public function success(array $response=array()) {
			if(isset($this) && get_class($this) === __CLASS__) $response = array('status' => $this->_response);
			return isset($response['status']) && $response['status'] >= 200 && $response['status'] < 300;
		}

		/**
		 * Log a user in for more API\REST based actions.
		 *
		 * @param array $parameters STDIN
		 * @static
		 */
		static public function login(array $parameters=array()) {
			self::token($parameters);
			if(self::$_credentials['id']) Cookie::generate('token_id',self::$_credentials['token_id']);
		}

		/**
		 * Single line API runs.
		 *
		 * @param string $Class
		 * @param string|array $method If $method is an array, then $parameters = $method, $method = 'GET';
		 * @param array $parameters
		 * @return array
		 */
		static public function single($Class=NULL,$method='GET',array $parameters=array()) {
			if(!is_string($Class) || !$Class) return array(
					'status' => 400,
					'response' => 'Class not defined. #'.__LINE__
				);
			if(is_array($method)):
				$parameters = $method;
				$method = 'GET';
			endif;
			$API = new \PHY\API($Class,$parameters);
			return $API->run($method);
		}

		/**
		 * Check a user's credentials based on token or username and password.
		 *
		 * @param array $parameters
		 * @return array
		 */
		static public function token(array $parameters=array()) {
			if(self::$_credentials === NULL):
				$user = isset($parameters['user'])?$parameters['user']:NULL;
				$password = isset($parameters['password'])?$parameters['password']:NULL;
				/* login */
				if(self::$_credentials === NULL) self::$_credentials = array(
						'id' => NULL,
						'token_id' => NULL,
						'type' => NULL
					);
				self::$_credentials['admin'] = in_array(self::$_credentials['type'],array('a','z'));
			endif;
			return self::$_credentials;
		}

	}
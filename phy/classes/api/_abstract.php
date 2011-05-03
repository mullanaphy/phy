<?php

	namespace PHY\API;

	/**
	 * All API Classes must be able to handle RESTful methods.
	 *
	 * @category API
	 * @package API_Abstract
	 * @author John Mullanaphy
	 */
	abstract class _Abstract extends \ArrayObject {

		private $_settings = array(
			'MySQL' => NULL,
			'captcha' => NULL,
			'credentials' => array(
				'id' => 0,
				'token_id' => '',
				'type' => 'u'
			),
			'parameters' => array()
			),
		$_null = NULL,
		$_response = array(
			'status' => 404,
			'url' => '/module',
			'response' => 'No data was sent.'
		);

		/**
		 * Will RESTfully run whatever $function is and return a true\false for success.
		 * 
		 * @param string $function
		 * @param array $parameters
		 * @return bool 
		 */
		final public function __call($function,$parameters) {
			$response = $this->run($function,((!is_array($parameters))?$this->parameters:$parameters));
			if(API::success($response)) return $response['status'] == 204?true:$response['response'];
			else return false;
		}

		/**
		 * @param array $parameters STDIN or defined key => values.
		 * @return API_Abstract
		 */
		final public function __construct(array $parameters=array(),$database=NULL) {
			$this->_settings['MySQL'] = is_object($database)?:\PHY\Registry::get('MySQL/default');
			$this->_parameters($parameters);
			$this->_response = array(
				'status' => 200,
				'url' => '/api/'.$this->controller,
				'response' => 'API '.$this->controller.' has been initiated.'
			);
			return $this;
		}

		/**
		 * Return any read only values. Referenced so you can use array gets as well.
		 *
		 * @return mixed
		 */
		final public function &__get($key) {
			if((get_parent_class($this) === __CLASS__) && array_key_exists($key,$this->_settings)) return $this->_settings[$key];
			else return $this->_null;
		}

		/**
		 * See if a readable value exists.
		 */
		final public function __isset($key) {
			return ((get_parent_class($this) === __CLASS__) && array_key_exists($key,$this->_settings));
		}

		/**
		 * Returns a JSON encoded version of an API's last run response.
		 *
		 * @return string.
		 */
		final public function __toString() {
			return json_encode($this->response());
		}

		/**
		 * Only internal sets are allowed.
		 *
		 * @return mixed
		 */
		final public function __set($key,$value=NULL) {
			if(get_parent_class($this) === __CLASS__) return $this->_settings[$key] = $value;
			else return $this->_null = NULL;
		}

		/**
		 * Only internal unsets are allowed.
		 *
		 * @return bool
		 */
		final public function __unset($key) {
			if((get_parent_class($this) === __CLASS__) && array_key_exists($key,$this->_settings)):
				unset($this->_settings[$key]);
				return true;
			else:
				return false;
			endif;
		}

		/**
		 * Return the last response.
		 *
		 * @final
		 * @return mixed
		 */
		final public function response() {
			return $this->_response['response'];
		}

		/**
		 * Returns the last response if the last API call failed.
		 *
		 * @final
		 * @return mixed|false
		 */
		final public function error() {
			return !\PHY\API::success($this->_response['status'])?$this->_response['response']:false;
		}

		/**
		 * Return the HTTP status code for the last run API call.
		 * 
		 * @final
		 * @return int
		 */
		final public function status() {
			return $this->_response['status'];
		}

		/**
		 * Alias for API_*::run. Needed to Class API.
		 */
		final public function api($method=NULL,array $parameters=array()) {
			return $this->run($method,$parameters);
		}

		/**
		 * 
		 * @param string $method
		 * @param array $parameters Optional
		 * @final
		 * @return array array('status' => (int),'response' => (mixed));
		 */
		final public function run($method=NULL,array $parameters=array()) {
			if(!is_string($method)) return array(
					'status' => 404,
					'url' => $this->url,
					'response' => 'No method was provided'
				);

			# Set the new parameters if they exist.
			if($parameters) $this->_parameters($parameters);

			# Append run_ to the front so only outside calls are allowed.
			$run = 'api_'.strtolower($method);

			# Change the method if it makes sense to.
			if(isset($this->parameters['method']) && !in_array(strtoupper($this->parameters['method']),\PHY\API::methods())):
				if(in_array(strtoupper($method),\PHY\API::methods())) $this->parameters['method'] = strtoupper($method);
				elseif(isset($_SERVER['REQUEST_METHOD']) && in_array(strtoupper($_SERVER['REQUEST_METHOD']),\PHY\API::methods())) $this->parameters['method'] = strtoupper($_SERVER['REQUEST_METHOD']);
				else $this->parameters['method'] = 'GET';
			endif;

			# See if there is any security related dodads.
			if(method_exists(get_class($this),'allow')):
				$this->_response = $this->allow(strtolower($method));
				if($this->_response['status'] < 200 || $this->_response['status'] >= 300) return $this->_response;
			endif;

			# If the set action exists, then lets run it.
			if(method_exists(get_class($this),$run)):
				$this->_response = $this->$run();
			else:
				$this->_response = array(
					'status' => 404,
					'url' => $this->url,
					'response' => 'Action was not found. #'.__LINE__
				);
			endif;

			return $this->_response;
		}

		/**
		 * For RESTful -> DELETE.
		 * 
		 * @return array
		 */
		protected function api_delete() {
			return $this->api_get();
		}

		/**
		 * For RESTful -> GET.
		 *
		 * @return array
		 */
		protected function api_get() {
			return array(
				'status' => 404,
				'url' => $this->url,
				'response' => 'Request method '.$_SERVER['REQUEST_METHOD'].' is undefined.'
			);
		}

		/**
		 * For RESTful -> POST.
		 * 
		 * @return array
		 */
		protected function api_post() {
			return $this->run_get();
		}

		/**
		 * For RESTful -> PUT.
		 *
		 * @return array
		 */
		protected function api_put() {
			return $this->api_get();
		}

		/**
		 * Parses parameters and logs a user in.
		 *  
		 * @param array $parameters 
		 * @internal
		 */
		private function _parameters(array $parameters) {
			if(!$this->_settings['credentials']['id']):
				# Authentication related details.
				if(isset($parameters['token_id']) || isset($this->token_id)):
					$parameters['user'] = 'token_id';
					$parameters['password'] = isset($parameters['token_id'])?$parameters['token_id']:$this->token_id;
					unset($parameters['token_id']);
				endif;
				$this->_settings['credentials'] = \PHY\API::token($parameters);
			endif;
			$this->_settings['parameters'] = $parameters;
			if(!isset($this->_settings['parameters']['method']) || !in_array($this->_settings['parameters']['method'],\PHY\API::methods())) $this->_settings['parameters']['method'] = 'GET';
		}

	}
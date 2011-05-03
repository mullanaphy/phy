<?php

	namespace PHY\API;

	/**
	 * Logging API Based errors.
	 *
	 * @category API
	 * @package API_Error
	 * @author John Mullanaphy
	 * @final
	 */
	final class Error {

		/**
		 * Store an API based Error directly into a log.
		 *
		 * @param string $controller
		 * @param string $method
		 * @param mixed $response
		 * @param array $parameters
		 * @return string
		 */
		public function __construct($controller='',$method='',$response='',array $parameters,$database=NULL) {
			$MySQL = is_object($database)?:\PHY\Registry::get('MySQL/default');
			$credentials = \PHY\API::token($parameters);
			if(isset($parameters['password'])) $parameters['password'] = \PHY\String::password($parameters['password']);
			$columns = array(
				'controller' => strtolower($MySQL->clean($controller)),
				'status' => (int)$response['status'],
				'method' => strtolower($method?$MySQL->clean($method):'GET'),
				'response' => $MySQL->real_escape_string(
					(
					(isset($response['response']) && is_array($response['response']))?json_encode($response['response']):(
						(isset($response['response']))?$response['response']:NULL
						)
					)
				),
				'file' => $_SERVER['REQUEST_URI'],
				'parameters' => $MySQL->real_escape_string(
					(
					(is_array($parameters) && count($parameters))?json_encode($parameters):NULL
					)
				),
				'user_ip' => isset($_SERVER['REMOTE_ADDR'])?ip2long($_SERVER['REMOTE_ADDR']):0,
				'server_ip' => ip2long($_SERVER['SERVER_ADDR']),
				'user_id' => $credentials['id']?$credentials['id']:0,
				'created' => date(DATE_TIMESTAMP)
			);
		}

	}
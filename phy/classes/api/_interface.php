<?php

	namespace PHY\API;

	/**
	 * Any API desiring class must implement API_Interface.
	 *
	 * @package API_Interface
	 * @category API
	 * @author John Mullanaphy
	 */
	interface _Interface {

		/**
		 * Must be defined. Must return:
		 *
		 * <code>
		 * <?php
		 * array(
		 * 'status' => 200, # HTTP status code
		 * 'response' => 'The response' # Response, unless status==204.
		 * );
		 * </code>
		 *
		 * @method run
		 * @param string $action
		 * @param array $parameters
		 */
		public function api($action=NULL,array $parameters);
	}
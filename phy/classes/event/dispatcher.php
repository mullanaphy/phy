<?php

	namespace PHY\Event;

	final class Dispatcher {

		private $_settings = array(
			'action' => '',
			'parameters' => array(),
			'recurring' => false
		);

		/**
		 * Create a dispatcher item.
		 * 
		 * @param string $action Method to be called on dispatch.
		 * @param array $parameters Parameters to send along to the method.
		 * @param bool $recurring Set true if you want this to be called for every trigger
		 * @return Dispatcher 
		 */
		public function __construct($action='',$parameters=NULL,$recurring=NULL) {
			$this->action($action);
			$this->parameters($parametters);
			$this->recurring($recurring);
			return $this;
		}

		/**
		 * Get a value for the current dispatcher.
		 * 
		 * @param string $key
		 * @return mixed
		 */
		public function __get($key) {
			if(array_key_exists($key,$this->_settings))
				return $this->_settings[$key];
		}

		/**
		 * Set the dispatcher action.
		 * 
		 * @param string $action Method to be called on dispatch.
		 * @return Dispatcher
		 */
		public function action($action='') {
			if(is_callable($action))
				$this->_settings['function'] = $action;
			return $this;
		}

		/**
		 * Set parameters.
		 *
		 * @param array $parameters Parameters to send along to the method.
		 * @return Dispatcher
		 */
		public function parameters(array $parameters=NULL) {
			if($parameters !== NULL)
				$this->_settings['parameters'] = $parameters;
			return $this;
		}

		/**
		 * Set recurring.
		 * 
		 * @param bool $recurring Set true if you want this to be called for every trigger
		 * @return Dispatcher
		 */
		public function recurring($recurring=NULL) {
			if(is_bool($recurring))
				$this->_settings['recurring'] = (bool)$recurring;
			return $this;
		}

		/**
		 * Dispatch current item.
		 */
		public function dispatch() {
			call_user_func_array($this->_settings['action'],$this->_settings['parameters']);
		}

	}
<?php
	abstract class Comet_Abstract extends ArrayObject {
		const AUTHOR = 'John Mullanaphy';
		const CREATED = '2010-03-22';
		const VERSION = '0.1.0';
		
		private $_settings = array(
				'MySQL' => NULL,
				'credentials' => array(
					'id' => 0,
					'token_id' => '',
					'type' => 'u'
				),
				'parameters' => array()
			),
			$_null = NULL,
			$_stack = array();
		
#####	# Magic methods.
		final public function __construct($parameters=array()) {
			$this->_settings['MySQL'] = Singleton_MySQL::instance();
			if(is_object($parameters)) $this->_settings['parameters'] = Convert::object_to_array($parameters);
			elseif(is_array($parameters)) $this->_settings['parameters'] = $parameters;
			else $this->_settings['parameters'] = array();
		}
		
		final public function &__get($key=NULL) {
			if((get_parent_class($this)===__CLASS__)&&array_key_exists($key,$this->_settings)) return $this->_settings[$key];
			else return $this->_null;
		}
		
		final public function __toString() { return json_encode($this->_stack); }
		
		final public function __set($key,$value=NULL) { if(get_parent_class($this)===__CLASS__) $this->_settings[$key] = $value; }
		
#####	# Final methods.
		final public function stack() { return $this->_stack; }
		
#####	# Run methods.
		public function run($method=NULL,$parameters=NULL) {
			if(!is_string($method)) return array(
				'status' => 404,
				'url' => $this->url,
				'response' => 'No method was provided'
			);
			
			if(!is_array($parameters)) $parameters = &$this->_settings['parameters'];
			
			if(!$this->_settings['credentials']['id']):
				# Authentication related details.
				if(isset($parameters['token_id'])||isset($this->token_id)):
					$parameters['user'] = 'token_id';
					$parameters['password'] = isset($parameters['token_id'])?$parameters['token_id']:$this->token_id;
					unset($parameters['token_id']);
				endif;
				
				# Make sure this user\module has permission.
				$parameters['method'] = ((in_array(strtoupper($method),array('DELETE','GET','POST','PUT')))?strtoupper($method):'GET');
				$this->_settings['credentials'] = API::token($parameters);
			endif;
			
			# Append run_ to the front so only outside calls are allowed.
			$run = 'run_'.strtolower($method);
			
			# See if there is any security related dodads.
			if(method_exists(get_class($this),'allow')):
				$allow = $this->allow(strtolower($method));
				if(!$allow) return;
			endif;
			
			# If the set action exists, then lets run it.
			if(method_exists(get_class($this),$run)) $this->_stack = $this->$run();
			
			return $this->_stack;
		}
	}
?>
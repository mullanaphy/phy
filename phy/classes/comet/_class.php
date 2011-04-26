<?php
	final class Comet {
		const AUTHOR = 'John Mullanaphy';
		const CREATED = '2011-03-22';
		const VERSION = '0.1.0';
		
		private $Comet = NULL,
			$_action = '',
			$_controller = '',
			$_stack = array();
		
#####	# Magic methods.		
		public function __construct($controller=NULL,array $parameters=array()) {
			if($controller===NULL) return;
			$this->_controller = $controller;
			$Class = $this->_controller;
			if(class_exists('Comet_'.$Class)):
				$Class = 'Comet_'.$Class;
				$this->Comet = new $Class($parameters);
			else:
				try { $this->Comet = new $Class($parameters); } catch(Exception $e) { return false; }
			endif;
			return true;
		}
		
		public function __get($key=NULL) {
			if(isset($this->_stack[$key])) return $this->_stack[$key];
			elseif(in_array($key,array('_action','_controller','_stack'))) return $this->{'_'.$key};
		}
		
		public function __toString() { return json_encode($this->_response); }
		
#####	# Actions.
		public function run($action=NULL,array $parameters=array()) {
			if(!is_string($action)) return false;
			$this->_action = $action;
			if(is_object($this->Class)):
				if(method_exists(get_class($this->Class),'comet')) $this->_stack[] = $this->Com->comet($action,$parameters);
				else $this->_stack[] = array(
					'action' => 'alert',
					'message' => 'Action `'.$action.'` was not found within Controller `'.$this->_controller.'`. #'.__LINE__
				);
			else:
				$this->_stack[] = array(
					'action' => 'alert',
					'message' => 'Controller `'.$this->_controller.'` was not found. #'.__LINE__
				);
			endif;
			$commands = '';
			foreach($this->_stack as $key => $command):
				if(isset($command['action'])):
					$action = $command['action'];
					unset($command['action']);
				else:
					$action = 'callback';
				endif;
				$command .= 'comet.'.$action.'('.json_encode($command).');';
				unset($this->_stack[$key]);
			endforeach;
			return $commands;
		}
		
#####	# Gets.
		public function controller() { return $this->_controller; }
		
		public function stack() { return $this->_stack; }
	}
?>
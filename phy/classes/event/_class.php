<?php

	namespace PHY;
	
	final class Event {
		static private $_events = array();
		
		/**
		 * Add an event to trigger list.
		 * 
		 * @param string $event
		 * @param mixed $action
		 * @return bool
		 */
		static public function on($event='',$action='',$recurring=false) {
			if(!is_string($event))
				\PHY\Debug::error('First parameter must be a string.',E_USER_ERROR);
			self::$_events[$event] = array(
				'action' => $action,
				'recurring' => !!$recurring
			);
			return true;
		}
		
		/**
		 * Get a list of events waiting to be triggered.
		 * 
		 * @param mixed $event
		 * @return array 
		 */
		static public function events($event=NULL) {
			if(is_string($event)&&array_key_exists($event,self::$_events))
				return self::$_events[$event];
			else
				return self::$_events;
		}
		
		/**
		 * Dispatch a trigger.
		 * 
		 * @param string $event
		 */
		static public function dispatch($event='') {
			if(!is_string($event))
				\PHY\Debug::error('First parameter must be a string.',E_USER_ERROR);
			if(array_key_exists($event,self::$_events)):
				foreach(self::$_events[$event] as &$function):
					switch(gettype($function['action'])):
						case 'array':
							$call = array_shift($function['action']);
							call_user_func_array($call,$function);
							break;
						case 'object':
							if($function['action'] instanceof \PHY\Dispatcher)
								$function['action']->dispatch();
								if(!$function['recurring']->isRecurring()) unset($function);
							break;
						case 'string':
						default:
							call_user_func($function['action']);
					endswitch;
				endforeach;
			endif;
		}
	}
<?php

	namespace PHY\API;

	/**
	 * For calling locally defined containers via REST for AJAX inserts\updates.
	 *
	 * @category API
	 * @package API\Container
	 * @author John Mullanaphy
	 */
	class Container extends \PHY\API\_Abstract {

		public $tag = NULL;

		protected function api_get() {
			if(isset($this->parameters['_url'])) return $this->run_url();
			elseif(!isset($this->parameters['class'],$this->parameters['container'])) return array(
					'status' => 400,
					'response' => 'Missing information, please make sure you provide `class` and `container`. #'.__LINE__
				);
			$Class = '\PHY\Page\\'.$this->parameters['class'];
			$container = $this->parameters['container'];
			if(!method_exists($Class,$container)) return array(
					'status' => 404,
					'response' => 'Container was not found in the class provided. #'.__LINE__
				);
			try {
				$this->tag = \PHY\Markup::tag();
				$Container = $Class::$container($this->parameters);
				$hash = isset($this->parameters['hash']) && $this->parameters['hash']?$this->parameters['hash']:NULL;
				if(is_array($Container) && isset($Container['status'])):
					if(isset($Container['content']) && md5($Container['content']) === $hash):
						return array('status' => 204);
					else:
						$Container['hash'] = isset($Container['content'])?md5($Container['content']):'';
						return array(
							'status' => 200,
							'response' => $Container
						);
					endif;
				else:
					if($Container === NULL) $return = NULL;
					elseif(isset($this->parameters['values'])) $return = $Container->values();
					else $return = array('content' => (string)$Container);
					if($return === NULL || md5($Container) === $hash):
						return array('status' => 204);
					else:
						$return['hash'] = md5($return['content']);
						return array(
							'status' => 200,
							'response' => $return
						);
					endif;
				endif;
			}
			catch(Exception $e) {
				return array(
					'status' => 404,
					'response' => 'Container was not found in the class provided. #'.__LINE__
				);
			}
		}

		protected function api_url() {
			if(!isset($this->parameters['_url'])) return array(
					'status' => 400,
					'response' => 'Missing information, please make sure you provide a `page`. #'.__LINE__
				);
			if(strpos($this->parameters['_url'],'?') !== false):
				$query_string = explode('?',$this->parameters['_url']);
				$values = explode('/',trim($query_string[0],'/'));
				parse_str($query_string[1],$query_string);
				foreach($query_string as $key => $value) $this->parameters[$key] = $value;
			else:
				$values = explode('/',trim($this->parameters['_url'],'/'));
			endif;
			$this->parameters['parameters'] = $values;
			if(count($values) > 2):
				$this->parameters['page'] = array_shift($values);
				$this->parameters['mode'] = array_shift($values);
				$this->parameters['id'] = array_shift($values);
			elseif(count($values) === 2):
				$this->parameters['page'] = array_shift($values);
				$this->parameters['mode'] = array_shift($values);
			elseif(count($values)):
				$this->parameters['page'] = array_shift($values);
			endif;
			if(strpos($this->parameters['_url'],'?') !== false):
				$query_string = explode('?',$this->parameters['_url']);
				parse_str($query_string[1],$query_string);
				foreach($query_string as $key => $value) $this->parameters[$key] = $value;
			endif;
			if(isset($this->parameters['page'])) $Class = $this->parameters['page'];
			else $Class = array_shift($values);
			$this->tag = PHY\Markup::tag();
			$Class = str_replace('/','\\',$Class);
			if(class_exists('\PHY\Page\\'.$Class,true)):
				$Reflection = new \ReflectionClass('\PHY\Page\\'.$Class);
				if($Reflection->implementsInterface('\PHY\Interfaces\Page\Ajax')):
					$Class = '\PHY\Page\\'.ucfirst($Class);
					$Container = $Class::ajax();
				endif;
			endif;
			$hash = isset($this->parameters['hash']) && $this->parameters['hash']?$this->parameters['hash']:NULL;
			if(is_array($Container) && isset($Container['status'])):
				if(isset($Container['content']) && md5($Container['content']) === $hash):
					return array('status' => 202);
				else:
					$Container['response']['hash'] = isset($Container['response']['content'])?md5(is_array($Container['response']['content'])?join(',',$Container['response']['content']):$Container['response']['content']):'';
					return array(
						'status' => 200,
						'response' => $Container['response']
					);
				endif;
			else:
				if($Container === NULL) $return = NULL;
				elseif(isset($this->parameters['values'])) $return = $Container->values();
				else $return = array('content' => (string)$Container);
				if($return === NULL || md5($Container) === $hash):
					return array('status' => 201);
				else:
					$return['hash'] = md5($return['content']);
					return array(
						'status' => 200,
						'response' => $return
					);
				endif;
			endif;
		}

	}
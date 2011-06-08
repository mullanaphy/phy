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
	final class Registry {

		static private $_configs = array(),
		$_registry = array(),
		$_theme = 'default';

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
		 * Return a value from the Registry if it exists.
		 * 
		 * @param string $key
		 * @return mixed|NULL
		 */
		static public function get($key=NULL,$graceful=false) {
			if(is_string($key)):
				if(substr($key,0,7) === 'config/') return self::config(str_replace('config/','',$key),$graceful);
				else return isset(self::$_registry[$key])?self::$_registry[$key]:NULL;
			endif;
		}

		/**
		 * Set a Registry value. If the value already exists then it will fail
		 * and a warning will be printed if $graceful is false.
		 * 
		 * @param string $key
		 * @param mixed $value
		 * @param bool $graceful
		 * @return type
		 */
		static public function set($key=NULL,$value=NULL,$graceful=false) {
			if(!is_string($key)):
				if(!$graceful) \PHY\Debug::error('A registry key must be a string.',E_USER_WARNING);
				return false;
			elseif(substr($key,0,6) === 'config'):
				if(!$graceful) \PHY\Debug::error('A registry key cannot be declared in the config registry.',E_USER_WARNING);
				return false;
			elseif(isset(self::$_registry[$key])):
				if(!$graceful) \PHY\Debug::error('A registry key already exists for "'.$key.'".',E_USER_WARNING);
				return false;
			else:
				self::$_registry[$key] = $value;
				return true;
			endif;
		}

		/**
		 * Delete this registry key if it exists.
		 * 
		 * @param string $key
		 * @param bool $graceful
		 * @return bool 
		 */
		static public function delete($key=NULL,$graceful=false) {
			if(substr($key,0,6) === 'config'):
				if(!$graceful) \PHY\Debug::error('Cannot delete the config registry.',E_USER_WARNING);
				return false;
			elseif(isset(self::$_registry[$key])):
				unset(self::$_registry[$key]);
				return true;
			endif;
			return false;
		}

		/**
		 * Resets the Registry.
		 */
		static public function reset() {
			self::$_registry = array();
		}

		/**
		 * Lists all of the current resources.
		 */
		static public function resources() {
			$resources = array();
			foreach(self::$_registry as $key => $resource):
				$type = gettype($resource);
				switch(strtolower($type)):
					case 'object':
						$resources[$key] = 'Object '.get_class($resource);
						break;
					case 'array':
						$resources[$key] = 'Array #'.count($array).' keys';
						break;
					case 'string':
						$resources[$key] = 'String '.String::shorten($resource);
						break;
					default:
						$resources[$key] = ucfirst($type).' '.$resource;
				endswitch;
			endforeach;
			ksort($resources);
			return $resources;
		}

		/**
		 * Read config values.
		 *
		 * @staticvar array $configs
		 * @param string $key Path for the desired value.
		 * @param bool $graceful
		 * @return mixed
		 */
		static public function config($key,$graceful=false) {
			$values = explode('/',$key);
			$config = array_shift($values);

			if(!isset(self::$_configs[self::$_theme])) self::$_configs[self::$_theme] = array();

			if(!isset(self::$_configs[self::$_theme][$config])):
				$file = false;
				foreach(array(ROOT_PATH.'/phy/config/'.self::$_theme.'/'.$config.'.json',ROOT_PATH.'/phy/config/default/'.$config.'.json',BASE_PATH.'phy/config/'.self::$_theme.'/'.$config.'.json',BASE_PATH.'phy/config/default/'.$config.'.json') as $check):
					if(is_file($check)):
						$file = $check;
						break;
					endif;
				endforeach;
				if(!$file):
					if(!$graceful) \PHY\Debug::error('Config "'.$config.'" was not found.');
					return;
				endif;
				$FILE = fopen($file,'r');
				$content = fread($FILE,filesize($file));
				fclose($FILE);
				$content = preg_replace(array('#//.+?\n#is','#/\*.+?\*/#is'),'',$content);
				$content = json_decode($content);
				if($content !== NULL):
					self::$_configs[self::$_theme][$config] = \PHY\Convert::object_to_array($content);
				else:
					if(!$graceful) \PHY\Debug::error('Config "'.$config.'" empty or malformed.');
					return;
				endif;
			endif;

			if($values):
				$temp = self::$_configs[self::$_theme][$config];
				foreach($values as $value):
					if(!isset($temp[$value])) return;
					elseif($temp) $temp = $temp[$value];
				endforeach;
				return $temp;
			else:
				return self::$_configs[self::$_theme][$config];
			endif;
		}

		static public function theme($theme=NULL) {
			if($theme !== NULL):
				if(is_dir(ROOT_PATH.'/phy/config/'.$theme)) self::$_theme = $theme;
				elseif(is_dir(BASE_PATH.'phy/config/'.$theme)) self::$_theme = $theme;
				else self::$_theme = 'default';
			endif;
			return self::$_theme;
		}

	}
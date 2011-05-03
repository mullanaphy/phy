<?php

	namespace PHY;

	/**
	 * Bootstrapping\Core functions.
	 * 
	 * @category PHY
	 * @package PHY_Core
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	final class Core {
		const AUTHOR = 'John Mullanaphy';
		const VERSION = 0.1;

		static private $_configs = array();

		/**
		 *
		 * @param string $Class
		 * @static
		 * @return bool
		 */
		static public function init($Class) {
			$Class = strtolower($Class);
			$path = explode('\\',trim($Class,'\\'));
			
			$namespace = array_shift($path);
			if($namespace !== strtolower(__NAMESPACE__)):
				var_dump($path);
				throw new \Exception('Looking for '.__NAMESPACE__.' namespace. This must belong to someone else. #'.__LINE__);
			endif;

			if(in_array($Class,array_map('strtolower',array_map('strtolower',get_declared_classes())))) return true;

			if($path):
				$dir = '';
				for($i = 0,$count = count($path); $i < $count; ++$i):
					$dir .= '/'.$path[$i];
					if(is_file(BASE_PATH.'phy/classes'.$dir.'/_interface.php')) require_once BASE_PATH.'phy/classes'.$dir.'/_interface.php';
					if(is_file(BASE_PATH.'phy/classes'.$dir.'/_abstract.php')) require_once BASE_PATH.'phy/classes'.$dir.'/_abstract.php';
				endfor;
			endif;

			$path = join('/',$path);

			if(is_file(BASE_PATH.'phy/classes/'.$path.'/_interface.php')) require_once BASE_PATH.'phy/classes/'.$path.'/_interface.php';
			if(is_file(BASE_PATH.'phy/classes/'.$path.'/_abstract.php')) require_once BASE_PATH.'phy/classes/'.$path.'/_abstract.php';
			if(is_file(BASE_PATH.'phy/classes/'.$path.'/_class.php')):
				require_once BASE_PATH.'phy/classes/'.$path.'/_class.php';
			elseif(file_exists(BASE_PATH.'phy/classes/'.$path.'.php')):
				require_once BASE_PATH.'phy/classes/'.$path.'.php';
			elseif(!in_array($Class,array_map('strtolower',get_declared_classes()))):
				\PHY\Debug::stack();
				\PHY\Debug::error('Could not find Class "'.$path.'". #'.__LINE__,E_USER_WARNING);
				return false;
			endif;
		}

		/**
		 * Bootstrapping.
		 * 
		 * @param type $Class 
		 * @return Class
		 */
		static public function load($Class='') {
			if(!is_string($Class) || !$Class) Debug::warning('Attempting to load an invalid Class Name',E_USER_WARNING);
			$Class = strtolower(substr($Class,0,4)) === '\PHY\\'?:'\PHY\\'.$Class;
			if(!class_exists($Class)) $exists = self::init($Class);
			else $exists = true;
			$Class = $exists?new $Class:new \stdClass;
			return $Class;
		}

		/**
		 * Read config values.
		 *
		 * @staticvar array $configs
		 * @param string $key Path for the desired value.
		 * @return mixed
		 */
		static public function config($key) {
			$values = explode('/',$key);
			$config = array_shift($values);

			# Grab the default values.
			if(!isset(self::$_configs[$config])):
				if(is_file(BASE_PATH.'phy/config/'.$config.'.ini')):
					self::$_configs[$config] = parse_ini_file(BASE_PATH.'phy/config/'.$config.'.ini');
				else:
					\PHY\Debug::error('Config "'.$config.'" was not found.');
					return;
				endif;
			endif;

			if($values):
				$temp = self::$_configs[$config];
				foreach($values as $value):
					if(!isset($temp[$value])) return;
					elseif($temp) $temp = $temp[$value];
				endforeach;
				return $temp;
			else:
				return self::$_configs[$config];
			endif;
		}

		/**
		 * Turn on PHY namespacing.
		 */
		static public function register() {
			spl_autoload_register('PHY\CORE::init');
		}

		/**
		 * Turn off PHY namespacing.
		 */
		static public function unregister() {
			spl_autoload_unregister('PHY\CORE::init');
		}

	}

	\PHY\Core::register();
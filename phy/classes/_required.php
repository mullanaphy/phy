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

		/**
		 *
		 * @param string $Class
		 * @static
		 * @return bool
		 */
		static public function init($Class,$graceful=false) {
			$Class = strtolower($Class);
			$path = explode('\\',trim($Class,'\\'));

			$namespace = array_shift($path);
			if($namespace !== strtolower(__NAMESPACE__)):
				return false;
			endif;

			if(in_array($Class,array_map('strtolower',array_map('strtolower',get_declared_classes())))) return true;

			if($path):
				$dir = '';
				for($i = 0,$count = count($path); $i < $count; ++$i):
					$dir .= '/'.$path[$i];
					if(is_file(ROOT_PATH.'/phy/classes'.$dir.'/_interface.php')) require_once ROOT_PATH.'/phy/classes'.$dir.'/_interface.php';
					elseif(is_file(BASE_PATH.'phy/classes'.$dir.'/_interface.php')) require_once BASE_PATH.'phy/classes'.$dir.'/_interface.php';
					if(is_file(ROOT_PATH.'/phy/classes'.$dir.'/_abstract.php')) require_once ROOT_PATH.'/phy/classes'.$dir.'/_abstract.php';
					elseif(is_file(BASE_PATH.'phy/classes'.$dir.'/_abstract.php')) require_once BASE_PATH.'phy/classes'.$dir.'/_abstract.php';
				endfor;
			endif;

			$path = join('/',$path);

			if(is_file(ROOT_PATH.'/phy/classes/'.$path.'/_interface.php')) require_once ROOT_PATH.'/phy/classes/'.$path.'/_interface.php';
			elseif(is_file(BASE_PATH.'phy/classes/'.$path.'/_interface.php')) require_once BASE_PATH.'phy/classes/'.$path.'/_interface.php';
			if(is_file(ROOT_PATH.'/phy/classes/'.$path.'/_abstract.php')) require_once ROOT_PATH.'/phy/classes/'.$path.'/_abstract.php';
			elseif(is_file(BASE_PATH.'phy/classes/'.$path.'/_abstract.php')) require_once BASE_PATH.'phy/classes/'.$path.'/_abstract.php';
			if(is_file(ROOT_PATH.'/phy/classes/'.$path.'/_class.php')):
				require_once ROOT_PATH.'/phy/classes/'.$path.'/_class.php';
			elseif(file_exists(ROOT_PATH.'/phy/classes/'.$path.'.php')):
				require_once ROOT_PATH.'/phy/classes/'.$path.'.php';
			elseif(is_file(BASE_PATH.'phy/classes/'.$path.'/_class.php')):
				require_once BASE_PATH.'phy/classes/'.$path.'/_class.php';
			elseif(file_exists(BASE_PATH.'phy/classes/'.$path.'.php')):
				require_once BASE_PATH.'phy/classes/'.$path.'.php';
			elseif(!in_array($Class,array_map('strtolower',get_declared_classes()))):
				if(!$graceful) \PHY\Debug::error('Could not find Class "'.$path.'". #'.__LINE__,E_USER_WARNING);
				return false;
			endif;

			return true;
		}

		/**
		 * Bootstrapping.
		 * 
		 * @param type $Class 
		 * @return Class
		 */
		static public function load($Class='',$graceful=false) {
			if(!is_string($Class) || !$Class) Debug::warning('Attempting to load an invalid Class Name',E_USER_WARNING);
			$Class = strtolower(substr($Class,0,4)) === '\PHY\\'?
				:'\PHY\\'.$Class;
			if(!class_exists($Class)) $exists = self::init($Class);
			else $exists = true;
			if($exists):
				$Class = new $Class;
			else:
				if(!$graceful) \PHY\Debug::error('Could not load Class "'.$Class.'". #'.__LINE__,E_USER_WARNING);
				$Class = new \stdClass;
			endif;
			return $Class;
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
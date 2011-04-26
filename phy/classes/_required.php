<?php

	if(!function_exists('__autoload')):

		function __autoload($Class) {
			$Class = strtolower($Class);
			if(in_array($Class,array_map('strtolower',array_map('strtolower',get_declared_classes())))) return true;
			if(strpos($Class,'_') === false):
				if(is_file(BASE_PATH.'phy/classes/'.$Class.'/_interface.php')) require_once BASE_PATH.'phy/classes/'.$Class.'/_interface.php';
				if(is_file(BASE_PATH.'phy/classes/'.$Class.'/_abstract.php')) require_once BASE_PATH.'phy/classes/'.$Class.'/_abstract.php';
				if(is_file(BASE_PATH.'phy/classes/'.$Class.'/_class.php')):
					require_once BASE_PATH.'phy/classes/'.$Class.'/_class.php';
				elseif(file_exists(BASE_PATH.'phy/classes/'.$Class.'.php')):
					require_once BASE_PATH.'phy/classes/'.$Class.'.php';
				elseif(!in_array($Class,array_map('strtolower',get_declared_classes()))):
					throw new Exception('Page not found');
					trigger_error('Class '.strtoupper($Class).' is undefined.');
					return false;
				endif;
			else:
				$location = explode('_',$Class);
				$Class = array_pop($location);
				$count = count($location);
				$dir = false;
				for($i = 0; $i < $count; ++$i):
					$dir .= '/'.$location[$i];
					if(is_file(BASE_PATH.'phy/classes'.$dir.'/_interface.php')) require_once BASE_PATH.'phy/classes'.$dir.'/_interface.php';
					if(is_file(BASE_PATH.'phy/classes'.$dir.'/_abstract.php')) require_once BASE_PATH.'phy/classes'.$dir.'/_abstract.php';
				endfor;
				$location = join('/',$location);
				if(is_file(BASE_PATH.'phy/classes/'.$location.'/'.$Class.'/_class.php')):
					require_once BASE_PATH.'phy/classes/'.$location.'/'.$Class.'/_class.php';
				elseif(is_file(BASE_PATH.'phy/classes/'.$location.'/'.$Class.'.php')):
					require_once BASE_PATH.'phy/classes/'.$location.'/'.$Class.'.php';
				elseif(file_exists(BASE_PATH.'phy/classes/'.str_replace('/','_',$location).'/'.$Class.'/_class.php')):
					require_once BASE_PATH.'phy/classes/'.str_replace('/','_',$location).'/'.$Class.'/_class.php';
				elseif(file_exists(BASE_PATH.'phy/classes/'.str_replace('/','_',$location).'_'.$Class.'.php')):
					require_once BASE_PATH.'phy/classes/'.str_replace('/','_',$location).'_'.$Class.'.php';
				elseif(!in_array(str_replace('/','_',$location).'_'.$Class,array_map('strtolower',get_declared_classes()))):
					if(strtolower(substr($Class,0,5)) === 'page_'):
						throw new Exception('Page not found');
						trigger_error('Class '.strtoupper(str_replace('/','_',$location).'_'.$Class).' is undefined.');
						return false;
					else:
						return false;
					endif;
				endif;
			endif;
		}

	endif;
<?php

	const DEBUGGER = true;

	call_user_func(
		function() {

			# Time zone
			date_default_timezone_set('America/New_York');

			# Define the BASE_PATH.
			if(!defined('BASE_PATH')) define('BASE_PATH',dirname(dirname(__FILE__)).'/');
			if(!defined('ROOT_PATH')) define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT']);

			# Class handler file. This is what controls the folder system.
			require_once BASE_PATH.'/phy/classes/_required.php';

			# Constants.
			require_once BASE_PATH.'/phy/constants/_required.php';

			# Setting the XSRF cookie.
			if(!isset($_COOKIE['_xsrf_id'])) \PHY\Cookie::set('_xsrf_id',md5(\PHY\String::random(16)),time() + INT_YEAR);

			# Headers. Also note, HTML\PHP pages will also call session_start.
			new \PHY\Headers;

			# See if errors should be printed to screen, if it's a devo server
			# then sure.
			if(defined('DEBUGGER') && DEBUGGER):
				ini_set('display_errors',1);
				error_reporting(E_ALL);
			else:
				ini_set('display_errors',0);
				error_reporting(0);
			endif;

			$_ = sys_getloadavg();
			if($_[0] > 30):
				header('HTTP/1.1 503 Service Unavailable');
				die(ERROR_HTML);
			elseif($_[0] > 10):
				sleep(1);
			endif;

			# Register default SQL.
			$database = \PHY\Registry::get('config/site/database')?:'mysql';
			if(\PHY\Registry::config($database,true)):
				$config = \PHY\Registry::get('config/'.$database.'/'.\PHY\Registry::theme());
				$database = '\PHY\Extended\\'.$database;
				$MySQL = new $database($config['host'],$config['username'],$config['password'],$config['table']);
				\PHY\Registry::set('storage/db',$MySQL);
			endif;

			# Register default Cache.
			if(\PHY\Registry::get('config/memcache',true)):
				$Memcache = new \PHY\Extended\Memcache;
				\PHY\Debug::dump(\PHY\Registry::get('config/memcache'));
				foreach(\PHY\Registry::get('config/memcache') as $host):
					if(strpos($host,':') !== false):
						$host = explode(':',$host);
						$Memcache->connect($host[0],$host[1]);
					else:
						$Memcache->connect($host,11211);
					endif;
				endforeach;
				\PHY\Registry::set('storage/cache',$Memcache);
			endif;

			# Start timing page generation
			\PHY\Debug::timer(true);
		}
	);
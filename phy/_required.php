<?php

	const DEBUGGER = true;
	
	call_user_func(
		function() {

			# Time zone
			date_default_timezone_set('America/New_York');

			# Define the BASE_PATH.
			define('BASE_PATH',dirname(dirname(__FILE__)).'/');

			# Class handler file. This is what controls the folder system.
			require_once BASE_PATH.'/phy/classes/_required.php';

			# Constants.
			require_once BASE_PATH.'/phy/constants/_required.php';

			# Config.
			require_once BASE_PATH.'/phy/config/_required.php';

			# Setting the XSRF cookie.
			if(!isset($_COOKIE['xsrf_id'])) PHY\Cookie::set('xsrf_id',md5(PHY\String::random(16)),INT_YEAR);

			# Headers. Also note, HTML\PHP pages will also call session_start.
			new PHY\Headers;

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

			# Start timing page generation
			PHY\Debug::timer(true);
		}
	);
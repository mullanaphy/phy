<?php

	set_time_limit(0);
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type:text/html;charset=utf-8');
	flush();

	# Load up phy.
	require_once 'phy/_required.php';

	call_user_func(
		function() {
			switch($_SERVER['REQUEST_METHOD']):
				case 'GET':
				case 'HEAD':
					$parameters = $_GET;
					break;
				case 'POST':
					$parameters = array_merge($_GET,$_POST);
					break;
				case 'PUT':
				case 'DELETE':
					parse_str(file_get_contents('php://input'),$parameters);
					$parameters = array_merge($_GET,$_POST,$parameters);
					break;
				default:
					header('HTTP/1.1 501 Not Implemented');
					header('Allow: DELETE, GET, HEAD, POST, PUT',true,501);
					echo 'Unauthorized';
					exit;
			endswitch;

			$parameters['method'] = $_SERVER['REQUEST_METHOD'];

			echo '<!DOCTYPE html><html><head><title>Comet</title><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /></head><body>';

			$xsrf_id = ((isset($parameters['xsrf_id']))?$parameters['xsrf_id']:((isset($parameters['xsrf']))?$parameters['xsrf']:false));
			if(!$xsrf_id || !isset($_COOKIE['xsrf']) || ($xsrf_id != $_COOKIE['xsrf'])):
				echo '<script type="text/javascript">comet.alert(\'Polling can only be done on Lafango. #'.__LINE__.'\');</script>';
				exit;
			endif;

			if(!isset($parameters['controller'])):
				echo '<script type="text/javascript">comet.alert(\'Controller was not provided. #'.__LINE__.'\');</script>';
				exit;
			endif;

			# Comet loader.
			$Comet = new Comet($parameters['controller'],$parameters);
			if($Comet->exists):
				while(1):
					$run = $Comet->run();
					if($run) echo '<script type="text/javascript">',$run,'</script>';
					flush();
					sleep(1);
				endwhile;
			else:
				echo '<script type="text/javascript">comet.alert(\'Controller does not support Comet. #'.__LINE__.'\');</script>';
			endif;

			echo '</body></html>';
		}
	);
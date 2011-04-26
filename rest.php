<?php

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
					header('Allow: '.join(', ',API::methods()),true,501);
					echo 'Unauthorized';
					exit;
			endswitch;

			$parameters['method'] = isset($parameters['method']) && in_array(strtoupper($parameters['method']),API::methods())?strtoupper($parameters['method']):$_SERVER['REQUEST_METHOD'];

			# See if we're from an internal AJAX call.
			define('IS_AJAX',isset($parameters['_ajax']));

			# We have user login\out.
			if(!isset($parameters['user'],$parameters['password'])):
				$xsrf_id = isset($parameters['xsrf_id'])?$parameters['xsrf_id']:false;
				if(!$xsrf_id || !isset($_COOKIE['xsrf_id']) || ($xsrf_id != $_COOKIE['xsrf_id'])):
					header('HTTP/1.1 403 '.Constant::STATUS_CODE(403));
					header('Content-type: application/json; charset=utf-8');
					echo json_encode('Please use http://api.lafango.com/ for outside requests. #'.__LINE__);
					new API_Error(isset($parameters['controller'])?$parameters['controller']:'null',$parameters['method'],'Please use http://api.lafango.com/ for outside requests. #'.(__LINE__ - 1),$parameters);
					exit;
				endif;
			endif;

			if(!isset($parameters['controller']) && !isset($parameters['_caller'])):
				header('HTTP/1.1 404 '.Constant::STATUS_CODE(404));
				header('Content-type: application/json; charset=utf-8');
				echo json_encode('Controller was not provided. #'.__LINE__);
				new API_Error('null',$parameters['method'],'Controller was not provided. #'.(__LINE__ - 1),$parameters);
				exit;
			endif;

			if(isset($parameters['_caller']) && $parameters['_caller'] === 'module'):
				if(isset($parameters['id'])):
					$Module = new Module($parameters['id'],$parameters);
					$run = $Module->run($parameters['method'],$parameters);
				else:
					$run = array(
						'status' => 400,
						'url' => '/modules',
						'response' => 'Missing a controller id. #'.__LINE__
					);
				endif;
				if($run['status'] >= 300 || $run['status'] < 200):
					new API_Error('module',$parameters['method'],$run,$parameters);
				endif;
			else:
				$API = new API($parameters['controller'],$parameters);
				$run = $API->run(isset($parameters['action'])?$parameters['action']:strtolower($parameters['method']),$parameters);
				if(!$run) $run = array(
						'status' => 404,
						'response' => 'Action was not found. #'.__LINE__
					);
				if($run['status'] >= 300 || $run['status'] < 200) new API_Error($parameters['controller'],isset($parameters['action'])?$parameters['action']:'get',$run,$parameters);
			endif;

			# Redirect or display nothing on a 204.
			if(isset($run['status']) && $run['status'] == 204):
				if(!isset($parameters['_ajax'])):
					Cookie::set('xsrf_id',md5(String::random(16)),INT_YEAR);
					if(isset($run['url'])) header('Location: '.$run['url']);
					else header('HTTP/1.1 204 '.Constant::STATUS_CODE(204));
					exit;
				endif;
				header('HTTP/1.1 204 '.Constant::STATUS_CODE(204));
				exit;

			# set the status.
			elseif(!isset($run['status']) || ($run['status'] != 204 && !isset($run['response']))):
				header('HTTP/1.1 500 '.Constant::STATUS_CODE(500));
				header('Content-type: application/json; charset=utf-8');
				echo json_encode('Missing a status or a response. #'.__LINE__);
			else:
				header('HTTP/1.1 '.$run['status'].' '.Constant::STATUS_CODE($run['status']));
			endif;

			if(isset($parameters['_ajax'])):
				header('Content-type: application/json; charset=utf-8');
				if(is_array($run['response']) && isset($run['response']['content']) && is_object($run['response']['content']) && preg_match('#Markup|Container#i',get_class($run['response']['content']))):
					$run['response']['console'] = 'Generation: '.Debug::timer().'; Elements: '.Markup_HTML5::elements().'; Server: '.$_SERVER['SERVER_ADDR'];
					$run['response']['content'] = (string)$run['response']['content'];
					if(Template::files()) $run['response']['files'] = Template::files();
					$run['response'] = json_encode($run['response']);
				elseif(is_object($run['response']) && preg_match('#Markup|Container#i',get_class($run['response']))):
					$run['response'] = array(
						'console' => 'Generation: '.Debug::timer().'; Elements: '.Markup_HTML5::elements().'; Server: '.$_SERVER['SERVER_ADDR'],
						'content' => (string)$run['response']
					);
					if(Template::files()) $run['response']['files'] = Template::files();
					$run['response'] = json_encode($run['response']);
				elseif(is_object($run['response']) && method_exists($run['response'],'__toString')):
					$run['response'] = (string)$run['response'];
				else:
					if(is_array($run['response'])):
						$run['response']['console'] = 'Generation: '.Debug::timer().'; Elements: '.Markup_HTML5::elements().'; Server: '.$_SERVER['SERVER_ADDR'];
						if(Template::files()) $run['response']['files'] = Template::files();
					endif;
					$run['response'] = json_encode($run['response']);
				endif;
				echo 'while(1);'.$run['response'];
			elseif(isset($parameters['_iframe'])):
				header('HTTP/1.1 '.$run['status'].' '.Constant::STATUS_CODE($run['status']));
				header('Content-type: text/html; charset=utf-8');
				echo $run['response'];
			elseif(isset($parameters['_form'])):
				if($run['status'] >= 200 && $run['status'] < 300) Cookie::set('xsrf_id',md5(String::random(16)),INT_YEAR);
				if(isset($run['url'])) header('Location: '.$run['url']);
				exit;
			elseif(isset($parameters['xsrf']) || isset($parameters['xsrf_id']) || isset($parameters['_caller'])):
				header('HTTP/1.1 '.$run['status'].' '.Constant::STATUS_CODE($run['status']));
				$Template = new Template;
				$Template->section('dark');
				$Template->css('rest/0.1.0.css');
				$tag = new Markup_HTML5;
				if($run['status'] >= 200 && $run['status'] < 300):
					$Container = new Container_Generic;
					$Container->title('Alert');
					$Container->header(
						$tag->url(
							'Go back',
							isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'javascript:window.history.go(-1);'
						)
					);
					$Container->append('Greetings, you have received this page either by not having JavaScript activated or by opening a JavaScript link in a new tab\window. Do not worry, any actions you perform on this page will work the same as if you opened it as a popup box.');
					$Template->append($Container);
				endif;
				$Template->section('normal');
				if(is_object($run['response']) && preg_match('#Markup|Container#i',get_class($run['response']))):
					$Template->append($run['response']);
				elseif(is_array($run['response']) && isset($run['response']['content']) && is_object($run['response']['content']) && preg_match('#Markup|Container#i',get_class($run['response']['content']))):
					if(isset($run['response']['files'])) $Template->files($run['response']['files']);
					$Template->append($run['response']['content']);
				else:
					$Container = new Container_Generic;
					$Container->title('Alert');
					$Container->header(
						$tag->url(
							'Go back',
							isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'javascript:window.history.go(-1);'
						)
					);
					$Container->append(is_array($run['response']) && isset($run['response']['content'])?$run['response']['content']:$run['response']);
					$Template->append($Container);
				endif;
				exit;
			elseif(isset($parameters['xml'])):
				header('Content-type:text/xml;charset=utf-8');
				echo $run['response'];
			else:
				header('Content-type: text/javascript; charset=utf-8');
				if(isset($run['response']['content'])) $run['response']['content'] = (string)$run['response']['content'];
				$encode = json_encode($run['response']);
				$return = '';
				$indented = 0;
				$string = false;
				for($i = 0,$count = strlen($encode); $i <= $count; ++$i):
					$_ = substr($encode,$i,1);
					switch($_):
						case '\\':
							if($string) $_ = '';
							break;
						case '"':
							if(!$string):
								for($ident = 0; $ident < $indented; ++$ident) $_ = $_;
								$string = true;
							else:
								$string = false;
							endif;
							break;
						case '{':
						case '[':
							if($string) break;
							++$indented;
							$_ .= "\n";
							for($ident = 0; $ident < $indented; ++$ident) $_ .= "   ";
							break;
						case '}':
						case ']':
							if($string) break;
							--$indented;
							for($ident = 0; $ident < $indented; ++$ident) $_ = "   ".$_;
							$_ = "\n".$_;
							break;
						case ',':
							if($string) break;
							$_ .= "\n";
							for($ident = 0; $ident < $indented; ++$ident) $_ .= "   ";
							break;
						case ':':
							if($string) break;
							$_ .= ' ';
							break;
					endswitch;
					$return .= $_;
				endfor;
				echo preg_replace('#"(-?\d+\.?\d*)"#','$1',$return);
			endif;
		}
	);
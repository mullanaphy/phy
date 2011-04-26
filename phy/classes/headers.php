<?php

	/**
	 * Run headers.
	 *
	 * @category Header
	 * @package Header
	 * @author John Mullanaphy
	 * @final
	 */
	final class Headers {

		static $file = '',
		$extension = 'php',
		$_BOTS = array('baiduspider','googlebot','msnbot','yandex'),
		$_MOBILE = array('android','blackberry','htc','iphone','lg','motorola','nokia','palm','psp','samsung','sonyericsson');

		/**
		 * Parses REQUEST_URI to know what headers to use.
		 */
		public function __construct() {
			self::browser();
			if(strpos($_SERVER['REQUEST_URI'],'?') !== false):
				$file = explode('?',$_SERVER['REQUEST_URI']);
				self::$file = $file[0];
				$file = explode('.',$file[0]);
			else:
				self::$file = $_SERVER['REQUEST_URI'];
				$file = explode('.',$_SERVER['REQUEST_URI']);
			endif;
			self::$extension = $file[count($file) - 1];
			switch(self::$extension):
				case 'css':
					self::css();
					break;
				case 'js':
					self::js();
					break;
				case 'php':
				case 'html':
				case 'htm':
				case 'xml':
				case 'rss':
				default:
					self::html();
					break;
			endswitch;
		}

		/**
		 * See if the User is a bot.
		 *
		 * @return bool
		 */
		static public function bot() {
			return (in_array(USER_BROWSER,self::$_BOTS));
		}

		/**
		 * See if the User is on a mobile platform.
		 * @return bool
		 */
		static public function mobile() {
			return (in_array(USER_BROWSER,self::$_MOBILE) || strpos($_SERVER['HTTP_HOST'],'m.lafango') !== false);
		}

		/**
		 * See if a User is using $USER_AGENT
		 *
		 * @return bool
		 */
		static public function __callStatic($method,$parameters) {
			return USER_BROWSER === strtolower($method);
		}

		/**
		 * Set the User's browser.
		 */
		private function browser() {
			# Grab the user agent if it is set.
			$user_agent = ((isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT']:false);

			# Text browsers.
			if(strpos($user_agent,'Lynx') !== false) define('USER_BROWSER','lynx');

			# Mobile browsers.
			elseif(strpos($user_agent,'iPhone') !== false) define('USER_BROWSER','iphone');
			elseif(strpos($user_agent,'Android') !== false) define('USER_BROWSER','android');
			elseif(strpos($user_agent,'BlackBerry') !== false) define('USER_BROWSER','blackberry');
			elseif(strpos($user_agent,'HTC') !== false) define('USER_BROWSER','htc');
			elseif(strpos($user_agent,'LG ') !== false) define('USER_BROWSER','lg');
			elseif(strpos($user_agent,'Motorola') !== false) define('USER_BROWSER','motorola');
			elseif(strpos($user_agent,'Nokia') !== false) define('USER_BROWSER','nokia');
			elseif(strpos($user_agent,'Palm') !== false) define('USER_BROWSER','palm');
			elseif(strpos($user_agent,'PSP') !== false) define('USER_BROWSER','psp');
			elseif(strpos($user_agent,'Samsung') !== false) define('USER_BROWSER','samsung');
			elseif(strpos($user_agent,'SonyEricsson') !== false) define('USER_BROWSER','sonyericsson');

			# Bots and search engines.
			elseif(strpos($user_agent,'Googlebot') !== false) define('USER_BROWSER','googlebot');
			elseif(strpos($user_agent,'msnbot') !== false) define('USER_BROWSER','msnbot');
			elseif(strpos($user_agent,'Yandex') !== false) define('USER_BROWSER','yandex');
			elseif(strpos($user_agent,'Baiduspider') !== false) define('USER_BROWSER','baiduspider');

			# Other browsers.
			elseif(strpos($user_agent,'MSIE 6.0') !== false) define('USER_BROWSER','ie6');
			elseif(strpos($user_agent,'MSIE') !== false) define('USER_BROWSER','ie');
			elseif(strpos($user_agent,'Opera') !== false) define('USER_BROWSER','opera');
			elseif(strpos($user_agent,'Chrome') !== false) define('USER_BROWSER','chrome');
			elseif(strpos($user_agent,'Safari') !== false) define('USER_BROWSER','safari');
			elseif(strpos($user_agent,'Mozilla') !== false) define('USER_BROWSER','firefox');

			# Otherwise we have something unknown, probably a bot so let's treat it as a text based browser.
			else define('USER_BROWSER','lynx');
		}

		/**
		 * Parse headers for CSS files.
		 */
		private function css() {
			header('Content-Type: text/css;charset=utf-8');
			header('Content-Encoding: gzip');
			if(preg_match('#/style/#',self::$file)):
				return;
			elseif(!is_file(BASE_PATH.'public'.self::$file)):
				header('HTTP/1.0 404 Not Found');
				echo '/* file not found */';
				exit;
			else:
				$file = stat(BASE_PATH.'public'.self::$file);
				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])):
					$modified = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
					if(($modified > 0) && ($modified >= $file['mtime'])):
						header('HTTP/1.0 304 Not Modified');
						header('Expires:');
						header('Cache-Control: public, max-age=86400');
						exit;
					endif;
				endif;
				header('Content-Length: '.filesize(BASE_PATH.'public'.self::$file));
				header('Cache-Control: public, max-age=86400');
				header('Pragma: ');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s',strtotime('- 5 second')).' GMT');
				header('Expires: '.gmdate('D, d M Y H:i:s',strtotime('+ 1 day')).' GMT');
			endif;
		}

		/**
		 * Parse headers for JS files.
		 */
		private function js() {
			header('Content-Type: text/javascript;charset=utf-8');
			header('Content-Encoding: gzip');
			if(!is_file(BASE_PATH.'public'.self::$file)):
				header('HTTP/1.0 404 Not Found');
				echo '/* file not found */';
				exit;
			else:
				$file = stat(BASE_PATH.'public'.self::$file);
				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])):
					$modified = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
					if(($modified > 0) && ($modified >= $file['mtime'])):
						header('HTTP/1.0 304 Not Modified');
						header('Expires:');
						header('Cache-Control: public, max-age=86400');
						exit;
					endif;
				endif;
				header('Content-Length: '.filesize(BASE_PATH.'public'.self::$file));
				header('Cache-Control: public, max-age=86400');
				header('Pragma: ');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s',strtotime('- 5 second')).' GMT');
				header('Expires: '.gmdate('D, d M Y H:i:s',strtotime('+ 1 day')).' GMT');
			endif;
		}

		/**
		 * Default headers.
		 */
		private function html() {
			# We don't cache HTML.
			session_start();

			header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Pragma: no-cache');
			header('Content-language: en');
			switch(self::$extension):
				case 'rss':
					header('Content-Type: application/rss+xml;charset=utf-8');
					break;
				case 'xml':
					header('Content-Type: application/xml;charset=utf-8');
					break;
				default:
					header('Content-Type: text/html;charset=utf-8');
			endswitch;
		}

	}
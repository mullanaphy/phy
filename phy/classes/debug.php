<?php

	namespace PHY;
	
	/**
	 * Debugger class.
	 *
	 * @category Debug
	 * @package Debug
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	
	final class Debug {

		static private $_throw = false,
		$_count = 0;

		/**
		 * Variable dump.
		 *
		 * @staticvar int $count
		 * @param mixed $dump
		 * @param bool $return If true dump will return instead of echoing.
		 * @return string
		 */
		static public function dump($dump=NULL,$return=false) {
			if(!DEBUGGER) return;
			$debug = debug_backtrace();
			reset($debug);
			$first = current($debug);
			if(!isset($_GET['_ajax'])):
				$dump = str_replace(array('<','>'),array('&lt;','&gt;'),print_r($dump,true));
				if($return):
					$return = '<pre style="background:#efe;border:solid 1px #cfc;line-height:130%;margin:5px;font:12px \'courier new\';padding:5px;text-align:left;color:#080;">'.
						'<h2 style="border-bottom:solid 2px #cfc;color:#080;font:bold 16px \'courier new\';margin:0 0 5px;padding:0;">VAR #'.++self::$_count.': "'.str_replace(BASE_PATH,'/',$first['file']).'" on line "'.$first['line'].'"</h2>'.
						$dump.
						'</pre>';
				else:
					echo '<pre style="background:#efe;border:solid 1px #cfc;line-height:130%;margin:5px;font:12px \'courier new\';padding:5px;text-align:left;color:#080;">',
					'<h2 style="border-bottom:solid 2px #cfc;color:#080;font:bold 16px \'courier new\';margin:0 0 5px;padding:0;">VAR #',++self::$_count,': "',str_replace(BASE_PATH,'/',$first['file']),'" on line "',$first['line'],'"</h2>',
					$dump,
					'</pre>';
				endif;
			elseif($return):
				$return = print_r($dump,true);
			else:
				print_r($dump);
			endif;
			return $return;
		}

		/**
		 * Stack dump
		 *
		 */
		static public function stack() {
			$debug = debug_backtrace();
			reset($debug);
			$first = current($debug);
			array_shift($debug);
			$echo = str_replace(array('<','>'),array('&lt;','&gt;'),print_r($debug,true));
			echo '<pre style="background:#eef;border:solid 1px #ccf;line-height:130%;margin:5px;font:12px \'courier new\';padding:5px;text-align:left;color:#008;">',
			'<h2 style="border-bottom:solid 2px #ccf;color:#008;font:bold 16px \'courier new\';margin:0 0 5px;padding:0;">Stack #',++self::$_count,': "',str_replace(BASE_PATH,'/',$first['file']),'" on line "',$first['line'],'"</h2>',
			$echo,
			'</pre>';
		}

		/**
		 * Timer.
		 *
		 * @staticvar string $time
		 * @staticvar string $memory
		 * @param bool $reset Resets the timer.
		 */
		static public function timer($reset=false) {
			static $time = NULL;
			static $memory = NULL;
			if($time === NULL || $reset):
				$time = microtime(true);
				$memory = memory_get_usage();
			else:
				return (string)(round(microtime(true) - $time,5)).' using '.String::bytes(memory_get_usage() - $memory).' of '.String::bytes(memory_get_usage());
			endif;
		}

		/**
		 * Return number of times variables, stacks, or warnings were dumped.
		 * 
		 * @return <type> 
		 */
		static public function count() {
			return self::$_count;
		}

		/**
		 * Set warnings to be try/catch or echos.
		 * 
		 * @param bool $throw 
		 */
		static public function catcher($throw=true) {
			self::$_throw = !!$throw;
		}

		/**
		 * Either throws a new exception or echos an error depending on
		 * Debug::catcher($bool);
		 *
		 * @param string $error
		 * @param bool $fatal If true Warning will run: exit;
		 * @return <type>
		 */
		static public function error($error,$fatal=false) {
			if(!DEBUGGER) return;
			$debug = debug_backtrace();
			if(self::$_throw):
				if($fatal) throw new \PHY\Exception\Severe($error.' (WARNING #'.++self::$_count.' FROM '.str_replace(BASE_PATH,'/',((isset($debug[1]['file']))?$debug[1]['file']:$debug[2]['file'])).' ON LINE #'.((isset($debug[1]['line']))?$debug[1]['line']:$debug[2]['line']));
				else throw new \PHY\Exception\Warning($error.' (WARNING #'.++self::$_count.' FROM '.str_replace(BASE_PATH,'/',((isset($debug[1]['file']))?$debug[1]['file']:$debug[2]['file'])).' ON LINE #'.((isset($debug[1]['line']))?$debug[1]['line']:$debug[2]['line']));
			else:
				echo '<pre style="background:#fee;border:solid 1px #fcc;line-height:130%;margin:5px;font:12px \'courier new\';padding:5px;text-align:left;color:#f00;">',
				'<h2 style="border-bottom:solid 2px #fcc;color:#f00;font:bold 16px \'courier new\';margin:0 0 5px;padding:0;">WARNING #',++self::$_count,': "',str_replace(BASE_PATH,'/',((isset($debug[1]['file']))?$debug[1]['file']:$debug[2]['file'])),'" on line "',((isset($debug[1]['line']))?$debug[1]['line']:$debug[2]['line']),'"</h2>',
				str_replace(array('<','>'),array('&lt;','&gt;'),$error),
				(($fatal)?'<h3 style="border-top:solid 2px #fcc;color:#f00;font:bold 16px \'courier new\';margin:5px 0 0;padding:2px 0 0;">Error is fatal</h3>':false),
				'</pre>';
			endif;
			if($fatal) exit;
		}

	}
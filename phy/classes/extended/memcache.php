<?php

	namespace PHY\Extended;

	if(!class_exists('\Memcache',true)):
		\PHY\Debug::error('Memcache has not been installed.',E_ERROR);
		return;
	endif;

	/**
	 * Extended to add some debugging methods.
	 *
	 * @category Extended
	 * @package Extended_Memcache
	 * @author John Mullanaphy
	 */
	class Memcache extends \Memcache {

		private static $COUNT = 0,
		$DEBUG = false,
		$LAST = false,
		$SUCCESS = 0;

		/**
		 * Turns off Debugger in the event it was on.
		 */
		public function __destruct() {
			if(self::$DEBUG) $this->hide();
		}

		/**
		 * Decrement a node by $decrement.
		 *
		 * @param string $node
		 * @param int $decrement
		 * @return bool
		 */
		public function decrement($node=false,$decrement=1) {
			if(!$node):
				self::$LAST = '<strong>ERROR:</strong> tried to '.__FUNCTION__.' an empty node.';
				$this->last();
				return false;
			endif;
			++self::$COUNT;
			$success = parent::decrement($node,$decrement);
			self::$SUCCESS += is_array($success)?count($success):1;
			if($success) self::$LAST = '<strong>'.ucfirst(__FUNCTION__).':</strong> '.$node;
			else self::$LAST = '<strong>'.ucfirst(__FUNCTION__).' (FAILED):</strong> '.$node;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Delete an entry
		 *
		 * @param string $node
		 * @param int $time When to delete this node.
		 * @return bool
		 */
		public function delete($node=false,$time=0) {
			if(!$node):
				self::$LAST = '<strong>ERROR:</strong> tried to '.__FUNCTION__.' an empty node.';
				$this->last();
				return false;
			endif;
			++self::$COUNT;
			$success = parent::delete($node,$time);
			self::$SUCCESS += is_array($success)?count($success):1;
			if($success):
				self::$LAST = '<strong>'.ucfirst(__FUNCTION__).':</strong> '.$node;
				self::dirty('delete',$node);
			else:
				self::$LAST = '<strong>'.ucfirst(__FUNCTION__).' (FAILED):</strong> '.$node;
			endif;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Flush out all keys.
		 *
		 * @return bool
		 */
		public function flush() {
			++self::$COUNT;
			\PHY\Debug::stack();
			$success = parent::flush();
			self::$SUCCESS += (int)$success;
			if($success):
				self::$LAST = '<strong>FLUSH.</strong>';
				self::dirty('flush','');
			else:
				self::$LAST = '<strong>FLUSH (FAILED).</strong>';
			endif;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Grab a node if it exists.
		 *
		 * @param string $node
		 * @param int $flag
		 * @return <type>
		 */
		public function get($node=false,$flag=0) {
			if(!$node):
				self::$LAST = '<strong>ERROR:</strong> tried to '.__FUNCTION__.' an empty node.';
				$this->last();
				return false;
			endif;
			++self::$COUNT;
			$success = parent::get($node,$flag);
			self::$SUCCESS += is_array($success)?count($success):1;
			if($success) self::$LAST = '<strong>'.ucfirst(__FUNCTION__).':</strong> '.$node;
			else self::$LAST = '<strong>'.ucfirst(__FUNCTION__).' (FAILED):</strong> '.$node;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Increment a node by $increment.
		 *
		 * @param string $node
		 * @param int $increment
		 * @return bool
		 */
		public function increment($node=false,$increment=1) {
			if(!$node):
				self::$LAST = '<strong>ERROR:</strong> tried to '.__FUNCTION__.' an empty node.';
				$this->last();
				return false;
			endif;
			$success = parent::increment($node,$increment);
			self::$SUCCESS += is_array($success)?count($success):1;
			if($success) self::$LAST = '<strong>'.ucfirst(__FUNCTION__).':</strong> '.$node;
			else self::$LAST = '<strong>'.ucfirst(__FUNCTION__).' (FAILED):</strong> '.$node;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Replace a node with new data. WARNING: No fault tolerance built in.
		 *
		 * @param string $node
		 * @param mixed $value
		 * @param int $flag
		 * @param int $expiration
		 * @return bool
		 */
		public function replace($node=false,$value=false,$flag=0,$expiration=0) {
			if(!$node):
				self::$LAST = '<strong>ERROR:</strong> tried to '.__FUNCTION__.' an empty node.';
				$this->last();
				return false;
			endif;

			++self::$COUNT;
			$success = parent::replace($node,$value,$flag,$expiration);
			self::$SUCCESS += is_array($success)?count($success):1;
			if($success):
				self::$LAST = '<strong>'.ucfirst(__FUNCTION__).':</strong> '.$node;
				self::dirty('replace',$node);
			else:
				self::$LAST = '<strong>'.ucfirst(__FUNCTION__).' (FAILED):</strong> '.$node;
			endif;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Store a new key into the memory table.
		 *
		 * @param string $node
		 * @param mixed $value
		 * @param int $flag
		 * @param int $expiration
		 * @return bool
		 */
		public function set($node=false,$value=false,$flag=0,$expiration=0) {
			if(!$node):
				self::$LAST = '<strong>ERROR:</strong> tried to '.__FUNCTION__.' an empty node.';
				$this->last();
				return false;
			endif;

			++self::$COUNT;
			$success = parent::set($node,$value,$flag,$expiration);
			self::$SUCCESS += is_array($success)?count($success):1;
			if($success) self::$LAST = '<strong>'.ucfirst(__FUNCTION__).':</strong> '.$node;
			else self::$LAST = '<strong>'.ucfirst(__FUNCTION__).' (FAILED):</strong> '.$node;
			if(self::$DEBUG) $this->last();
			return $success;
		}

		/**
		 * Turn Debugging off.
		 */
		public function hide() {
			if(!self::$DEBUG) return;
			$debug = debug_backtrace();
			$i = 0;
			echo '<pre style="background:#fee;border:solid 1px #fcc;color:#800;line-height:130%;margin:5px;font:bold 16px \'courier new\';padding:5px;text-align:left;">',
			'<h2 style="border-bottom:solid 2px #fcc;color:#f00;font:inherit;margin:0 0 5px;padding:0;">MEMCACHED OUTPUT DEACTIVATED: '.str_replace(BASE_PATH,'/',$debug[$i]['file']).'" on line "'.$debug[$i]['line'].'".</h2>',
			'CALLS:        '.number_format(self::$COUNT)."\n".
			'SUCCESS:      '.number_format(self::$SUCCESS)."\n".
			'HIT RATE:     '.((self::$COUNT > 0)?(round(self::$SUCCESS / self::$COUNT,2) * 100).'%':'NULL')."\n".
			'RUNTIME:      '.(round(microtime(true) - self::$DEBUG[0],5)).' seconds'."\n".
			'MEMORY USAGE: '.String::bytes(memory_get_usage() - self::$DEBUG[1]).
			'</pre>';
			self::$DEBUG = false;
		}

		/**
		 * Grab the last run Memcache command.
		 */
		public function last() {
			$debug = debug_backtrace();
			$i = 1;
			echo '<pre style="background:#fef;border:solid 1px #fcf;line-height:130%;margin:5px;font:12px \'courier new\';padding:5px;text-align:left;color:#808;">',
			'<h2 style="border-bottom:solid 2px #fcf;color:#f0f;font:bold 16px \'courier new\';margin:0 0 5px;padding:0;">MEMCACHED #'.self::$COUNT.': '.str_replace(BASE_PATH,'/',$debug[$i]['file']).'" on line "'.$debug[$i]['line'].'" - '.Debug::timer().'</h2>',
			trim(str_replace(array('<','>'),array('&lt;','&gt;'),preg_replace('/([\t]+)/is','',self::$LAST))),';',
			'</pre>';
		}

		/**
		 * Turn Debugging on.
		 */
		public function show() {
			$debug = debug_backtrace();
			$i = 0;
			echo '<pre style="background:#fee;border:solid 1px #fcc;color:#800;line-height:130%;margin:5px;font:bold 16px \'courier new\';padding:5px;text-align:left;">',
			'MEMCACHED OUTPUT ACTIVATED: '.str_replace(BASE_PATH,'/',$debug[$i]['file']).'" on line "'.$debug[$i]['line'].'".',
			'</pre>';
			self::$DEBUG = array(microtime(true),memory_get_usage());
		}

	}
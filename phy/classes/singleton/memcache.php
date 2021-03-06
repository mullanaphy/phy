<?php

	namespace PHY\Singleton;

	/**
	 * Define your hosts inside phy/config.php
	 * 
	 * @category Singleton
	 * @package @Singleton\Memcache
	 * @author John Mullanaphy
	 */
	final class Memcache {

		private static $_instance = NULL;

		/**
		 * Singletons cannot be initiated.
		 */
		private function __construct() {
			
		}

		/**
		 * Singletons cannot be cloned.
		 */
		public function __clone() {
			\PHY\Debug::error('Singleton Classes cannot be cloned.',E_USER_ERROR);
		}

		/**
		 * Get the Singleton instance.
		 *
		 * @return resource
		 */
		public function instance() {
			if(self::$_instance === NULL):
				self::$_instance = new \PHY\Extended\Memcache;
				$hosts = explode(';',\PHY\Core::config('memcache'));
				foreach($hosts as $host):
					if(strpos($host,':') !== false):
						$host = explode(':',$host);
						self::$_instance->connect($host[0],$host[1]);
					else:
						self::$_instance->connect($host,11211);
					endif;
				endforeach;
			endif;
			return self::$_instance;
		}

	}
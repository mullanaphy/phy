<?php

	namespace PHY\Singleton;

	/**
	 * MySQL Singleton. Can connect to multiple MySQL tables at will.
	 * 
	 * @category Singleton
	 * @package Singleton\MySQL
	 * @author John Mullanaphy
	 */
	final class MySQL {

		private static $_instances = array(),
		$_current = NULL;

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
		 * @param string $host
		 * @param string $username
		 * @param string $password
		 * @param string $table
		 * @return resource
		 */
		public function instance($host=NULL,$username=NULL,$password=NULL,$table=NULL) {
			if($host === NULL):
				$host = \PHY\Registry::get('config/mysql/default/host');
				$username = \PHY\Registry::get('config/mysql/default/username');
				$password = \PHY\Registry::get('config/mysql/default/password');
				$table = \PHY\Registry::get('config/mysql/default/table');
			endif;
			$current = $username.'@'.$host.':'.$table;
			if(!isset(self::$_instances[$current])):
				$database = new \PHY\Extended\MySQL(
						$host,
						$username,
						$password,
						$table
				);
				if($database->error):
					\PHY\Debug::error($database->error.' #'.__LINE__,E_ERROR);
					return;
				else:
					self::$_instances[$current] = $database;
				endif;
			endif;
			self::$_current = $current;
			return self::$_instances[self::$_current];
		}

	}
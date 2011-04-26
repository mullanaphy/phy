<?php

	/**
	 * MySQL Singleton. Can connect to multiple MySQL tables at time.
	 */
	final class Singleton_MySQL {

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
			trigger_error('Singleton Classes cannot be cloned',E_USER_ERROR);
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
				$host = Constant::CONFIG('mysql/default/host');
				$username = Constant::CONFIG('mysql/default/username');
				$password = Constant::CONFIG('mysql/default/password');
				$table = Constant::CONFIG('mysql/default/table');;
			endif;
			$current = $username.'@'.$host.':'.$table;
			if(!isset(self::$_instances[$current])):
				$database = new Extended_MySQL(
						$host,
						$username,
						$password,
						$table
				);
				if($database->error):
					Debug::warning($database->error.' #'.__LINE__,true);
					return;
				else:
					self::$_instances[$current] = $database;
				endif;
			endif;
			self::$_current = $current;
			return self::$_instances[self::$_current];
		}

	}
<?php

	namespace PHY\Extended;

	class MySQL extends \MySQLi {

		private static $COUNT = 0,
		$DEBUG = false,
		$MULTI = false,
		$LAST = false,
		$SERVERS = array();

		/**
		 * Extend this just so we can through out a 503 error if our DB is
		 * acting flaky.
		 *
		 * @param string $host
		 * @param string $username
		 * @param string $password
		 * @param string $table
		 */
		public function __construct($host='localhost',$username=false,$password=false,$table='') {
			parent::__construct($host,$username,$password,$table);
			if($this->connect_error):
				header('HTTP/1.1 503 Service Unavailable');
				die('Connection Error ('.$this->connect_errno.') '.$this->connect_error);
			else:
				self::$SERVERS[] = $this->host_info;
				self::$SERVERS = array_unique(self::$SERVERS);
			endif;
			return $this;
		}

		/**
		 * Turn off Debugging if it was on.
		 */
		public function __destruct() {
			if(self::$DEBUG) $this->hide();
		}

		/**
		 * Prepare a SQL statement.
		 * 
		 * @param string $sql
		 * @return MySQLi_STMT
		 */
		public function prepare($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			$SQL = parent::prepare($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			else:
				return $SQL;
			endif;
		}

		/**
		 * Run a basic query.
		 *
		 * @param string $sql
		 * @return MySQLi_Result
		 */
		public function query($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			return parent::query($sql);
		}

		/**
		 * Run multiple queries.
		 *
		 * @param string $sql
		 * @return MySQLi_Result
		 */
		public function multi_query($sql=false) {
			++self::$COUNT;
			self::$MULTI = true;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			$SQL = parent::multi_query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			else:
				return $SQL;
			endif;
		}

		/**
		 * DELETE statement.
		 * 
		 * @param string $sql
		 * @return int|bool Returns number of affected rows or false on failure.
		 */
		public function delete($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			parent::query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			else:
				return $this->affected_rows;
			endif;
		}

		/**
		 * INSERT statement.
		 *
		 * @param string $sql
		 * @return insert_id|false Will return false on any error.
		 */
		public function insert($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			parent::query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			else:
				return $this->insert_id;
			endif;
		}

		/**
		 * SELECT statement.
		 * 
		 * @param string $sql
		 * @return MySQLi_Result
		 */
		public function select($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			$SQL = parent::query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			else:
				return $SQL;
			endif;
		}

		/**
		 * UPDATE statement.
		 *
		 * @param string $sql
		 * @return int|bool Returns number of affected rows or false on failure.
		 */
		public function update($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			parent::query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			else:
				return $this->affected_rows;
			endif;
		}

		/**
		 * Alias for real_escape_string.
		 *
		 * @param string $string
		 * @return string
		 */
		public function clean($string=false) {
			return $this->real_escape_string($string);
		}

		/**
		 * Clear out all returned results after using a multi_query.
		 */
		public function multi_free() {
			if(self::$MULTI) while($this->more_results()) $this->next_result();
			self::$MULTI = false;
		}

		/**
		 * Return a single value from the database.
		 *
		 * @param string $sql
		 * @return mixed
		 */
		public function single($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			$SQL = parent::query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			endif;
			$result = $SQL->fetch_array();
			$SQL->close();
			return isset($result[0])?$result[0]:false;
		}

		/**
		 * Return a single row from a SELECT statement.
		 *
		 * @param param $sql
		 * @return array
		 */
		public function row($sql=false) {
			++self::$COUNT;
			self::$MULTI = false;
			self::$LAST = $sql;
			if(self::$DEBUG) $this->last();
			$SQL = parent::query($sql);
			if($this->error):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					$this->error."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			elseif($SQL->num_rows > 1):
				\PHY\Debug::error(
					'<strong>Error:</from> '."\n".
					'Your SQL returned '.$SQL->num_rows.' rows. Use select() and fetch_assoc() instead.'."\n".
					'<strong>From:</from> '."\n".
					$sql,E_USER_WARNING
				);
				return false;
			endif;
			$result = $SQL->fetch_assoc();
			$SQL->close();
			return $result;
		}

		/**
		 * Turn debugging off.
		 */
		public function hide() {
			if(!self::$DEBUG) return;
			$debug = debug_backtrace();
			$i = 0;
			echo '<pre style="background:#fee;border:solid 1px #fcc;color:#800;line-height:130%;margin:5px;font:bold 16px \'courier new\';padding:5px;text-align:left;">',
			'<h2 style="border-bottom:solid 2px #fcc;color:#f00;font:inherit;margin:0 0 5px;padding:0;">SQL OUTPUT DEACTIVATED: ',str_replace(BASE_PATH,'/',$debug[$i]['file']),'" on line "',$debug[$i]['line'],'".</h2>',
			'SERVERS:      ',join("\n".'              ',self::$SERVERS),"\n",
			'CALLS:        ',number_format(self::$COUNT),"\n",
			'RUNTIME:      ',(round(microtime(true) - self::$DEBUG[0],5)),' seconds',"\n",
			'MEMORY USAGE: ',\PHY\String::bytes(memory_get_usage() - self::$DEBUG[1]),
			'</pre>';
			self::$SERVERS = array(array_shift(self::$SERVERS));
			self::$DEBUG = false;
		}

		/**
		 * Print out the last run query.
		 */
		public function last() {
			if(!self::$DEBUG) return;
			$debug = debug_backtrace();
			$i = 1;
			echo '<pre style="background:#eef;border:solid 1px #ccf;line-height:130%;margin:5px;font:12px \'courier new\';padding:5px;text-align:left;color:#008;">',
			'<h2 style="border-bottom:solid 2px #ccf;color:#00f;font:bold 16px \'courier new\';margin:0 0 5px;padding:0;">SQL #',self::$COUNT,': ',str_replace(BASE_PATH,'/',$debug[$i]['file']),'" on line "',$debug[$i]['line'],'" - ',\PHY\Debug::timer(),', server "',$this->host_info,'"</h2>',
			trim(str_replace(array('<','>'),array('&lt;','&gt;'),preg_replace('/([\t]+)/is','',self::$LAST))),';',
			'</pre>';
		}

		/**
		 * Turn on debugging.
		 *
		 * @param bool $show WARNING: If set to true it will show on live.
		 */
		public function show($show=false) {
			if((\PHY\Registry::get('config/site/production') && !\PHY\Registry::get('user/session')->admin) && $show !== true) return;
			$debug = debug_backtrace();
			$i = 0;
			echo '<pre style="background:#fsee;border:solid 1px #fcc;color:#800;line-height:130%;margin:5px;font:bold 16px \'courier new\';padding:5px;text-align:left;">',
			'SQL OUTPUT ACTIVATED: '.str_replace(BASE_PATH,'/',$debug[$i]['file']).'" on line "'.$debug[$i]['line'].'"',
			'</pre>';
			self::$DEBUG = array(microtime(true),memory_get_usage());
		}

	}
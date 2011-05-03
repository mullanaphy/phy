<?php

	namespace PHY;

# sphinx searchd client class

	class Sphinx {
#####	# Constants.
		const SEARCHD_COMMAND_SEARCH = 0;
		const SEARCHD_COMMAND_EXCERPT = 1;
		const SEARCHD_COMMAND_UPDATE = 2;
		const SEARCHD_COMMAND_KEYWORDS = 3;
		const SEARCHD_COMMAND_PERSIST = 4;
		const SEARCHD_COMMAND_STATUS = 5;
		const SEARCHD_COMMAND_QUERY = 6;

		# current client-side command implementation versions
		const VER_COMMAND_SEARCH = 0x116;
		const VER_COMMAND_EXCERPT = 0x100;
		const VER_COMMAND_UPDATE = 0x102;
		const VER_COMMAND_KEYWORDS = 0x100;
		const VER_COMMAND_STATUS = 0x100;
		const VER_COMMAND_QUERY = 0x100;

		# known searchd status codes
		const SEARCHD_OK = 0;
		const SEARCHD_ERROR = 1;
		const SEARCHD_RETRY = 2;
		const SEARCHD_WARNING = 3;

		# known match modes
		const MATCH_ALL = 0;
		const MATCH_ANY = 1;
		const MATCH_PHRASE = 2;
		const MATCH_BOOLEAN = 3;
		const MATCH_EXTENDED = 4;
		const MATCH_FULLSCAN = 5;
		const MATCH_EXTENDED2 = 6;	# extended engine V2(TEMPORARY, WILL BE REMOVED)
		# known ranking modes(ext2 only)
		const RANK_PROXIMITY_BM25 = 0;   # default mode, phrase proximity major factor and BM25 minor one
		const RANK_BM25 = 1;	 # statistical mode, BM25 ranking only(faster but worse quality)
		const RANK_NONE = 2;	 # no ranking, all matches get a weight of 1
		const RANK_WORDCOUNT = 3;	# simple word-count weighting, rank is a weighted sum of per-field keyword occurence counts
		const RANK_PROXIMITY = 4;
		const RANK_MATCHANY = 5;
		const RANK_FIELDMASK = 6;

		# known sort modes
		const SORT_RELEVANCE = 0;
		const SORT_ATTR_DESC = 1;
		const SORT_ATTR_ASC = 2;
		const SORT_TIME_SEGMENTS = 3;
		const SORT_EXTENDED = 4;
		const SORT_EXPR = 5;

		# known filter types
		const FILTER_VALUES = 0;
		const FILTER_RANGE = 1;
		const FILTER_FLOATRANGE = 2;

		# known attribute types
		const ATTR_INTEGER = 1;
		const ATTR_TIMESTAMP = 2;
		const ATTR_ORDINAL = 3;
		const ATTR_BOOL = 4;
		const ATTR_FLOAT = 5;
		const ATTR_BIGINT = 6;
		const ATTR_MULTI = 0x40000000;

		# known grouping functions
		const GROUPBY_DAY = 0;
		const GROUPBY_WEEK = 1;
		const GROUPBY_MONTH = 2;
		const GROUPBY_YEAR = 3;
		const GROUPBY_ATTR = 4;
		const GROUPBY_ATTRPAIR = 5;

#####	# Variables.

		private $_host = 'localhost',# searchd host(default is "localhost")
		$_port = 9312,# searchd port(default is 9312)
		$_path = false,# searchd path(default is false)
		$_socket = false,# searchd socket(default is false)
		$_offset = 0,# how many records to seek from result-set start(default is 0)
		$_limit = 20,# how many records to return from result-set starting at offset(default is 20)
		$_mode = self::MATCH_ALL,# query matching mode(default is self::MATCH_ALL)
		$_weights = array(),# per-field weights(default is 1 for all fields)
		$_sort = self::SORT_RELEVANCE,# match sorting mode(default is self::SORT_RELEVANCE)
		$_sortby = '',# attribute to sort by(defualt is "")
		$_min_id = 0,# min ID to match(default is 0, which means no limit)
		$_max_id = 0,# max ID to match(default is 0, which means no limit)
		$_filters = array(),# search filters
		$_groupby = '',# group-by attribute name
		$_groupfunc = self::GROUPBY_DAY,# group-by function(to pre-process group-by attribute value with)
		$_groupsort = '@group desc',# group-by sorting clause(to sort groups in result set with)
		$_groupdistinct = '',# group-by count-distinct attribute
		$_maxmatches = 1000,# max matches to retrieve
		$_cutoff = 0,# cutoff to stop searching at(default is 0)
		$_retrycount = 0,# distributed retries count
		$_retrydelay = 0,# distributed retries delay
		$_anchor = array(),# geographical anchor point
		$_indexweights = array(),# per-index weights
		$_ranker = self::RANK_PROXIMITY_BM25,# ranking mode(default is self::RANK_PROXIMITY_BM25)
		$_maxquerytime = 0,# max query time, milliseconds(default is 0, do not limit)
		$_fieldweights = array(),# per-field-name weights
		$_overrides = array(),# per-query attribute values overrides
		$_select = '*',# select-list(attributes or expressions, with optional aliases)

		$_error = '',# last error message
		$_warning = '',# last warning message
		$_connerror = false,# connection error vs remote error flag

		$_reqs = array(),# requests array for multi-query
		$_mbenc = '',# stored mbstring encoding
		$_arrayresult = false,# whether $result["matches"] should be a hash or an array
		$_timeout = 0;	   # connect timeout

#####	# Magic methods.		

		public function __destruct() {
			if($this->_socket !== false) fclose($this->_socket);
		}

#####	# Get methods.
		# get last error message(string)

		public function getLastError() {
			return $this->_error;
		}

		# get last warning message(string)

		public function getLastWarning() {
			return $this->_warning;
		}

		# get last error flag(to tell network connection errors from searchd errors or broken responses)

		public function isConnectError() {
			return $this->_connerror;
		}

#####	# Set methods.
		# set searchd host name(string) and port(integer)

		public function setServer($host,$port=0) {
			assert(is_string($host));
			if($host[0] === '/'):
				$this->_path = 'unix://'.$host;
				return;
			elseif(substr($host,0,7) === "unix://"):
				$this->_path = $host;
				return;
			endif;
			assert(is_int($port));
			$this->_host = $host;
			$this->_port = $port;
			$this->_path = '';
		}

		# set server connection timeout(0 to remove)

		public function setConnectTimeout($timeout) {
			assert(is_numeric($timeout));
			$this->_timeout = $timeout;
		}

		# set offset and count into result set,
		# and optionally set max-matches and cutoff limits

		public function setLimits($offset,$limit,$max=0,$cutoff=0) {
			assert(is_int($offset));
			assert(is_int($limit));
			assert($offset >= 0);
			assert($limit > 0);
			assert($max >= 0);
			$this->_offset = $offset;
			$this->_limit = $limit;
			if($max > 0) $this->_maxmatches = $max;
			if($cutoff > 0) $this->_cutoff = $cutoff;
		}

		# set maximum query time, in milliseconds, per-index
		# integer, 0 means "do not limit"

		public function setMaxQueryTime($max) {
			assert(is_int($max));
			assert($max >= 0);
			$this->_maxquerytime = $max;
		}

		# set matching mode

		public function setMatchMode($mode) {
			assert($mode == self::MATCH_ALL
				|| $mode == self::MATCH_ANY
				|| $mode == self::MATCH_PHRASE
				|| $mode == self::MATCH_BOOLEAN
				|| $mode == self::MATCH_EXTENDED
				|| $mode == self::MATCH_FULLSCAN
				|| $mode == self::MATCH_EXTENDED2);
			$this->_mode = $mode;
		}

		# set ranking mode

		public function setRankingMode($ranker) {
			assert($ranker == self::RANK_PROXIMITY_BM25
				|| $ranker == self::RANK_BM25
				|| $ranker == self::RANK_NONE
				|| $ranker == self::RANK_WORDCOUNT
				|| $ranker == self::RANK_PROXIMITY);
			$this->_ranker = $ranker;
		}

		# set matches sorting mode

		public function setSortMode($mode,$sortby='') {
			assert(
				$mode == self::SORT_RELEVANCE ||
				$mode == self::SORT_ATTR_DESC ||
				$mode == self::SORT_ATTR_ASC ||
				$mode == self::SORT_TIME_SEGMENTS ||
				$mode == self::SORT_EXTENDED ||
				$mode == self::SORT_EXPR);
			assert(is_string($sortby));
			assert($mode == self::SORT_RELEVANCE || strlen($sortby) > 0);
			$this->_sort = $mode;
			$this->_sortby = $sortby;
		}

		# bind per-field weights by order
		# DEPRECATED; use SetFieldWeights() instead

		public function setWeights($weights) {
			assert(is_array($weights));
			foreach($weights as $weight) assert(is_int($weight));
			$this->_weights = $weights;
		}

		# bind per-field weights by name

		public function setFieldWeights($weights) {
			assert(is_array($weights));
			foreach($weights as $name => $weight):
				assert(is_string($name));
				assert(is_int($weight));
			endforeach;
			$this->_fieldweights = $weights;
		}

		# bind per-index weights by name

		public function setIndexWeights($weights) {
			assert(is_array($weights));
			foreach($weights as $index => $weight):
				assert(is_string($index));
				assert(is_int($weight));
			endforeach;
			$this->_indexweights = $weights;
		}

		# set IDs range to match
		# only match records if document ID is beetwen $min and $max(inclusive)

		public function setIDRange($min,$max) {
			assert(is_numeric($min));
			assert(is_numeric($max));
			assert($min <= $max);
			$this->_min_id = $min;
			$this->_max_id = $max;
		}

		# set values set filter
		# only match records where $attribute value is in given set

		public function setFilter($attribute,$values,$exclude=false) {
			assert(is_string($attribute));
			assert(is_array($values));
			assert(count($values));
			if(is_array($values) && count($values)):
				foreach($values as $value) assert(is_numeric($value));
				$this->_filters[] = array(
					'type' => self::FILTER_VALUES,
					'attr' => $attribute,
					'exclude' => $exclude,
					'values' => $values
				);
			endif;
		}

		# set range filter
		# only match records if $attribute value is beetwen $min and $max(inclusive)

		public function setFilterRange($attribute,$min,$max,$exclude=false) {
			assert(is_string($attribute));
			assert(is_numeric($min));
			assert(is_numeric($max));
			assert($min <= $max);
			$this->_filters[] = array(
				'type' => self::FILTER_RANGE,
				'attr' => $attribute,
				'exclude' => $exclude,
				'min' => $min,
				'max' => $max
			);
		}

		# set float range filter
		# only match records if $attribute value is beetwen $min and $max(inclusive)

		public function setFilterFloatRange($attribute,$min,$max,$exclude=false) {
			assert(is_string($attribute));
			assert(is_float($min));
			assert(is_float($max));
			assert($min <= $max);
			$this->_filters[] = array(
				'type' => self::FILTER_FLOATRANGE,
				'attr' => $attribute,
				'exclude' => $exclude,
				'min' => $min,
				'max' => $max
			);
		}

		# setup anchor point for geosphere distance calculations
		# required to use @geodist in filters and sorting
		# latitude and longitude must be in radians

		public function setGeoAnchor($attrlat,$attrlong,$lat,$long) {
			assert(is_string($attrlat));
			assert(is_string($attrlong));
			assert(is_float($lat));
			assert(is_float($long));
			$this->_anchor = array(
				'attrlat' => $attrlat,
				'attrlong' => $attrlong,
				'lat' => $lat,
				'long' => $long
			);
		}

		# set grouping attribute and function

		public function setGroupBy($attribute,$func,$groupsort='@group desc') {
			assert(is_string($attribute));
			assert(is_string($groupsort));
			assert($func == self::GROUPBY_DAY
				|| $func == self::GROUPBY_WEEK
				|| $func == self::GROUPBY_MONTH
				|| $func == self::GROUPBY_YEAR
				|| $func == self::GROUPBY_ATTR
				|| $func == self::GROUPBY_ATTRPAIR);
			$this->_groupby = $attribute;
			$this->_groupfunc = $func;
			$this->_groupsort = $groupsort;
		}

		# set count-distinct attribute for group-by queries

		public function setGroupDistinct($attribute) {
			assert(is_string($attribute));
			$this->_groupdistinct = $attribute;
		}

		# set distributed retries count and delay

		public function setRetries($count,$delay=0) {
			assert(is_int($count) && $count >= 0);
			assert(is_int($delay) && $delay >= 0);
			$this->_retrycount = $count;
			$this->_retrydelay = $delay;
		}

		# set result set format(hash or array; hash by default)
		# PHP specific; needed for group-by-MVA result sets that may contain duplicate IDs

		public function setArrayResult($arrayresult) {
			assert(is_bool($arrayresult));
			$this->_arrayresult = $arrayresult;
		}

		# set attribute values override
		# there can be only one override per attribute
		# $values must be a hash that maps document IDs to attribute values

		public function setOverride($attrname,$attrtype,$values) {
			assert(is_string($attrname));
			assert(in_array($attrtype,array(self::ATTR_INTEGER,self::ATTR_TIMESTAMP,self::ATTR_BOOL,self::ATTR_FLOAT,self::ATTR_BIGINT)));
			assert(is_array($values));
			$this->_overrides[$attrname] = array(
				'attr' => $attrname,
				'type' => $attrtype,
				'values' => $values
			);
		}

		# set select-list(attributes or expressions), SQL-like syntax

		public function setSelect($select) {
			assert(is_string($select));
			$this->_select = $select;
		}

#####	# Reset methods.	
		# clear all filters(for multi-queries)

		public function resetFilters() {
			$this->_filters = array();
			$this->_anchor = array();
		}

		# clear groupby settings(for multi-queries)

		public function resetGroupBy() {
			$this->_groupby = '';
			$this->_groupfunc = self::GROUPBY_DAY;
			$this->_groupsort = '@group desc';
			$this->_groupdistinct = '';
		}

		# clear all attribute value overrides(for multi-queries)

		public function ResetOverrides() {
			$this->_overrides = array();
		}

#####	# Methods for generating and retrieving results.
		# connect to searchd server, run given search query through given indexes,
		# and return the search results

		public function query($query,$index='*',$comment='') {
			assert(empty($this->_reqs));

			$this->AddQuery($query,$index,$comment);
			$results = $this->RunQueries();
			$this->_reqs = array(); # just in case it failed too early

			if(!is_array($results)) return false;# probably network error; error message should be already filled

			$this->_error = $results[0]['error'];
			$this->_warning = $results[0]['warning'];
			if($results[0]['status'] == self::SEARCHD_ERROR) return false;
			else return $results[0];
		}

		# add query to multi-query batch
		# returns index into results array from RunQueries() call

		public function addQuery($query,$index='*',$comment='') {
			# mbstring workaround
			$this->_mbstringPush();

			# build request
			$req = pack('NNNNN',$this->_offset,$this->_limit,$this->_mode,$this->_ranker,$this->_sort); # mode and limits
			$req .= pack('N',strlen($this->_sortby)).$this->_sortby;
			$req .= pack('N',strlen($query)).$query; # query itself
			$req .= pack('N',count($this->_weights)); # weights
			foreach($this->_weights as $weight) $req .= pack('N',(int)$weight);
			$req .= pack('N',strlen($index)).$index; # indexes
			$req .= pack('N',1); # id64 range marker
			$req .= $this->packU64($this->_min_id).$this->packU64($this->_max_id); // id64 range
			// filters
			$req .= pack('N',count($this->_filters));
			foreach($this->_filters as $filter) {
				$req .= pack('N',strlen($filter["attr"])).$filter["attr"];
				$req .= pack('N',$filter["type"]);
				switch($filter["type"]) {
					case self::FILTER_VALUES:
						$req .= pack('N',count($filter["values"]));
						foreach($filter["values"] as $value) $req .= $this->packI64($value);
						break;

					case self::FILTER_RANGE:
						$req .= $this->packI64($filter["min"]).$this->packI64($filter["max"]);
						break;

					case self::FILTER_FLOATRANGE:
						$req .= $this->_packFloat($filter["min"]).$this->_packFloat($filter["max"]);
						break;

					default:
						assert(0 && "internal error: unhandled filter type");
				}
				$req .= pack('N',$filter["exclude"]);
			}

			// group-by clause, max-matches count, group-sort clause, cutoff count
			$req .= pack("NN",$this->_groupfunc,strlen($this->_groupby)).$this->_groupby;
			$req .= pack('N',$this->_maxmatches);
			$req .= pack('N',strlen($this->_groupsort)).$this->_groupsort;
			$req .= pack("NNN",$this->_cutoff,$this->_retrycount,$this->_retrydelay);
			$req .= pack('N',strlen($this->_groupdistinct)).$this->_groupdistinct;

			// anchor point
			if(empty($this->_anchor)) {
				$req .= pack('N',0);
			}
			else {
				$a = & $this->_anchor;
				$req .= pack('N',1);
				$req .= pack('N',strlen($a["attrlat"])).$a["attrlat"];
				$req .= pack('N',strlen($a["attrlong"])).$a["attrlong"];
				$req .= $this->_packFloat($a["lat"]).$this->_packFloat($a["long"]);
			}

			// per-index weights
			$req .= pack('N',count($this->_indexweights));
			foreach($this->_indexweights as $idx => $weight) $req .= pack('N',strlen($idx)).$idx.pack('N',$weight);

			// max query time
			$req .= pack('N',$this->_maxquerytime);

			// per-field weights
			$req .= pack('N',count($this->_fieldweights));
			foreach($this->_fieldweights as $field => $weight) $req .= pack('N',strlen($field)).$field.pack('N',$weight);

			// comment
			$req .= pack('N',strlen($comment)).$comment;

			// attribute overrides
			$req .= pack('N',count($this->_overrides));
			foreach($this->_overrides as $key => $entry) {
				$req .= pack('N',strlen($entry["attr"])).$entry["attr"];
				$req .= pack("NN",$entry["type"],count($entry["values"]));
				foreach($entry["values"] as $id => $val) {
					assert(is_numeric($id));
					assert(is_numeric($val));

					$req .= $this->packU64($id);
					switch($entry["type"]) {
						case self::ATTR_FLOAT: $req .= $this->_packFloat($val);
							break;
						case self::ATTR_BIGINT: $req .= $this->packI64($val);
							break;
						default: $req .= pack('N',$val);
							break;
					}
				}
			}

			// select-list
			$req .= pack('N',strlen($this->_select)).$this->_select;

			// mbstring workaround
			$this->_mbstringPop();

			// store request to requests array
			$this->_reqs[] = $req;
			return count($this->_reqs) - 1;
		}

		/// connect to searchd, run queries batch, and return an array of result sets
		public function runQueries() {
			if(empty($this->_reqs)) {
				$this->_error = "no queries defined, issue AddQuery() first";
				return false;
			}

			// mbstring workaround
			$this->_mbstringPush();

			if(!($fp = $this->_connect())) {
				$this->_mbstringPop();
				return false;
			}

			// send query, get response
			$nreqs = count($this->_reqs);
			$req = join("",$this->_reqs);
			$len = 4 + strlen($req);
			$req = pack("nnNN",self::SEARCHD_COMMAND_SEARCH,self::VER_COMMAND_SEARCH,$len,$nreqs).$req; // add header

			if(!($this->_send($fp,$req,$len + 8)) ||
				!($response = $this->_getResponse($fp,self::VER_COMMAND_SEARCH))) {
				$this->_mbstringPop();
				return false;
			}

			// query sent ok; we can reset reqs now
			$this->_reqs = array();

			// parse and return response
			return $this->_parseSearchResponse($response,$nreqs);
		}

#####	# Private methods.

		private function _send($handle,$data,$length) {
			if(feof($handle) || fwrite($handle,$data,$length) !== $length):
				$this->_error = 'connection unexpectedly closed(timed out?)';
				$this->_connerror = true;
				return false;
			else:
				return true;
			endif;
		}

		# enter mbstring workaround mode.

		private function _mbstringPush() {
			$this->_mbenc = '';
			if(ini_get("mbstring.func_overload") & 2):
				$this->_mbenc = mb_internal_encoding();
				mb_internal_encoding("latin1");
			endif;
		}

		# leave mbstring workaround mode

		private function _mbstringPop() {
			if($this->_mbenc) mb_internal_encoding($this->_mbenc);
		}

		# connect to searchd server

		private function _connect() {
			if($this->_socket !== false):
				# we are in persistent connection mode, so we have a socket
				# however, need to check whether it's still alive
				if(!@feof($this->_socket)) return $this->_socket;

				# force reopen
				$this->_socket = false;
			endif;

			$errno = 0;
			$errstr = '';
			$this->_connerror = false;

			if($this->_path):
				$host = $this->_path;
				$port = 0;
			else:
				$host = $this->_host;
				$port = $this->_port;
			endif;

			if($this->_timeout <= 0) $fp = @fsockopen($host,$port,$errno,$errstr);
			else $fp = @fsockopen($host,$port,$errno,$errstr,$this->_timeout);

			if(!$fp):
				if($this->_path) $location = $this->_path;
				else $location = $this->_host.':'.$this->_port;

				$errstr = trim($errstr);
				$this->_error = 'connection to '.$location.' failed(errno='.$errno.', msg='.$errstr.')';
				$this->_connerror = true;
				return false;
			endif;

			# send my version
			# this is a subtle part. we must do it before(!) reading back from searchd.
			# because otherwise under some conditions(reported on FreeBSD for instance)
			# TCP stack could throttle write-write-read pattern because of Nagle.
			if(!$this->_send($fp,pack('N',1),4)):
				fclose($fp);
				$this->_error = 'failed to send client protocol version';
				return false;
			endif;

			# check version
			list(,$v) = unpack('N*',fread($fp,4));
			$v = (int)$v;
			if($v < 1):
				fclose($fp);
				$this->_error = 'expected searchd protocol version 1+, got version \''.$v.'\'';
				return false;
			endif;

			return $fp;
		}

		# get and check response packet from searchd server

		private function _getResponse($fp,$client_ver) {
			$response = '';
			$len = 0;

			$header = fread($fp,8);
			if(strlen($header) == 8):
				list($status,$ver,$len) = array_values(unpack('n2a/Nb',$header));
				$left = $len;
				while($left > 0 && !feof($fp)):
					$chunk = fread($fp,$left);
					if($chunk):
						$response .= $chunk;
						$left -= strlen($chunk);
					endif;
				endwhile;
			endif;
			if($this->_socket === false) fclose($fp);

			# check response
			$read = strlen($response);
			if(!$response || $read != $len):
				$this->_error = (
					($len)?'failed to read searchd response(status='.$status.', ver='.$ver.', len='.$len.', read='.$read.')':'received zero-sized searchd response'
					);
				return false;
			endif;

			# check status
			if($status == self::SEARCHD_WARNING):
				list(,$wlen) = unpack('N*',substr($response,0,4));
				$this->_warning = substr($response,4,$wlen);
				return substr($response,4 + $wlen);
			elseif($status == self::SEARCHD_ERROR):
				$this->_error = 'searchd error: '.substr($response,4);
				return false;
			elseif($status == self::SEARCHD_RETRY):
				$this->_error = 'temporary searchd error: '.substr($response,4);
				return false;
			elseif($status != self::SEARCHD_OK):
				$this->_error = 'unknown status code \''.$status.'\'';
				return false;
			endif;

			# check version
			if($ver < $client_ver) $this->_warning = sprintf(
					'searchd command v.%d.%d older than client\'s v.%d.%d, some options might not work',$ver >> 8,$ver & 0xff,$client_ver >> 8,$client_ver & 0xff
				);

			return $response;
		}

		# helper to pack floats in network byte order

		private function _packFloat($f) {
			$t1 = pack('f',$f); # machine order
			list(,$t2) = unpack('L*',$t1); # int in machine order
			return pack('N',$t2);
		}

		# parse and return search query(or queries) response

		private function _parseSearchResponse($response,$nreqs) {
			$p = 0; # current position
			$max = strlen($response); # max position for checks, to protect against broken responses

			$results = array();
			for($ires = 0; $ires < $nreqs && $p < $max; ++$ires):
				$results[] = array();
				$result = & $results[$ires];

				$result['error'] = '';
				$result['warning'] = '';

				# extract status
				list(,$status) = unpack('N*',substr($response,$p,4));
				$p += 4;
				$result['status'] = $status;
				if($status != self::SEARCHD_OK):
					list(,$len) = unpack('N*',substr($response,$p,4));
					$p += 4;
					$message = substr($response,$p,$len);
					$p += $len;
					if($status == self::SEARCHD_WARNING):
						$result['warning'] = $message;
					else:
						$result['error'] = $message;
						continue;
					endif;
				endif;

				# read schema
				$fields = array();
				$attrs = array();

				list(,$nfields) = unpack('N*',substr($response,$p,4));
				$p += 4;
				while($nfields-- > 0 && $p < $max):
					list(,$len) = unpack('N*',substr($response,$p,4));
					$p += 4;
					$fields[] = substr($response,$p,$len);
					$p += $len;
				endwhile;
				$result['fields'] = $fields;

				list(,$nattrs) = unpack('N*',substr($response,$p,4));
				$p += 4;
				while($nattrs-- > 0 && $p < $max):
					list(,$len) = unpack('N*',substr($response,$p,4));
					$p += 4;
					$attr = substr($response,$p,$len);
					$p += $len;
					list(,$type) = unpack('N*',substr($response,$p,4));
					$p += 4;
					$attrs[$attr] = $type;
				endwhile;
				$result['attrs'] = $attrs;

				# read match count
				list(,$count) = unpack('N*',substr($response,$p,4));
				$p += 4;
				list(,$id64) = unpack('N*',substr($response,$p,4));
				$p += 4;

				# read matches
				$idx = -1;
				while($count-- > 0 && $p < $max):
					# index into result array
					$idx++;

					# parse document id and weight
					if($id64):
						$doc = $this->unpackU64(substr($response,$p,8));
						$p += 8;
						list(,$weight) = unpack('N*',substr($response,$p,4));
						$p += 4;
					else:
						list($doc,$weight) = array_values(unpack('N*N*',substr($response,$p,8)));
						$p += 8;
						$doc = $this->fixUint($doc);
					endif;
					$weight = sprintf('%u',$weight);

					# create match entry
					if($this->_arrayresult) $result['matches'][$idx] = array(
							'id' => $doc,
							'weight' => $weight
						);
					else $result['matches'][$doc]['weight'] = $weight;

					# parse and create attributes
					$attrvals = array();
					foreach($attrs as $attr => $type):
						# handle 64bit ints
						if($type == self::ATTR_BIGINT):
							$attrvals[$attr] = $this->unpackI64(substr($response,$p,8));
							$p += 8;
							continue;

						# handle floats
						elseif($type == self::ATTR_FLOAT):
							list(,$uval) = unpack('N*',substr($response,$p,4));
							$p += 4;
							list(,$fval) = unpack('f*',pack('L',$uval));
							$attrvals[$attr] = $fval;
							continue;
						endif;

						# handle everything else as unsigned ints
						list(,$val) = unpack('N*',substr($response,$p,4));
						$p += 4;
						if($type & self::ATTR_MULTI):
							$attrvals[$attr] = array();
							$nvalues = $val;
							while($nvalues-- > 0 && $p < $max):
								list(,$val) = unpack('N*',substr($response,$p,4));
								$p += 4;
								$attrvals[$attr][] = $this->fixUint($val);
							endwhile;
						else:
							$attrvals[$attr] = $this->fixUint($val);
						endif;
					endforeach;

					if($this->_arrayresult) $result['matches'][$idx]['attrs'] = $attrvals;
					else $result['matches'][$doc]['attrs'] = $attrvals;
				endwhile;

				list($total,$total_found,$msecs,$words) = array_values(unpack('N*N*N*N*',substr($response,$p,16)));
				$result['total'] = sprintf('%u',$total);
				$result['total_found'] = sprintf('%u',$total_found);
				$result['time'] = sprintf('%.3f',$msecs / 1000);
				$p += 16;

				while($words-- > 0 && $p < $max):
					list(,$len) = unpack('N*',substr($response,$p,4));
					$p += 4;
					$word = substr($response,$p,$len);
					$p += $len;
					list($docs,$hits) = array_values(unpack('N*N*',substr($response,$p,8)));
					$p += 8;
					$result['words'][$word] = array(
						'docs' => sprintf('%u',$docs),
						'hits' => sprintf('%u',$hits)
					);
				endwhile;
			endfor;

			$this->_mbstringPop();
			return $results;
		}

#####	# Building methods.
		# connect to searchd server, and generate exceprts(snippets)
		# of given documents for given query. returns false on failure,
		# an array of snippets on success

		public function buildExcerpts($docs,$index,$words,$opts=array()) {
			assert(is_array($docs));
			assert(is_string($index));
			assert(is_string($words));
			assert(is_array($opts));

			$this->_mbstringPush();

			if(!($fp = $this->_connect())):
				$this->_mbstringPop();
				return false;
			endif;

			# fix options.
			if(!isset($opts['before_match'])) $opts['before_match'] = '<strong>';
			if(!isset($opts['after_match'])) $opts['after_match'] = '</strong>';
			if(!isset($opts['chunk_separator'])) $opts['chunk_separator'] = ' ... ';
			if(!isset($opts['limit'])) $opts['limit'] = 256;
			if(!isset($opts['around'])) $opts['around'] = 5;
			if(!isset($opts['exact_phrase'])) $opts['exact_phrase'] = false;
			if(!isset($opts['single_passage'])) $opts['single_passage'] = false;
			if(!isset($opts['use_boundaries'])) $opts['use_boundaries'] = false;
			if(!isset($opts['weight_order'])) $opts['weight_order'] = false;

			# build request
			# v.1.0 req
			$flags = 1; # remove spaces
			if($opts['exact_phrase']) $flags |= 2;
			if($opts['single_passage']) $flags |= 4;
			if($opts['use_boundaries']) $flags |= 8;
			if($opts['weight_order']) $flags |= 16;
			$req = pack('NN',0,$flags); # mode=0, flags=$flags
			$req .= pack('N',strlen($index)).$index; # req index
			$req .= pack('N',strlen($words)).$words; # req words
			# options
			$req .= pack('N',strlen($opts['before_match'])).$opts['before_match'];
			$req .= pack('N',strlen($opts['after_match'])).$opts['after_match'];
			$req .= pack('N',strlen($opts['chunk_separator'])).$opts['chunk_separator'];
			$req .= pack('N',(int)$opts['limit']);
			$req .= pack('N',(int)$opts['around']);

			# documents
			$req .= pack('N',count($docs));
			foreach($docs as $doc):
				assert(is_string($doc));
				$req .= pack('N',strlen($doc)).$doc;
			endforeach;

			# send query, get response
			$len = strlen($req);
			$req = pack('nnN',self::SEARCHD_COMMAND_EXCERPT,self::VER_COMMAND_EXCERPT,$len).$req; # add header
			if(!($this->_send($fp,$req,$len + 8)) || !($response = $this->_getResponse($fp,self::VER_COMMAND_EXCERPT))):
				$this->_mbstringPop();
				return false;
			endif;

			# parse response
			$pos = 0;
			$res = array();
			$rlen = strlen($response);
			for($i = 0; $i < count($docs); ++$i):
				list(,$len) = unpack('N*',substr($response,$pos,4));
				$pos += 4;

				if($pos + $len > $rlen):
					$this->_error = 'incomplete reply';
					$this->_mbstringPop();
					return false;
				endif;
				$res[] = $len?substr($response,$pos,$len):'';
				$pos += $len;
			endfor;

			$this->_mbstringPop();
			return $res;
		}

		# connect to searchd server, and generate keyword list for a given query
		# returns false on failure,
		# an array of words on success

		public function buildKeywords($query,$index,$hits) {
			assert(is_string($query));
			assert(is_string($index));
			assert(is_bool($hits));

			$this->_mbstringPush();

			if(!($fp = $this->_connect())):
				$this->_mbstringPop();
				return false;
			endif;

			# build request
			# v.1.0 req
			$req = pack('N',strlen($query)).$query; # req query
			$req .= pack('N',strlen($index)).$index; # req index
			$req .= pack('N',(int)$hits);

			# send query, get response
			$len = strlen($req);
			$req = pack('nnN',self::SEARCHD_COMMAND_KEYWORDS,self::VER_COMMAND_KEYWORDS,$len).$req; # add header
			if(!($this->_send($fp,$req,$len + 8)) || !($response = $this->_getResponse($fp,self::VER_COMMAND_KEYWORDS))):
				$this->_mbstringPop();
				return false;
			endif;

			# parse response
			$pos = 0;
			$res = array();
			$rlen = strlen($response);
			list(,$nwords) = unpack('N*',substr($response,$pos,4));
			$pos += 4;
			for($i = 0; $i < $nwords; ++$i):
				list(,$len) = unpack('N*',substr($response,$pos,4));
				$pos += 4;
				$tokenized = $len?substr($response,$pos,$len):'';
				$pos += $len;
				list(,$len) = unpack('N*',substr($response,$pos,4));
				$pos += 4;
				$normalized = $len?substr($response,$pos,$len):'';
				$pos += $len;
				$res[] = array(
					'tokenized' => $tokenized,
					'normalized' => $normalized
				);
				if($hits):
					list($ndocs,$nhits) = array_values(unpack('N*N*',substr($response,$pos,8)));
					$pos += 8;
					$res[$i]['docs'] = $ndocs;
					$res[$i]['hits'] = $nhits;
				endif;
				if($pos > $rlen):
					$this->_error = 'incomplete reply';
					$this->_mbstringPop();
					return false;
				endif;
			endfor;

			$this->_mbstringPop();
			return $res;
		}

		public function escapeString($string) {
			static $from = array('\\','(',')','|','-','!','@','~','"','&','/','^','$','=');
			static $to = array('\\\\','\(','\)','\|','\-','\!','\@','\~','\"','\&','\/','\^','\$','\=');
			return str_replace($from,$to,$string);
		}

		# batch update given attributes in given rows in given indexes
		# returns amount of updated documents(0 or more) on success, or -1 on failure

		function updateAttributes($index,$attrs,$values,$mva=false) {
			# verify everything
			assert(is_string($index));
			assert(is_bool($mva));

			assert(is_array($attrs));
			foreach($attrs as $attr) assert(is_string($attr));

			assert(is_array($values));
			foreach($values as $id => $entry):
				assert(is_numeric($id));
				assert(is_array($entry));
				assert(count($entry) == count($attrs));
				foreach($entry as $v):
					if($mva):
						assert(is_array($v));
						foreach($v as $vv) assert(is_int($vv));
					else:
						assert(is_int($v));
					endif;
				endforeach;
			endforeach;

			# build request
			$req = pack('N',strlen($index)).$index;

			$req .= pack('N',count($attrs));
			foreach($attrs as $attr):
				$req .= pack('N',strlen($attr)).$attr;
				$req .= pack('N',$mva?1:0);
			endforeach;

			$req .= pack('N',count($values));
			foreach($values as $id => $entry):
				$req .= $this->packU64($id);
				foreach($entry as $v):
					$req .= pack('N',$mva?count($v):$v);
					if($mva) foreach($v as $vv) $req .= pack('N',$vv);
				endforeach;
			endforeach;

			# connect, send query, get response
			if(!($fp = $this->_connect())) return -1;

			$len = strlen($req);
			$req = pack('nnN',self::SEARCHD_COMMAND_UPDATE,self::VER_COMMAND_UPDATE,$len).$req; # add header
			if(!$this->_send($fp,$req,$len + 8) || !($response = $this->_getResponse($fp,self::VER_COMMAND_UPDATE))) return -1;

			# parse response
			list(,$updated) = unpack('N*',substr($response,0,4));
			return $updated;
		}

#####	# Connection methods.
		# persistent connections

		public function open() {
			if($this->_socket !== false):
				$this->_error = 'already connected';
				return false;
			elseif(!$fp = $this->_connect()):
				return false;
			endif;

			# command, command version = 0, body length = 4, body = 1
			$req = pack('nnNN',self::SEARCHD_COMMAND_PERSIST,0,4,1);
			if(!$this->_send($fp,$req,12)) return false;

			$this->_socket = $fp;
			return true;
		}

		# close connection.

		public function close() {
			if($this->_socket === false):
				$this->_error = 'not connected';
				return false;
			endif;

			fclose($this->_socket);
			$this->_socket = false;

			return true;
		}

		# status

		public function status() {
			$this->_mbstringPush();
			if(!($fp = $this->_connect())):
				$this->_mbstringPop();
				return false;
			endif;

			$req = pack('nnNN',self::SEARCHD_COMMAND_STATUS,self::VER_COMMAND_STATUS,4,1); # len=4, body=1
			if(!($this->_send($fp,$req,12)) || !($response = $this->_getResponse($fp,self::VER_COMMAND_STATUS))):
				$this->_mbstringPop();
				return false;
			endif;

			$res = substr($response,4); # just ignore length, error handling, etc
			$p = 0;
			list($rows,$cols) = array_values(unpack('N*N*',substr($response,$p,8)));
			$p += 8;

			$res = array();
			for($i = 0; $i < $rows; ++$i):
				for($j = 0; $j < $cols; ++$j):
					list(,$len) = unpack('N*',substr($response,$p,4));
					$p += 4;
					$res[$i][] = substr($response,$p,$len);
					$p += $len;
				endfor;
			endfor;

			$this->_mbstringPop();
			return $res;
		}

#####	# Helper methods.

		private function packI64($v) {
			assert(is_numeric($v));

			# x64
			if(PHP_INT_SIZE >= 8):
				$v = (int)$v;
				return pack('NN',$v >> 32,$v & 0xFFFFFFFF);

			# x32, int
			elseif(is_int($v)):
				return pack('NN',$v < 0?-1:0,$v);

			# x32, bcmath	
			elseif(function_exists('bcmul')):
				if(bccomp($v,0) == -1) $v = bcadd("18446744073709551616",$v);
				$h = bcdiv($v,'4294967296',0);
				$l = bcmod($v,'4294967296');
				return pack('NN',(float)$h,(float)$l); # conversion to float is intentional; int would lose 31st bit
			endif;

			# x32, no-bcmath
			$p = max(0,strlen($v) - 13);
			$lo = abs((float)substr($v,$p));
			$hi = abs((float)substr($v,0,$p));

			$m = $lo + $hi * 1316134912.0; #(10 ^ 13) %(1 << 32) = 1316134912
			$q = floor($m / 4294967296.0);
			$l = $m - ($q * 4294967296.0);
			$h = $hi * 2328.0 + $q; #(10 ^ 13) /(1 << 32) = 2328

			if($v < 0):
				if($l == 0):
					$h = 4294967296.0 - $h;
				else:
					$h = 4294967295.0 - $h;
					$l = 4294967296.0 - $l;
				endif;
			endif;
			return pack('NN',$h,$l);
		}

		# pack 64-bit unsigned

		private function packU64($v) {
			assert(is_numeric($v));

			# x64
			if(PHP_INT_SIZE >= 8):
				assert($v >= 0);

				# x64, int
				if(is_int($v)):
					return pack('NN',$v >> 32,$v & 0xFFFFFFFF);

				# x64, bcmath
				elseif(function_exists('bcmul')):
					$h = bcdiv($v,4294967296,0);
					$l = bcmod($v,4294967296);
					return pack('NN',$h,$l);
				endif;

				# x64, no-bcmath
				$p = max(0,strlen($v) - 13);
				$lo = (int)substr($v,$p);
				$hi = (int)substr($v,0,$p);

				$m = $lo + $hi * 1316134912;
				$l = $m % 4294967296;
				$h = $hi * 2328 + (int)($m / 4294967296);

				return pack('NN',$h,$l);

			# x32, int
			elseif(is_int($v)):
				return pack('NN',0,$v);

			# x32, bcmath
			elseif(function_exists('bcmul')):
				$h = bcdiv($v,'4294967296',0);
				$l = bcmod($v,'4294967296');
				return pack('NN',(float)$h,(float)$l); # conversion to float is intentional; int would lose 31st bit
			endif;

			# x32, no-bcmath
			$p = max(0,strlen($v) - 13);
			$lo = (float)substr($v,$p);
			$hi = (float)substr($v,0,$p);

			$m = $lo + $hi * 1316134912.0;
			$q = floor($m / 4294967296.0);
			$l = $m - ($q * 4294967296.0);
			$h = $hi * 2328.0 + $q;

			return pack('NN',$h,$l);
		}

		# unpack 64-bit unsigned

		private function unpackU64($v) {
			list($hi,$lo) = array_values(unpack('N*N*',$v));

			if(PHP_INT_SIZE >= 8):
				if($hi < 0) $hi += ( 1 << 32);# because php 5.2.2 to 5.2.5 is totally fucked up again
				if($lo < 0) $lo += ( 1 << 32);

				# x64, int
				if($hi <= 2147483647) return($hi << 32) + $lo;

				# x64, bcmath
				elseif(function_exists('bcmul')) return bcadd($lo,bcmul($hi,'4294967296'));

				# x64, no-bcmath
				$C = 100000;
				$h = ((int)($hi / $C) << 32) + (int)($lo / $C);
				$l = (($hi % $C) << 32) + ($lo % $C);
				if($l > $C):
					$h +=(int)($l / $C);
					$l = $l % $C;
				endif;

				if($h == 0) return $l;
				return sprintf('%d%05d',$h,$l);

			# x32, int
			elseif($hi == 0):
				if($lo > 0) return $lo;
				return sprintf('%u',$lo);
			endif;

			$hi = sprintf('%u',$hi);
			$lo = sprintf('%u',$lo);

			# x32, bcmath
			if(function_exists('bcmul')) return bcadd($lo,bcmul($hi,'4294967296'));

			# x32, no-bcmath
			$hi = (float)$hi;
			$lo = (float)$lo;

			$q = floor($hi / 10000000.0);
			$r = $hi - $q * 10000000.0;
			$m = $lo + $r * 4967296.0;
			$mq = floor($m / 10000000.0);
			$l = $m - $mq * 10000000.0;
			$h = $q * 4294967296.0 + $r * 429.0 + $mq;

			$h = sprintf('%.0f',$h);
			$l = sprintf('%07.0f',$l);
			if($h == '0') return sprintf('%.0f',(float)$l);
			return $h.$l;
		}

		# unpack 64-bit signed

		private function unpackI64($v) {
			list($hi,$lo) = array_values(unpack('N*N*',$v));

			# x64
			if(PHP_INT_SIZE >= 8):
				if($hi < 0) $hi += ( 1 << 32);# because php 5.2.2 to 5.2.5 is totally fucked up again
				if($lo < 0) $lo += ( 1 << 32);

				return($hi << 32) + $lo;

			# x32, int
			elseif($hi == 0):
				if($lo > 0) return $lo;
				return sprintf('%u',$lo);

			# x32, int
			elseif($hi == -1):
				if($lo < 0) return $lo;
				return sprintf('%.0f',$lo - 4294967296.0);
			endif;

			$neg = '';
			$c = 0;
			if($hi < 0):
				$hi = ~$hi;
				$lo = ~$lo;
				$c = 1;
				$neg = '-';
			endif;

			$hi = sprintf('%u',$hi);
			$lo = sprintf('%u',$lo);

			# x32, bcmath
			if(function_exists('bcmul')) return $neg.bcadd(bcadd($lo,bcmul($hi,'4294967296')),$c);

			# x32, no-bcmath
			$hi = (float)$hi;
			$lo = (float)$lo;

			$q = floor($hi / 10000000.0);
			$r = $hi - $q * 10000000.0;
			$m = $lo + $r * 4967296.0;
			$mq = floor($m / 10000000.0);
			$l = $m - $mq * 10000000.0 + $c;
			$h = $q * 4294967296.0 + $r * 429.0 + $mq;
			if($l == 10000000):
				$l = 0;
				$h += 1;
			endif;

			$h = sprintf('%.0f',$h);
			$l = sprintf('%07.0f',$l);
			if($h == '0') return $neg.sprintf('%.0f',(float)$l);
			return $neg.$h.$l;
		}

		private function fixUint($value) {
			if(PHP_INT_SIZE >= 8):
				# x64 route, workaround broken unpack() in 5.2.2+
				if($value < 0) $value += ( 1 << 32);
				return $value;
			else:
				# x32 route, workaround php signed/unsigned braindamage
				return sprintf("%u",$value);
			endif;
		}

	}

?>
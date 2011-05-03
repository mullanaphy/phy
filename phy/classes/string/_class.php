<?php

	namespace PHY;
	
	/**
	 * Different static methods for dealing with String output.
	 *
	 * @category String
	 * @package String
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	final class String {

		static private $Iterators = array();

		const BITLY_USERNAME = '';
		const BITLY_API_KEY = '';
		const BITLY_VERSION = '2.0.1';

		/**
		 * Generate a bit.ly url.
		 *
		 * @param string $url
		 * @param bool $json If true method will return JSON data.
		 * @return string
		 */
		static public function bitly($url='',$json=false) {
			if(!$url) return false;

			ob_start();
			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,'http://api.bit.ly/shorten?version='.self::BITLY_VERSION.'&longUrl='.urlencode($url).'&login='.self::BITLY_USERNAME.'&apiKey='.self::BITLY_API_KEY.'&format=json');
			curl_setopt($curl,CURLOPT_HEADER,0);
			curl_exec($curl);
			curl_close($curl);
			$response = ob_get_contents();
			ob_clean();

			$response = @json_decode($response,true);
			if(!$json && isset($response['results'][$url]['shortUrl'])) return $response['results'][$url]['shortUrl'];
			else return $response;
		}

		/**
		 * Convert bytes into a human readable version.
		 *
		 * @param int $size
		 * @return string
		 */
		static public function bytes($size=0) {
			$size = (int)$size;
			if(!$size) return 0;
			elseif($size < 0) $sign = -1;
			else $sign = 1;
			$size = abs($size);
			$units = array('b','kb','mb','gb','tb','pb');
			return ($sign * (round($size / pow(1024,($i = floor(log($size,1024)))),2))).' '.$units[$i];
		}

		/**
		 * Fix capitalization of names.
		 *
		 * @param string $string
		 * @return string
		 */
		static public function capitalize($string='') {
			if(!$string) return '';
			else $string = (string)$string;

			# Prefixes for special cases.
			$prefixes = array('mac','mc');

			# Separates each name so they can be processed individually.
			if(!is_array($string)) $string = ($string?explode(' ',$string):array());

			# Loops through each name.
			foreach($string as $key => $value):

				# If name is in all uppercase or all lowercase, this name will be changed.
				if($value === strtoupper($value) || $value === strtolower($value)):

					# The new value with proper capitalization.
					$value = ucfirst(strtolower($value));

					# Updates the name in the array.
					$string[$key] = $value;

					# Loops through the special case prefixes.
					foreach($prefixes as $prefix):

						# So code below knows how many letters into the name to go.
						$prefix_length = strlen($prefix);

						# If the name starts with the prefix. Then we'll modify the capitalization.
						if(strtolower(substr($value,0,$prefix_length)) === $prefix) $string[$key] = ucfirst($prefix).ucfirst(substr($value,$prefix_length));
					endforeach;
				endif;
			endforeach;

			# Puts the entire name back together.
			return implode(' ',$string);
		}

		/**
		 * Return a relative time difference string.
		 *
		 * @param datetime $date_1
		 * @param datetime $date_2 time() by default
		 * @return string
		 */
		static public function date($date=0,$time=0) {
			if(!$date) return '';

			# Convert times.
			if(!is_numeric($date)) $date = strtotime($date);
			if(!is_numeric($time)) $time = $time?strtotime($time):0;

			if(!$time) $time = time();
			$date = (int)$date;
			$time = (int)$time;

			# Grab differences.
			$days = abs(intval(($date - $time) / INT_DAY));
			$months = floor($days / 30);
			$years = floor($months / 12);
			$seconds = abs(($date - $time) % INT_DAY);
			$hours = (int)(($seconds) / INT_HOUR);
			$seconds = ($seconds) % INT_HOUR;
			$minutes = (int)(($seconds) / 60);
			$seconds = ($seconds) % 60;

			# Set prefix\suffix.
			if($date > $time):
				$prefix = 'in ';
				$suffix = '';
			else:
				$prefix = '';
				$suffix = ' ago';
			endif;

			# Return our results.
			switch(true):
				case($years):
					return 'over '.($years == 1?'a':$years).' '.self::pluralize('year',$years).$suffix;
					break;
				case($months):
					return $prefix.$months.' '.self::pluralize('month',$months).$suffix;
					break;
				case($days):
					return $prefix.$days.' '.self::pluralize('day',$days).$suffix;
					break;
				case($hours):
					return $prefix.$hours.' '.self::pluralize('hour',$hours).$suffix;
					break;
				case($minutes):
					return $prefix.$minutes.' '.self::pluralize('minute',$minutes).$suffix;
					break;
				default:
					return $prefix.$seconds.' '.self::pluralize('second',$seconds).$suffix;
					break;
			endswitch;
		}

		/**
		 * Convert int seconds into a displayable version of minutes (zero
		 * padded).
		 *
		 * @param int $time
		 * @return string
		 */
		static public function minutes($time=false) {
			if(!$time) return '00:00';
			else return sprintf('%02d:%02d',(int)(floor($time / 60)),(int)($time % 60));
		}

		/**
		 * Parse a string to make it appealing on the frontend. Also parses
		 * urls and other in house actions.
		 *
		 * @param string $string
		 * @return string
		 */
		static public function parse($string='') {
			if(is_array($string)) $string = join('',$string);
			elseif(!is_string($string)) return '';

			# Parse URLs.
			$string = preg_replace(
					array(
						'#((?:https?|ftp)://\S+[[:alnum:]]/?)#si',
						'#((?<!//)(www\.\S+[[:alnum:]]/?))#si'
					),
					array(
						'<a href="$1" rel="nofollow" target="_blank">$1</a>',
						'<a href="http://$1" rel="nofollow" target="_blank">$1</a>'
					),
					$string
			);

			# Add line breaks where it's appropriate.
			$string = nl2br($string);

			return $string;
		}

		/**
		 * Oneway encrypt a password.
		 *
		 * @param string $password
		 * @param string $salt
		 * @param string $algorithm
		 * @return string.
		 * @todo Convert to PHPass
		 */
		static public function password($password='',$salt='random_salt',$algorithm='ripemd160') {
			if(!is_string($password) || !$password) return $password;
			else $password = password.$salt;

			if(in_array($algorithm,hash_algos())) return hash($algorithm,$password,false);
			else return '';
		}

		/**
		 * Pluralize a string.
		 *
		 * @param string $string
		 * @param int $quantity
		 * @return string
		 */
		static public function pluralize($string='',$quantity=0) {
			if(!$string) return '';
			else $string = (string)$string;

			$quantity = (int)$quantity;

			# Array of words that are the same for singular and plural.
			$no_change = array(
				'bison',
				'deer',
				'elk',
				'fish','flounder',
				'grouse',
				'herring',
				'moose',
				'offspring',
				'quail',
				'reindeer',
				'salmon','series','sheep','shrimp','species','swine',
				'trout'
			);

			# Array of special cases.
			$special_cases = array(
				'child' => 'children',
				'foot' => 'feet',
				'goose' => 'geese',
				'louse' => 'lice',
				'man' => 'men',
				'mouse' => 'mice',
				'ox' => 'oxen',
				'person' => 'people',
				'runner-up' => 'runners-up',
				'tooth' => 'teeth',
				'woman' => 'women'
			);

			# Singular quantity or if no change. Will return text as-is.
			if($quantity === 1 || in_array(strtolower($string),$no_change)):
				return $string;

			# For special cases.
			elseif(array_key_exists(strtolower($string),$special_cases)):
				return $special_cases[strtolower($string)];

			# Plural quantity.
			else:
				# Should "y" be replaced with "ies"?
				if(substr($string,-1) === 'y' && !in_array(substr($string,-2,1),array('a','e','i','o','u'))):
					return substr($string,0,strlen($string) - 1).'ies';

				# Text does not end in "y".
				else:

					# Should an "es" be added instead of just "s"?
					# Adds an "s" or "es".
					return $string.((in_array(substr($string,-1),array('s','x')) || in_array(substr($string,-2),array('sh','ch')))?'es':'s');
				endif;
			endif;

			# Returns the pluralized text.
			return $string;
		}

		/**
		 * Get the possessive version of a string.
		 *
		 * @param string $string
		 * @return string
		 */
		static public function possessive($string='') {
			if(!$string) return '';
			else $string = (string)$string;

			return $string."'".((substr($string,-1) !== 's')?'s':NULL);
		}

		/**
		 * Generate a random string based on $keys with a length of $count.
		 *
		 * @param int $count
		 * @param string $keys
		 * @return string
		 */
		static public function random($count=8,$keys='abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTWXYZ') {
			mt_srand((double)microtime() * 1000000);
			$random = array();
			$length = strlen($keys) - 1;
			for($i = 0; $i < $count; ++$i) $random[] = $keys[mt_rand(0,$length)];
			return join('',$random);
		}

		/**
		 * Shorten a string.
		 *
		 * @param string $string
		 * @param int $length
		 * @param bool $truncate
		 * @return string
		 */
		static public function shorten($string='',$length=32,$truncate=false) {
			if(!$string) return '';
			else $string = strip_tags((string)$string);
			if(is_bool($length)):
				$truncate = $length;
				$length = 32;
			else:
				$length = (int)$length;
			endif;

			# Convert HTML entities into UTF-8 characters.
			$string = html_entity_decode($string,ENT_NOQUOTES,'UTF-8');

			if(strlen($string) > $length):
				$length - 4;
				$string = substr($string,0,$length);
				if(!$truncate):
					$string = explode(' ',$string);
					$size = count($string) - 1;
					if($size > -1):
						$final = '';
						for($i = 0; $i < $size; ++$i) {
							$final .= $string[$i].' ';
						}
						$string = $final;
					endif;
				endif;
				$string = trim($string).'...';
			endif;

			# Convert String back into HTMLENTITIES.
			$string = htmlentities($string,ENT_NOQUOTES,'UTF-8');

			return $string;
		}

		/**
		 * Return a number with its suffix.
		 * 
		 * @param int $number
		 * @return string
		 */
		static public function suffix($number=0) {
			if(!$number) return;
			$number = '0'.$number;
			if(substr($number,-2,1) == '1') return 'th';
			elseif(substr($number,-1) == '1') return 'st';
			elseif(substr($number,-1) == '2') return 'nd';
			elseif(substr($number,-1) == '3') return 'rd';
			return 'th';
		}

		/**
		 * Make a string url friendly.
		 * 
		 * @param string $string
		 * @return string 
		 */
		static public function urlize($string='') {
			if(!$string) return '';
			else $string = (string)$string;

			return trim(preg_replace('/[-]+/','-',str_replace(' ','-',preg_replace('/[^a-z0-9- ]/i','',html_entity_decode(strtolower(trim(self::shorten($string,64))),ENT_QUOTES)))),'-');
		}

	}
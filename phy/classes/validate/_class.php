<?php

	/**
	 * Validate various issues.
	 *
	 * @category Validate
	 * @package Validate
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	final class Validate {

		/**
		 * Check for a valid email.
		 *
		 * @param string $email
		 * @param bool $domain Optional, if true it will attempt to check MX
		 * records as well.
		 * @return bool
		 */
		static public function email($email='',$domain=false) {
			# Don't waste our time on empty strings.
			if(!$email || !is_string($email)) return false;

			# Perform Regular Expressions first for non-printable codes, then for valid emails.
			# if(preg_match('#[\\000-\\037]#',$url)||!preg_match("#^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$#iD",$url)) return false;
			if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return false;

			# If we want to check for a domains existence then we can do that now.
			if($domain):
				list($user,$domain) = explode('@',$email);
				if(function_exists('checkdnsrr')):
					if(!checkdnsrr($domain,'MX')):
						return false;
					endif;
				elseif(function_exists('getmxrr')):
					if(!getmxrr($domain,$mxhosts)):
						return false;
					endif;
				endif;
			endif;

			# If nothing was wrong above then we can return true.
			return true;
		}

		/**
		 * Sees if an IP is valid.
		 *
		 * @param string $ip
		 * @return bool
		 */
		static public function ip($ip='') {
			return $ip?filter_var($ip,FILTER_VALIDATE_IP):false;
		}

		/**
		 * Checks to see if a url is valid. If $data it will also cURL the url.
		 *
		 * @param string $url
		 * @param bool $data Optional. If true method will return response data
		 * instead of a boolean.
		 * @return bool|array
		 */
		static public function url($url='',$data=false) {
			# Don't waste our time on empty strings.
			if(!$url || !is_string($url)) return false;

			# prepend http if one was not provided.
			if(substr($url,0,7) !== 'http://' && substr($url,0,8) !== 'https://') $url = 'http://'.$url;

			# If we just need to know if it's valid.
			if(!$data):
				return filter_var($url,FILTER_VALIDATE_URL);

			# Otherwise we'll grab what we can.
			else:
				$CURL = curl_init($url);
				curl_setopt($CURL,CURLOPT_USERAGENT,'Opera/9.80 (Windows NT 6.1; U; en) Presto/2.6.30 Version/10.60');
				curl_setopt($CURL,CURLOPT_AUTOREFERER,true);
				curl_setopt($CURL,CURLOPT_FOLLOWLOCATION,true);
				curl_setopt($CURL,CURLOPT_TIMEOUT,4);
				curl_setopt($CURL,CURLOPT_HEADER,true);
				curl_setopt($CURL,CURLOPT_RETURNTRANSFER,true);
				$response = curl_exec($CURL);
				$headers = curl_getinfo($CURL);
				curl_close($CURL);

				if(substr($headers['http_code'],0,1) !== '2') return array(
						'status' => $headers['http_code'],
						'response' => 'Link could not be found. Please check to make sure the link is working properly and then try again.'
					);

				preg_match_all('#<meta(.*?)>#si',$response,$tags);
				$meta = array();
				$x = 0;
				foreach($tags[1] as $pairs):
					if(preg_match_all('#([a-z]+)="(.*?)"#si',$pairs,$pair)):
						if(!isset($pair[1],$pair[2])):
							continue;
						endif;
					elseif(preg_match_all('#([a-z]+)=\'(.*?)\'#si',$pairs,$pair)):
						if(!isset($pair[1],$pair[2])):
							continue;
						endif;
					else:
						continue;
					endif;
					$pair = array_combine($pair[1],$pair[2]);
					if(isset($pair['name'],$pair['content'])) $meta[$pair['name']] = $pair['content'];
				endforeach;

				if(!isset($meta['title'])):
					if(preg_match('#<title>(.*?)<\/title>#si',$response,$title)) $title = $title[1];
					else $title = $url;
				else:
					$title = $meta['title'];
				endif;

				return array(
					'status' => 200,
					'response' => array(
						'url' => $url,
						'title' => $title,
						'description' => isset($meta['description'])?String::shorten($meta['description'],128):'',
						'keywords' => isset($meta['keywords'])?$meta['keywords']:''
					)
				);
			endif;
		}

	}
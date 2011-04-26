<?php
	class Geoplugin {
		private $host = 'http://www.geoplugin.net/php.gp?ip={IP}&base_currency={CURRENCY}',
			$currency = 'USD';
		
		public $ip = NULL,
			$city = NULL,
			$region = NULL,
			$areaCode = NULL,
			$dmaCode = NULL,
			$countryCode = NULL,
			$countryName = NULL,
			$continentCode = NULL,
			$latitute = NULL,
			$longitude = NULL,
			$currencyCode = NULL,
			$currencySymbol = NULL,
			$currencyConverter = NULL;
		
		public function __construct($ip=NULL) {
			if($ip===NULL) $ip = $_SERVER['REMOTE_ADDR'];
			$host = str_replace('{IP}',$ip,$this->host);
			$host = str_replace('{CURRENCY}',$this->currency,$host);
			$row = unserialize($this->__fetch($host));
			$this->ip = $ip;
			$this->city = $row['geoplugin_city'];
			$this->region = $row['geoplugin_region'];
			$this->areaCode = $row['geoplugin_areaCode'];
			$this->dmaCode = $row['geoplugin_dmaCode'];
			$this->countryCode = $row['geoplugin_countryCode'];
			$this->countryName = $row['geoplugin_countryName'];
			$this->continentCode = $row['geoplugin_continentCode'];
			$this->latitude = $row['geoplugin_latitude'];
			$this->longitude = $row['geoplugin_longitude'];
			$this->currencyCode = $row['geoplugin_currencyCode'];
			$this->currencySymbol = $row['geoplugin_currencySymbol'];
			$this->currencyConverter = $row['geoplugin_currencyConverter'];
		}
		
		public function convert($amount,$float=2,$symbol=true) {
			if(!is_numeric($this->currencyConverter)||$this->currencyConverter==0):
				trigger_error('Geoplugin class Notice: currencyConverter has no value.',E_USER_NOTICE);
				return $amount;
			elseif(!is_numeric($amount)):
				trigger_error ('Geoplugin class Warning: The amount passed to Geoplugin::convert is not numeric.',E_USER_WARNING);
				return $amount;
			elseif($symbol===true):
				return $this->currencySymbol.round(($amount*$this->currencyConverter),$float);
			else:
				return round(($amount*$this->currencyConverter),$float);
			endif;
		}
		
		public function nearby($radius=10,$limit=NULL) {
			if(!is_numeric($this->latitude)||!is_numeric($this->longitude)):
				trigger_error ('Geoplugin class Warning: Incorrect latitude or longitude values.',E_USER_NOTICE);
				return array(array());
			endif;
			$host = 'http://www.geoplugin.net/extras/nearby.gp?lat='.$this->latitude.'&long='.$this->longitude.'&radius='.$radius;
			if(is_numeric($limit)) $host .= '&limit='.$limit;
			return unserialize($this->__fetch($host));
		}
		
		private function __fetch($host=NULL) {
			if(function_exists('curl_init')):
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $host);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Geoplugin PHP Class v1.0');
				$response = curl_exec($ch);
				curl_close ($ch);
			elseif(ini_get('allow_url_fopen')):
				$response = file_get_contents($host, 'r');
			else:
				trigger_error ('Geoplugin class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
				return;
			endif;
			return $response;
		}
	}
?>
<?php

	namespace PHY;

	/**
	 * Retrieve array based constants since constants cannot be arrays.
	 *
	 * @category Contant
	 * @package Constant
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	final class Constant {

		/**
		 * Class cannot be constructed.
		 */
		private function __construct() {

		}

		/**
		 * Class cannot be cloned.
		 */
		private function __clone() {

		}

		/**
		 * Trigger an error on undefined calls.
		 */
		public function __call($method,$arguments) {
			\PHY\Debug::error('Functions::'.$method.'() does not exist.',E_USER_WARNING);
		}

		/**
		 * Read config values.
		 *
		 * @staticvar array $configs
		 * @param string $key Path for the desired value.
		 * @return mixed
		 */
		static public function config($key=NULL) {
			static $configs = array();

			$values = explode('/',$key);
			$config = array_shift($values);

			# Grab the default values.
			if(!isset($configs[$config])):
				if(is_file(BASE_PATH.'phy/config/'.$config.'.ini')):
					$configs[$config] = parse_ini_file(BASE_PATH.'phy/config/'.$config.'.ini');
				else:
					\PHY\Debug::warning('Config "'.$config.'" was not found.',E_USER_WARNING);
					return;
				endif;
			endif;

			if($values):
				$temp = $configs[$config];
				foreach($values as $value):
					if(!isset($temp[$value]))
						return;
					elseif($temp)
						$temp = $temp[$value];
				endforeach;
				return $temp;
			else:
				return $configs[$config];
			endif;
		}

		/**
		 * Find a country name by its 2 character ISO.
		 *
		 * @param string $country
		 * @return string
		 */
		static public function COUNTRY($country=NULL) {
			$countries = self::COUNTRIES();
			return ((isset($countries[strtoupper($country)]))?$countries[strtoupper($country)]:false);
		}

		/**
		 * List of countries
		 * @return array $ISO => $name
		 */
		static public function COUNTRIES() {
			return array(
				'AF' => 'Afghanistan',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AS' => 'American Samoa',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'AP' => 'Azores',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BA' => 'Bosnia And Herzegowina',
				'XB' => 'Bosnia-Herzegovina',
				'BW' => 'Botswana',
				'BV' => 'Bouvet Island',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'VG' => 'British Virgin Islands',
				'BN' => 'Brunei Darussalam',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CG' => 'Congo',
				'CD' => 'Congo, The Democratic Republic Of',
				'CK' => 'Cook Islands',
				'XE' => 'Corsica',
				'CR' => 'Costa Rica',
				'CI' => 'Cote d` Ivoire (Ivory Coast)',
				'HR' => 'Croatia',
				'CU' => 'Cuba',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'TP' => 'East Timor',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands (Malvinas)',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France (Includes Monaco)',
				'FX' => 'France, Metropolitan',
				'GF' => 'French Guiana',
				'PF' => 'French Polynesia',
				'TA' => 'French Polynesia (Tahiti)',
				'TF' => 'French Southern Territories',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GU' => 'Guam',
				'GT' => 'Guatemala',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HM' => 'Heard And Mc Donald Islands',
				'VA' => 'Holy See (Vatican City State)',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran',
				'IQ' => 'Iraq',
				'IE' => 'Ireland',
				'EI' => 'Ireland (Eire)',
				'IL' => 'Israel',
				'IT' => 'Italy',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KP' => 'Korea, Democratic People\'s Republic',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Laos',
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macao',
				'MK' => 'Macedonia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia, Federated States Of',
				'MD' => 'Moldova, Republic Of',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'ME' => 'Montenegro',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar (Burma)',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PW' => 'Palau',
				'PS' => 'Palestinian Territory, Occupied',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'PR' => 'Puerto Rico',
				'QA' => 'Qatar',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'RW' => 'Rwanda',
				'KN' => 'Saint Kitts And Nevis',
				'SM' => 'San Marino',
				'ST' => 'Sao Tome and Principe',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'RS' => 'Serbia',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovak Republic',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'GS' => 'South Georgia And The South Sand',
				'KR' => 'South Korea',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'NV' => 'St. Christopher and Nevis',
				'SH' => 'St. Helena',
				'LC' => 'St. Lucia',
				'PM' => 'St. Pierre and Miquelon',
				'VC' => 'St. Vincent and the Grenadines',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard And Jan Mayen Islands',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syrian Arab Republic',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania',
				'TH' => 'Thailand',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad and Tobago',
				'XU' => 'Tristan da Cunha',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks and Caicos Islands',
				'TV' => 'Tuvalu',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'UK' => 'United Kingdom',
				'GB' => 'Great Britain',
				'US' => 'United States',
				'UM' => 'United States Minor Outlying Isl',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'XV' => 'Vatican City',
				'VE' => 'Venezuela',
				'VN' => 'Vietnam',
				'VI' => 'Virgin Islands (U.S.)',
				'WF' => 'Wallis and Furuna Islands',
				'EH' => 'Western Sahara',
				'WS' => 'Western Samoa',
				'YE' => 'Yemen',
				'YU' => 'Yugoslavia',
				'ZR' => 'Zaire',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe'
			);
		}

		/**
		 * Return the state name for a given 2 character state and country ISO
		 * @param string $state
		 * @param string $country
		 * @return string
		 */
		static public function STATE($state=NULL,$country=NULL) {
			$states = self::STATES($country);
			return ((isset($states[strtoupper($state)]))?$states[strtoupper($state)]:false);
		}

		/**
		 * Return a list of states for a country if they are set.
		 *
		 * @staticvar array $states
		 * @param string $country
		 * @return array
		 */
		static public function STATES($country=NULL) {
			static $states = array(
			'US' => array(
				'AL' => 'Alabama',
				'AK' => 'Alaska',
				'AR' => 'Arkansas',
				'AS' => 'American Samoa',
				'AZ' => 'Arizona',
				'CA' => 'California',
				'CO' => 'Colorado',
				'CT' => 'Connecticut',
				'DC' => 'Washington D.C.',
				'DE' => 'Delaware',
				'FL' => 'Florida',
				'FM' => 'Micronesia',
				'GA' => 'Georgia',
				'GU' => 'Guam',
				'HI' => 'Hawaii',
				'IA' => 'Iowa',
				'ID' => 'Idaho',
				'IL' => 'Illinois',
				'IN' => 'Indiana',
				'KS' => 'Kansas',
				'KY' => 'Kentucky',
				'LA' => 'Louisiana',
				'MA' => 'Massachusetts',
				'MD' => 'Maryland',
				'ME' => 'Maine',
				'MH' => 'Marshall Islands',
				'MI' => 'Michigan',
				'MN' => 'Minnesota',
				'MO' => 'Missouri',
				'MP' => 'Marianas',
				'MS' => 'Mississippi',
				'MT' => 'Montana',
				'NC' => 'North Carolina',
				'ND' => 'North Dakota',
				'NE' => 'Nebraska',
				'NH' => 'New Hampshire',
				'NJ' => 'New Jersey',
				'NM' => 'New Mexico',
				'NV' => 'Nevada',
				'NY' => 'New York',
				'OH' => 'Ohio',
				'OK' => 'Oklahoma',
				'OR' => 'Oregon',
				'PA' => 'Pennsylvania',
				'PR' => 'Puerto Rico',
				'PW' => 'Palau',
				'RI' => 'Rhode Island',
				'SC' => 'South Carolina',
				'SD' => 'South Dakota',
				'TN' => 'Tennessee',
				'TX' => 'Texas',
				'UT' => 'Utah',
				'VA' => 'Virginia',
				'VI' => 'Virgin Islands',
				'VT' => 'Vermont',
				'WA' => 'Washington',
				'WI' => 'Wisconsin',
				'WV' => 'West Virginia',
				'WY' => 'Wyoming',
				'AA' => 'Military Americas',
				'AE' => 'Military Europe/ME/Canada',
				'AP' => 'Military Pacific'
			),
			'CA' => array(
				'AB' => 'Alberta',
				'MB' => 'Manitoba',
				'AB' => 'Alberta',
				'BC' => 'British Columbia',
				'MB' => 'Manitoba',
				'NB' => 'New Brunswick',
				'NL' => 'Newfoundland and Labrador',
				'NS' => 'Nova Scotia',
				'NT' => 'Northwest Territories',
				'NU' => 'Nunavut',
				'ON' => 'Ontario',
				'PE' => 'Prince Edward Island',
				'QC' => 'Quebec',
				'SK' => 'Saskatchewan',
				'YT' => 'Yukon Territory'
			),
			'AU' => array(
				'AAT' => 'Australian Antarctic Territory',
				'ACT' => 'Australian Capital Territory',
				'NT' => 'Northern Territory',
				'NSW' => 'New South Wales',
				'QLD' => 'Queensland',
				'SA' => 'South Australia',
				'TAS' => 'Tasmania',
				'VIC' => 'Victoria',
				'WA' => 'Western Australia'
			),
			'BR' => array(
				'AC' => 'Acre',
				'AL' => 'Alagoas',
				'AM' => 'Amazonas',
				'AP' => 'Amapa',
				'BA' => 'Baia',
				'CE' => 'Ceara',
				'DF' => 'Distrito Federal',
				'ES' => 'Espirito Santo',
				'FN' => 'Fernando de Noronha',
				'GO' => 'Goias',
				'MA' => 'Maranhao',
				'MG' => 'Minas Gerais',
				'MS' => 'Mato Grosso do Sul',
				'MT' => 'Mato Grosso',
				'PA' => 'Para',
				'PB' => 'Paraiba',
				'PE' => 'Pernambuco',
				'PI' => 'Piaui',
				'PR' => 'Parana',
				'RJ' => 'Rio de Janeiro',
				'RN' => 'Rio Grande do Norte',
				'RO' => 'Rondonia',
				'RR' => 'Roraima',
				'RS' => 'Rio Grande do Sul',
				'SC' => 'Santa Catarina',
				'SE' => 'Sergipe',
				'SP' => 'Sao Paulo',
				'TO' => 'Tocatins'
			),
			'NL' => array(
				'DR' => 'Drente',
				'FL' => 'Flevoland',
				'FR' => 'Friesland',
				'GL' => 'Gelderland',
				'GR' => 'Groningen',
				'LB' => 'Limburg',
				'NB' => 'Noord Brabant',
				'NH' => 'Noord Holland',
				'OV' => 'Overijssel',
				'UT' => 'Utrecht',
				'ZH' => 'Zuid Holland',
				'ZL' => 'Zeeland'
			),
			'UK' => array(
				'AVON' => 'Avon',
				'BEDS' => 'Bedfordshire',
				'BERKS' => 'Berkshire',
				'BUCKS' => 'Buckinghamshire',
				'CAMBS' => 'Cambridgeshire',
				'CHESH' => 'Cheshire',
				'CLEVE' => 'Cleveland',
				'CORN' => 'Cornwall',
				'CUMB' => 'Cumbria',
				'DERBY' => 'Derbyshire',
				'DEVON' => 'Devon',
				'DORSET' => 'Dorset',
				'DURHAM' => 'Durham',
				'ESSEX' => 'Essex',
				'GLOUS' => 'Gloucestershire',
				'GLONDON' => 'Greater London',
				'GMANCH' => 'Greater Manchester',
				'HANTS' => 'Hampshire',
				'HERWOR' => 'Hereford & Worcestershire',
				'HERTS' => 'Hertfordshire',
				'HUMBER' => 'Humberside',
				'IOM' => 'Isle of Man',
				'IOW' => 'Isle of Wight',
				'KENT' => 'Kent',
				'LANCS' => 'Lancashire',
				'LEICS' => 'Leicestershire',
				'LINCS' => 'Lincolnshire',
				'MERSEY' => 'Merseyside',
				'NORF' => 'Norfolk',
				'NHANTS' => 'Northamptonshire',
				'NTHUMB' => 'Northumberland',
				'NOTTS' => 'Nottinghamshire',
				'OXON' => 'Oxfordshire',
				'SHROPS' => 'Shropshire',
				'SOM' => 'Somerset',
				'STAFFS' => 'Staffordshire',
				'SUFF' => 'Suffolk',
				'SURREY' => 'Surrey',
				'SUSS' => 'Sussex',
				'WARKS' => 'Warwickshire',
				'WMID' => 'West Midlands',
				'WILTS' => 'Wiltshire',
				'YORK' => 'Yorkshire'
			),
			'IN' => array(
				'AN' => 'Andaman and Nicobar Islands',
				'AP' => 'Andhra Pradesh',
				'AR' => 'Arunachal Pradesh',
				'AS' => 'Assam',
				'BR' => 'Bihar',
				'CH' => 'Chandigarh',
				'CT' => 'Chhattisgarh',
				'DN' => 'Dadra and Nagar Haveli',
				'DD' => 'Daman and Diu',
				'DL' => 'Delhi',
				'GA' => 'Goa',
				'GJ' => 'Gujarat',
				'HR' => 'Haryana',
				'HP' => 'Himachal Pradesh',
				'JK' => 'Jammu and Kashmir',
				'JH' => 'Jharkhand',
				'KA' => 'Karnataka',
				'KL' => 'Kerala',
				'LD' => 'Lakshadweep',
				'MP' => 'Madhya Pradesh',
				'MH' => 'Maharashtra',
				'MN' => 'Manipur',
				'ML' => 'Meghalaya',
				'MZ' => 'Mizoram',
				'NL' => 'Nagaland',
				'OR' => 'Orissa',
				'PY' => 'Puducherry',
				'PB' => 'Punjab',
				'RJ' => 'Rajasthan',
				'SK' => 'Sikkim',
				'TN' => 'Tamil Nadu',
				'TR' => 'Tripura',
				'UL' => 'Uttarakhand',
				'UP' => 'Uttar Pradesh',
				'WB' => 'West Bengal'
			),
			'EI' => array(
				'CO ANTRIM' => 'County Antrim',
				'CO ARMAGH' => 'County Armagh',
				'CO CARLOW' => 'County Carlow',
				'CO CAVAN' => 'County Cavan',
				'CO CLARE' => 'County Clare',
				'CO CORK' => 'County Cork',
				'CO DERRY' => 'County Derry',
				'CO DONEGAL' => 'County Donegal',
				'CO DOWN' => 'County Down',
				'CO DUBLIN' => 'County Dublin',
				'CO FERMANAGH' => 'County Fermanagh',
				'CO GALWAY' => 'County Galway',
				'CO KERRY' => 'County Kerry',
				'CO KILDARE' => 'County Kildare',
				'CO KILKENNY' => 'County Kilkenny',
				'CO LAOIS' => 'County Laois',
				'CO LEITRIM' => 'County Leitrim',
				'CO LIMERICK' => 'County Limerick',
				'CO LONGFORD' => 'County Longford',
				'CO LOUTH' => 'County Louth',
				'CO MAYO' => 'County Mayo',
				'CO MEATH' => 'County Meath',
				'CO MONAGHAN' => 'County Monaghan',
				'CO OFFALY' => 'County Offaly',
				'CO ROSCOMMON' => 'County Roscommon',
				'CO SLIGO' => 'County Sligo',
				'CO TIPPERARY' => 'County Tipperary',
				'CO TYRONE' => 'County Tyrone',
				'CO WATERFORD' => 'County Waterford',
				'CO WESTMEATH' => 'County Westmeath',
				'CO WEXFORD' => 'County Wexford',
				'CO WICKLOW' => 'County Wicklow'
			)
			);
			return ((isset($states[strtoupper($country)]))?$states[strtoupper($country)]:array());
		}

		/**
		 * Return a status message based on its code.
		 *
		 * @param int $status_code
		 * @return string
		 */
		static public function STATUS_CODE($status_code=NULL) {
			static $array = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Authorization Required',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'Mullanaphy!',
			420 => 'unused',
			421 => 'unused',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'No code',
			426 => 'Upgrade Required',
			500 => 'Internal Server Error',
			501 => 'Method Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Temporarily Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			508 => 'unused',
			509 => 'unused',
			510 => 'Not Extended'
			);
			if(isset($array[$status_code]))
				return $array[$status_code];
			elseif($status_code === NULL)
				return $array;
		}

	}
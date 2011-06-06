<?php

	namespace PHY\API;

	/**
	 * Useing Sphinx for auto complete search boxes.
	 * 
	 * @category API
	 * @package API\Sphinx
	 * @author John Mullanaphy
	 */
	class Search extends \PHY\API\_Abstract {
		const AUTHOR = 'John Mullanaphy';
		const CREATED = '2010-09-30';
		const VERSION = '0.1.0';

		const LIMIT = 3;

		protected $url = '/rest.php?controller=search';
		private $results = array();

#####	# Actions.

		protected function api_get() {
			if(!$this->results):
				if(!isset($this->parameters['q'])) return array(
						'status' => 400,
						'url' => $this->url,
						'response' => 'Query is missing.'
					);

				self::insert($this->parameters['q'],'header');
				$tag = new \PHY\Markup;
				$Search = new \PHY\Sphinx;
				$Search->setLimits(0,self::LIMIT);
				$this->results = $Search->query($this->parameters['q']);
			endif;

			if(!$this->results) $this->results[] = 'No results found.';

			return array(
				'status' => 200,
				'url' => $this->url.'&q='.$this->parameters['q'],
				'response' => array('content' => $this->results)
			);
		}

		static public function insert($term='',$location='search') {
			if($term):
			/* Log it */
			endif;
		}

	}
<?php

	class API_Search extends API_Abstract {
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
				$tag = new Markup_HTML5;
				$Search = new Sphinx;
				$Search->setLimits(0,self::LIMIT);
				$results = $Search->query($this->parameters['q'],'users users_delta');
				if($results['total_found']):
					$User = new User;
					foreach($results['matches'] as $user_id => $weights):
						$User($user_id);
						$this->results[] = $tag->url(
								array(
									$tag->image(
										$User->icon(40),
										$User->fullname
									),
									$tag->span($User->fullname),
									$tag->small($User->bulletin)
								),
								$User->url,
								array('title' => $User->fullname)
						);
					endforeach;
					unset($User);
				endif;

				$results = $Search->query($this->parameters['q'],'media media_delta');
				if($results['total_found']):
					$Media = new Media;
					foreach($results['matches'] as $media_id => $weights):
						$Media($media_id);
						$this->results[] = $tag->url(
								array(
									$tag->image(
										$Media->icon(40),
										$Media->title
									),
									$tag->span($Media->title),
									$tag->small(str_replace(array("\r","\n"),'',$Media->description))
								),
								$Media->url,
								array(
									'class' => 'search',
									'title' => $Media->title
								)
						);
					endforeach;
					unset($Media);
				endif;
			endif;

			if(!$this->results) $this->results[] = $tag->p(Language::translate('No results found.'));

			return array(
				'status' => 200,
				'url' => $this->url.'&q='.$this->parameters['q'],
				'response' => array('content' => join('',$this->results))
			);
		}

		static public function insert($term='',$location='search') {
			if($term):
				/* Log it */
			endif;
		}

	}
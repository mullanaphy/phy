<?php

	namespace PHY\Container;

	class Form extends \PHY\Container\_Abstract {
		const RECAPTCHA_KEY = '';

		protected $hidden = array(),
		$content = 'div',
		$holder = 'form',
		$item = 'fieldset',
		$settings = array(
			'cancel' => false,
			'submit' => NULL
			),
		$type = 'form',
		$captcha = false;

		public function __construct($attributes=false) {
			$this->hidden['xsrf_id'] = isset($_COOKIE['xsrf_id']) && $_COOKIE['xsrf_id']?$_COOKIE['xsrf_id']:NULL;
			$this->hidden['_form'] = 1;
			$this->container['attributes']['method'] = 'post';
			parent::__construct($attributes);
		}

		public function action($action=NULL) {
			if(is_array($action)):
				if(is_string(key($action))) $this->container['attributes']['action'] = '/rest.php';
				else $this->container['attributes']['action'] = array_shift($action);
				$this->hidden = array_merge($action,$this->hidden);
			else:
				$this->container['attributes']['action'] = $action;
			endif;
			return $this;
		}

		public function ajax($data=NULL) {
			if($data !== false):
				if(!is_array($this->container['attributes']) || !isset($this->container['attributes']['class'])):
					$this->container['attributes']['class'] = 'ajax';
				else:
					$this->container['attributes']['class'] = explode(' ',$this->container['attributes']['class']);
					array_unshift($this->container['attributes']['class'],'ajax');
					$this->container['attributes']['class'] = join(' ',$this->container['attributes']['class']);
				endif;
				if(is_array($data)):
					foreach($data as $key => $value) $this->container['attributes']['data-'.strtolower($key)] = $value;
				endif;
			elseif(!is_array($this->container['attributes']) || !isset($this->container['attributes']['class'])):
				return;
			else:
				$this->container['attributes']['class'] = explode(' ',$this->container['attributes']['class']);
				if($key = array_search('ajax',$this->container['attributes']['class'])) unset($this->container['attributes']['class'][$key]);
				$this->container['attributes']['class'] = join(' ',$this->container['attributes']['class']);
			endif;
			if(!isset($this->container['attributes']['action']) || !$this->container['attributes']['action']) $this->container['attributes']['action'] = '/rest.php';
			return $this;
		}

		public function cancel($label=false,$action=false,$attributes=false) {
			if(!$label):
				$this->settings['cancel'] = false;
			else:
				if(is_array($action)):
					$attributes = $action;
					$action = false;
				endif;
				if(!is_array($attributes)) $attributes = array();
				$this->settings['cancel'] = array(
					'label' => $label,
					'action' => $action,
					'attributes' => $attributes
				);
			endif;
			return $this;
		}

		public function captcha() {
			$this->heading('Please solve the following CAPTCHA.');
			$id = \PHY\String::random(8);
			if(is_numeric($id[0])) $id = '_'.$id;
			$this->append(
				$this->tag->fieldset(
					$this->tag->table(
						$this->tag->tr(
							array(
								$this->tag->td(
									$this->tag->table(
										array(
										$this->tag->tr(
											$this->tag->td(
												'&nbsp;',array(
												'colspan' => 2,
												'id' => 'recaptcha_image',
												'style' => 'padding:0 0 5px 0;'
												)
											)
										),
										$this->tag->tr(
											array(
												$this->tag->th(
													$this->tag->label(
														$this->tag->label('Solution:'),array('for' => 'recaptcha_response_field')
													),array('style' => 'width:25%')
												),
												$this->tag->td(
													$this->tag->input(
														array(
															'id' => 'recaptcha_response_field',
															'name' => 'recaptcha_response_field',
															'type' => 'text'
														)
													)
												)
											)
										),
										$this->tag->tr(
											$this->tag->td(
												array(
												$this->tag->url(
													'Reload CAPTCHA','javascript:Recaptcha.reload();'
												),
												'&nbsp;|&nbsp;',
												$this->tag->span(
													$this->tag->url(
														'Get Audio','javascript:Recaptcha.switch_type(\'audio\');'
													),array('class' => 'recaptcha_only_if_image')
												),
												$this->tag->span(
													$this->tag->url(
														'Image CAPTCHA','Recaptcha.switch_type(\'image\');'
													),array('class' => 'recaptcha_only_if_audio')
												),
												'&nbsp;|&nbsp;',
												$this->tag->url(
													'About reCAPTCHA','http://www.recaptcha.net/',array(
													'rel' => 'nofollow',
													'target' => '_blank'
													)
												)
												),array(
												'colspan' => 2,
												'style' => 'padding-top:4px;text-align:center;'
												)
											)
										)
										),array('class' => 'form')
									),array('style' => 'padding:0;width:302px;')
								),
								$this->tag->td('CAPTCHAs help reduce spam by making sure the user is human (and not another computer). To solve a CAPTCHA, enter the two words from the image, separated by a space. {SITE_NAME} uses reCAPTCHA to stop spam and to help digitize old books and newspapers.',array('style' => 'padding:0 0 0 5px;'))
							)
						),array('id' => 'recaptcha_widget','style' => 'margin:0')
					),array('class' => 'recaptcha','id' => $id)
				)
			);
			$this->append(
				$this->tag->noscript(
					array(
						$this->tag->iframe(
							false,array(
							'height' => 300,
							'src' => 'http://api.recaptcha.net/noscript?k='.self::RECAPTCHA_KEY,
							'width' => 500
							)
						),
						$this->tag->br,
						$this->tag->textarea(
							false,array(
							'cols' => 40,
							'name' => 'recaptcha_challenge_field',
							'rows' => 3
							)
						),
						$this->tag->input(
							array(
								'name' => 'recaptcha_response_field',
								'type' => 'hidden',
								'value' => 'manual_challenge'
							)
						)
					)
				)
			);
			$this->append(
				$this->tag->script(
					false,array(
					'src' => '/scripts/global/recaptcha.0.1.js',
					'type' => 'text/javascript'
					)
				)
			);
			$this->append(
				$this->tag->script(
					'Recaptcha.create(\''.self::RECAPTCHA_KEY.'\',\''.$id.'\',{theme:\'custom\',custom_theme_widget:\'recaptcha_widget\'});',array('type' => 'text/javascript')
				)
			);
			return $this;
		}

		public function hidden($fields=NULL) {
			if(!is_array($fields)) return;
			foreach($fields as $key => $value) $this->hidden[$key] = $value;
			return $this;
		}

		public function method($method=NULL) {
			$this->container['attributes']['method'] = $method;
			return $this;
		}

		public function pagination(array $settings=array(),array $attributes=array()) {
			if(isset($settings['url'],$settings['total']) && $settings['total'] > 1):
				if(!is_array($attributes)) $attributes = array();
				if(isset($attributes['class'])) $attributes['class'] .= ' pagination';
				else $attributes['class'] = 'pagination';
				if(!isset($settings['limit'])) $settings['limit'] = 10;
				if(!isset($settings['page_id']) || $settings['page_id'] < 1 || $settings['page_id'] > $settings['total']) $settings['page_id'] = 1;
				$pages = $this->tag->ul;
				foreach(range($settings['page_id'] - 4,$settings['page_id'] + 4) as $i):
					if($i <= 0) continue;
					elseif($i > $settings['total']) break;
					$pages->append(
						$this->tag->li($i != $settings['page_id']?$this->tag->url($i,str_replace('[%i]',$i,$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL):$this->tag->strong($i))
					);
				endforeach;
				$pages->prepend(
					$this->tag->li($settings['page_id'] > 1?$this->tag->url('&laquo;',str_replace('[%i]',($settings['page_id'] - 1),$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL):$this->tag->span('&laquo;'),array('class' => 'button_dark'))
				);
				$pages->append(
					$this->tag->li($settings['page_id'] < $settings['total']?$this->tag->url('&raquo;',str_replace('[%i]',($settings['page_id'] + 1),$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL):$this->tag->span('&raquo;'),array('class' => 'button_dark'))
				);
				$this->container['footer'] = array(
					'content' => $pages,
					'attributes' => $attributes
				);
			endif;
			return $this;
		}

		public function submit($label=NULL,$attributes=NULL) {
			if($label === false):
				$this->settings['submit'] = false;
				return $this;
			endif;

			# Set the value.
			if(!is_array($attributes)) $attributes = array('value' => $label);
			else $attributes['value'] = $label;
			$attributes['value'] = $attributes['value']?htmlentities($attributes['value'],ENT_QUOTES,'utf-8',false):'Submit';

			# Set submit accordingly.
			$this->settings['submit'] = $attributes;

			# Return $this for chaining.
			return $this;
		}

		protected function _footer() {
			$submit = $this->tag->fieldset;

			# Load the default settings for submit and then rewrite whatever is needed.
			if($this->settings['submit'] !== false):
				if(!is_array($this->settings['submit'])) $this->settings['submit'] = array('value' => 'Submit');
				$this->settings['submit'] = array_merge(
					array(
					'class' => 'submit button',
					'name' => 'submit'
					),$this->settings['submit']
				);
				$this->settings['submit']['type'] = 'submit';

				$submit->append(
					$this->tag->input($this->settings['submit']),array('class' => 'submit')
				);
			endif;

			if($this->settings['cancel']) $submit->append(
					$this->tag->button(
						$this->settings['cancel']['label'],array_merge(
							array(
							'class' => 'button red close white',
#							'id' => $this->container['id'].'_cancel',
							'name' => 'cancel',
							'onclick' => $this->settings['cancel']['action'].'return false;'
							),$this->settings['cancel']['attributes']
						)
					)
				);

			if(isset($this->container['footer']['attributes']) && is_array($this->container['footer']['attributes'])) $submit->attributes($this->_attributes($this->container['footer']['attributes']));

			foreach($this->hidden as $key => $value) $submit->append(
					$this->tag->input(
						array(
							'type' => 'hidden',
							'name' => $key,
							'value' => $value
						)
					)
				);

			if($this->container['footer']['content']) $submit->append($this->container['footer']['content']);

			return $submit;
		}

	}
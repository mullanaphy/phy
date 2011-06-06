<?php

	namespace PHY\Container;

	/**
	 * Create a togglable container between different inner content.
	 *
	 * @category Container
	 * @package Container\Toggle
	 * @author John Mullanaphy
	 */
	class Toggle extends \PHY\Container\_Abstract {

		protected $content = 'div',
		$holder = 'article',
		$item = 'p',
		$tabulate = 'div';

		public function __construct($attributes=false) {
			$this->container['tabs'] = array(
				'selected' => false,
				'tabs' => array()
			);
			parent::__construct($attributes);
			$this->container['attributes']['id'] = 'tab_'.String::random(8);
		}

		public function action($action=NULL) {
			if(is_array($action)):
				$this->container['attributes']['action'] = array_shift($action);
				if(!isset($this->container['hidden'])) $this->container['hidden'] = array();
				$this->container['hidden'] = array_merge($action,$this->container['hidden']);
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
			return $this;
		}

		# Add content.

		public function append($content=false,$attributes=NULL) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				$this->container['content'][count($this->container['content']) - 1]['content'][] = $content;
			else:
				$tag = $this->item;
				$tag = $this->tag->$tag;
				$tag->append($content);
				if(is_array($attributes)) $tag->attributes($attributes);
				$this->container['content'][count($this->container['content']) - 1]['content'][] = $tag;
			endif;
			return $this;
		}

		public function cancel($label=false,$action=false) {
			if(!$label) $this->container['cancel'] = false;
			else $this->container['cancel'] = array(
					'label' => $label,
					'action' => $action
				);
			return $this;
		}

		# Sets the partial ID for the field to focus on when the pop-up loads.

		public function focus($tab=NULL) {
			if($tab === NULL) return;
			$this->container['tabs']['selected'] = $tab;
			return $this;
		}

		public function form($action=NULL) {
			if($action !== false):
				if(is_string($action) || is_array($action)) $this->action($action);
				$this->holder = 'form';
				$this->tabulate = 'div';
				$this->item = 'fieldset';
			else:
				$this->holder = 'article';
				$this->content = 'div';
				$this->tabulate = 'div';
				$this->item = 'p';
			endif;
			return $this;
		}

		# Heading.

		public function heading($content=false,$tag='tag',$attributes=NULL) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				$this->container['content'][count($this->container['content']) - 1]['content'][] = $content;
			else:
				if(is_array($tag)):
					$attributes = $tag;
					$tag = 'h3';
				elseif(!preg_match('#h[0-6]#i',$tag)):
					$tag = 'h3';
				endif;
				$tag = $this->tag->$tag;
				$tag->append($content);
				if(is_array($attributes)) $tag->attributes($attributes);
				$this->container['content'][count($this->container['content']) - 1]['content'][] = $tag;
			endif;
			return $this;
		}

		public function hidden($fields=NULL) {
			if(!is_array($fields)) return;
			if(!isset($this->container['hidden'])) $this->container['hidden'] = array();
			foreach($fields as $key => $value) $this->container['hidden'][$key] = $value;
			return $this;
		}

		public function method($method=NULL) {
			$this->container['attributes']['method'] = $method;
			return $this;
		}

		# Make this a list or not.

		public function ordered($list=true) {
			if($list):
				$this->content = 'div';
				$this->tabulate = 'ol';
				$this->item = 'li';
			else:
				$this->content = 'div';
				$this->tabulate = 'div';
				$this->item = 'p';
			endif;
			return $this;
		}

		public function pagination($settings=false,$attributes=false) {
			if(isset($settings['url'],$settings['total']) && $settings['total'] > 1):
				if(!is_array($attributes)) $attributes = array();
				if(isset($attributes['class'])):
					$attributes['class'] = explode(' ',$attributes['class']);
					$attributes['class'][] = 'pagination';
					$attributes['class'] = join(' ',$attributes['class']);
				else:
					$attributes['class'] = 'pagination';
				endif;
				if(!isset($settings['limit'])) $settings['limit'] = 10;
				if(!isset($settings['page_id']) || $settings['page_id'] < 1 || $settings['page_id'] > $settings['total']) $settings['page_id'] = 1;
				$pages = $this->tag->ul;
				foreach(range($settings['page_id'] - 4,$settings['page_id'] + 4) as $i):
					if($i <= 0) continue;
					elseif($i > $settings['total']) break;
					$pages->append(
						$this->tag->li(
							($i != $settings['page_id'])?$this->tag->url(
									$i,str_replace('[%i]',$i,$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL
								):$this->tag->strong($i)
						)
					);
				endforeach;
				$pages->prepend(
					$this->tag->li(
						($settings['page_id'] > 1)?$this->tag->url(
								'&laquo;',str_replace('[%i]',($settings['page_id'] - 1),$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL
							):$this->tag->span('&laquo;'),array('class' => 'button_dark')
					)
				);
				$pages->append(
					$this->tag->li(
						($settings['page_id'] < $settings['total'])?$this->tag->url(
								'&raquo;',str_replace('[%i]',($settings['page_id'] + 1),$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL
							):$this->tag->span('&raquo;'),array('class' => 'button_dark')
					)
				);
				$this->container['footer'] = array(
					'content' => $pages,
					'attributes' => $attributes
				);
			endif;
			return $this;
		}

		# Add content to the beginning.

		public function prepend($content=false) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				array_unshift($this->container['content'][count($this->container['content']) - 1]['content'],$content);
			else:
				$tag = $this->item;
				$tag = $this->tag->$tag;
				$tag->append($content);
				if(is_array($attributes)) $tag->attributes($attributes);
				array_unshift($this->container['content'][count($this->container['content']) - 1]['content'],$tag);
			endif;
			return $this;
		}

		public function submit($label=NULL) {
			$this->container['submit'] = $label;
		}

		# Make this a list or not.

		public function unordered($list=true) {
			if($list):
				$this->content = 'div';
				$this->tabulate = 'ul';
				$this->item = 'li';
			else:
				$this->content = 'div';
				$this->tabulate = 'div';
				$this->item = 'p';
			endif;
			return $this;
		}

		# Sets the current tab with the tab's ID. (Note: Tab must already have been created.)

		public function tab($name=NULL,$attributes=NULL,$focus=false) {
			if($name === NULL) return;
			if(is_bool($attributes)):
				$focus = $attributes;
				$attributes = NULL;
			endif;
			$this->container['tabs']['tabs'][] = $name;
			$this->container['content'][] = array(
				'attributes' => $attributes,
				'content' => array()
			);
			if($focus) $this->container['tabs']['selected'] = count($this->container['content']) - 1;
			return $this;
		}

#####	# Overwritten generators.

		protected function _content() {
			$content = $this->content;
			$tabulate = $this->tabulate;
			$content = $this->tag->$content;
			$content->attributes(array('class' => 'content'));
			if(count($this->container['content'])):
				foreach($this->container['content'] as $tab => $rows):
					$div = $this->tag->$tabulate;
					if(isset($rows['attributes'])) $attributes = $rows['attributes'];
					else $attributes = array();
					if(isset($attributes['class'])):
						$attributes['class'] = explode(' ',$attributes['class']);
						$attributes['class'][] = 'tab';
						$attributes['class'] = join(' ',$attributes['class']);
					else:
						$attributes['class'] = 'tab';
					endif;
					$attributes['id'] = $this->container['attributes']['id'].'_tab_'.$tab.'_content';
					if($tab != $this->container['tabs']['selected']) $attributes['style'] = 'display:none;';
					$div->attributes($attributes);
					foreach($rows['content'] as $row) $div->append($row);
					$content->append($div);
				endforeach;
			endif;
			return $content;
		}

		public function _header() {
			$header = $this->tag->header;
			if($this->container['title']) $header->append(
					$this->tag->h2(
						$this->container['title']['content'],$this->container['title']['attributes']
					)
				);

			$tabs = $this->tag->ul;
			$tabs->attributes(
				array(
					'class' => 'tabs',
					'id' => $this->container['attributes']['id'].'_tabs'
				)
			);

			foreach($this->container['tabs']['tabs'] as $tab => $value):
				$attributes = array(
					'class' => 'tab'.(($tab == $this->container['tabs']['selected'])?' selected':NULL),
					'id' => $this->container['attributes']['id'].'_tab_'.$tab,
					'href' => '#'
				);
				$tabs->append(
					$this->tag->li(
						$this->tag->a(
							$value,$attributes
						)
					)
				);
			endforeach;
			$header->append($tabs);
			return $header;
		}

		protected function _footer() {
			if($this->holder === 'form'):
				$submit = $this->tag->fieldset;

				if(isset($this->container['submit']) && $this->container['submit'] !== false) $submit->append(
						$this->tag->input(
							array(
								'class' => 'submit',
								#						'id' => $this->container['attributes']['id'].'_submit',
								'name' => 'submit',
								'type' => 'submit',
								'value' => (($this->container['submit'])?htmlentities($this->container['submit'],ENT_QUOTES,'utf-8',false):'Submit')
							)
						),array('class' => 'submit')
					);

				if(isset($this->container['cancel']) && $this->container['cancel']) $submit->append(
						$this->tag->button(
							$this->container['cancel']['label'],array(
							'class' => 'cancel',
							#							'id' => $this->container['attributes']['id'].'_cancel',
							'name' => 'cancel',
							'onclick' => $this->container['cancel']['action'].'return false;'
							)
						)
					);

				if(isset($this->container['footer']['attributes'])) $submit->attributes($this->_attributes($this->container['footer']['attributes']));

				if(isset($this->container['hidden'])) foreach($this->container['hidden'] as $key => $value) $submit->append(
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
			else:
				$footer = NULL;
				if($this->container['footer']):
					$attributes = $this->_attributes($this->container['footer']['attributes']);
					$footer = $this->tag->footer($this->container['footer']['content'],$attributes);
				endif;
				return $footer;
			endif;
		}

	}
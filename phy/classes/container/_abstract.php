<?php

	namespace PHY\Container;

	/**
	 * Container classes are for creating content blocks with titles, footers,
	 * etc.
	 *
	 * @category Container
	 * @package Container\_Abstract
	 * @author John Mullanaphy
	 */
	abstract class _Abstract {

		protected $attributes = array(),
		$container = array(
			'after' => array(),
			'attributes' => false,
			'before' => array(),
			'content' => array(),
			'footer' => false,
			'h1' => 'h2',
			'header' => false,
			'hide_if_empty' => false,
			'id' => false,
			'info' => false,
			'title' => false,
			'width' => false
			),
		$content = 'div',
		$holder = 'article',
		$item = 'p',
		$type = 'container';
		static protected $ITEMS = array('aside','dialog','div','dl','figure','fieldset','form','h1','h2','h3','h4','h5','h6','iframe','li','noscript','ol','p','script','table','ul');
		public $tag = NULL;

		/**
		 * Set an attribute depending on $function name to $parameters[0];
		 * 
		 * @param string $function
		 * @param array $parameters
		 * @return \PHY\Container\_Abstract
		 */
		public function __call($function,$parameters=NULL) {
			if($function === 'class' && $parameters !== NULL):
				if(isset($parameters[0]) && is_array($parameters[0])) $this->_class($parameters[0],isset($parameters[1])?!!$parameters[1]:true);
				else $this->_class($parameters);
			endif;
			return $this;
		}

		/**
		 * Create a new Container object.
		 * 
		 * @return \PHY\Container\_Abstract
		 */
		public function __construct($attributes=false) {
			$this->tag = \PHY\Markup::instance();
			if($attributes) $this->attributes = $attributes;
			return $this;
		}

		/**
		 * Reinitiate the Container.
		 * 
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function __invoke($attributes=false) {
			$this->container = array(
				'after' => array(),
				'attributes' => false,
				'before' => array(),
				'content' => array(),
				'footer' => false,
				'h1' => 'h2',
				'header' => false,
				'hide_if_empty' => false,
				'id' => false,
				'info' => false,
				'title' => false,
				'width' => false
			);
			return $this;
		}

		/**
		 * Render and return this container.
		 * 
		 * @return string
		 */
		public function __toString() {
			return (string)$this->render();
		}

		/**
		 * Get just the content of this container as a string.
		 * 
		 * @return string
		 */
		public function content() {
			return join('',$this->container['content']);
		}

		/**
		 * Check to see if this Container is empty.
		 * 
		 * Good for working with iterations.
		 * 
		 * return bool;
		 */
		public function is_empty() {
			return!count($this->container['content']);
		}

		/**
		 * Return a JSON string of this Container.
		 * 
		 * @return string
		 */
		public function json() {
			return json_encode($this->values());
		}

		/**
		 * Render the container and return its Markup object.
		 * 
		 * @return \PHY\Markup\_Abstract 
		 */
		public function render() {
			$holder = $this->holder;
			$holder = $this->tag->$holder;

			# Grab default attributes and defined attributes then add the container class.
			if(is_array($this->container['attributes'])) $attributes = array_merge($this->attributes,$this->container['attributes']);
			else $attributes = $this->attributes;

			if(isset($attributes['class'])):
				$attributes['class'] = explode(' ',$attributes['class']);
				array_unshift($attributes['class'],'container');
				$attributes['class'] = array_unique($attributes['class']);
				$attributes['class'] = join(' ',$attributes['class']);
			else:
				$attributes['class'] = 'container';
			endif;

			# Generate a valid HTML id.
			if(isset($this->container['attributes']['id']) && $this->container['attributes']['id'] && is_numeric($this->container['attributes']['id'][0])) $this->container['attributes']['id'] = '_'.$this->container['attributes']['id'];

			# Add additional values if they exist.
			if($this->container['width']):
				if(isset($attributes['style'])) $attributes['style'] = 'width:'.$this->container['width'].';'.$attributes['style'];
				else $attributes['style'] = 'width:'.$this->container['width'].';';
			endif;
			if($this->container['hide_if_empty'] && $this->is_empty()):
				if(isset($attributes['style'])) $attributes['style'] = 'display:none;'.$attributes['style'];
				else $attributes['style'] = 'display:none;';
			endif;

			# Push attributes to container.
			$holder->attributes($attributes);

			# Header if there is info for it.
			$header = $this->_header();
			if($header) $holder->append($header);

			# If before we need to add that stuff.
			if($this->container['before']) $holder->append($this->container['before']);

			# If we have content then lets encapsulate it.
			$content = $this->_content();
			if($content) $holder->append($content);

			# If after we need to add that stuff.
			if($this->container['after']) $holder->append($this->container['after']);

			# Create a footer if one is set.
			$footer = $this->_footer();
			if($footer) $holder->append($footer);

			return $holder;
		}

		/**
		 * Return an array of all the values for a Container.
		 * 
		 * @return array
		 */
		public function values() {
			if($this->container['hide_if_empty'] && !count($this->container['content'])) return array();

			# Holder element.
			$return = array('type' => $this->type);

			# Grab default attributes and defined attributes then add the container class.
			if(is_array($this->container['attributes'])) $return['attributes'] = array_merge($this->attributes,$this->container['attributes']);
			else $return['attributes'] = array();

			# Make sure the class exists.
			if(isset($return['attributes']['class'])):
				$return['attributes']['class'] = explode(' ',$return['attributes']['class']);
				array_unshift($return['attributes']['class'],'container');
				$return['attributes']['class'] = array_unique($return['attributes']['class']);
				$return['attributes']['class'] = join(' ',$return['attributes']['class']);
			else:
				$return['attributes']['class'] = 'container';
			endif;

			# Generate a valid HTML id.
			if(isset($this->container['attributes']['id']) && $this->container['attributes']['id'] && is_numeric($this->container['attributes']['id'][0])) $return['id'] = '_'.$this->container['attributes']['id'];

			# Add additional values if they exist.
			if($this->container['width']) $return['width'] = $this->container['width'];

			# Header
			if($this->container['title']) $return['title'] = $this->container['title'];

			# If we have header content.
			if($this->container['header']):
				$attributes = $this->_attributes($this->container['header']['attributes']);
				$return['header'] = (string)$this->tag->p(
						$this->container['header']['content'],$attributes
				);
			endif;

			# Footer.
			$return['footer'] = (string)$this->_footer();
			if(!$return['footer']) unset($return['footer']);

			# Content!
			$content = $this->content;
			$content = $this->tag->$content;
			$content->attributes(array('class' => 'content'));
			if(count($this->container['content'])):
				foreach($this->container['content'] as $row) $content->append($row);
			else:
				$holder = $this->holder;
				$content->append($this->tag->$holder('&nbsp;'));
			endif;
			$return['content'] = (string)$content;

			# Return the array.
			return $return;
		}

		/**
		 * Append raw conotent AFTER the Container body.
		 * 
		 * @param mixed $content
		 * @return \PHY\Container\_Abstract
		 */
		public function after($content=NULL) {
			$this->container['after'][] = $content;
			return $this;
		}

		/**
		 * Append content to the Container body.
		 * 
		 * @param mixed $content
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function append($content=false,array $attributes=NULL) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				$this->container['content'][] = $content;
			else:
				$tag = $this->item;
				$tag = $this->tag->$tag;
				$tag->append($content);
				if(is_array($attributes)) $tag->attributes($attributes);
				$this->container['content'][] = $tag;
			endif;
			return $this;
		}

		/**
		 * Sets attributes for the Container.
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function attributes(array $attributes=NULL) {
			if(is_array($attributes)):
				foreach($attributes as $key => $value):
					if($key === 'data' && is_array($value)) foreach($value as $k => $v) $this->container['attributes']['data-'.$k] = $v;
					elseif($key === 'class') $this->_class($value);
					else $this->container['attributes'][$key] = $value;
				endforeach;
			endif;
			return $this;
		}

		/**
		 * Add raw content before the Container body.
		 * 
		 * @param mixed $content
		 * @return \PHY\Container\_Abstract
		 */
		public function before($content=NULL) {
			$this->container['before'][] = $content;
			return $this;
		}

		/**
		 * Clear the Container values.
		 * 
		 * @return \PHY\Container\_Abstract
		 */
		public function clear() {
			return $this->__invoke();
		}

		/**
		 * Sets whether or not the container is to have an "error" class applied to it.
		 * 
		 * @param bool $error
		 * @return \PHY\Container\_Abstract
		 */
		public function error($error=true) {
			$this->_class('error',$error);
			return $this;
		}

		/**
		 * Sets whether or not the container is to have an "important" class applied to it.
		 * 
		 * @param bool $float
		 * @return \PHY\Container\_Abstract
		 */
		public function float($float=true) {
			$this->_class('float',$float);
			return $this;
		}

		/**
		 * Sets the Container footer.
		 * 
		 * @param type $footer
		 * @param type $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function footer($footer=false,$attributes=false) {
			if($footer):
				$this->container['footer'] = array(
					'content' => $footer,
					'attributes' => $attributes
				);
			endif;
			return $this;
		}

		/**
		 * Set the type of H tag the Container's header should use.
		 * 
		 * Send it a number 1-6 or a H tag.
		 * 
		 * @param mixed $h1
		 * @return \PHY\Container\_Abstract
		 */
		public function h1($h1='h1') {
			if(is_numeric($h1) && $h1 >= 1 && $h1 <= 6) $this->container['h1'] = 'h'.$h1;
			else $this->container['h1'] = in_array($h1,array('h1','h2','h3','h4','h5','h6'))?$h1:'h2';
			return $this;
		}

		/**
		 * Create a heading inside of the Container body.
		 * 
		 * @param mixed $content
		 * @param string $tag If $tag is an array then $attributes = $tag;
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function heading($content=false,$tag='h3',array $attributes=NULL) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				$this->container['content'][] = $content;
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
				$this->container['content'][] = $tag;
			endif;
			return $this;
		}

		/**
		 * Sets header content for the top right portion of a Container.
		 * 
		 * @param mixed $header
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function header($header=false,array $attributes=NULL) {
			if($header):
				if(is_object($header) && in_array($header->tag,self::$ITEMS)) $this->container['header'] = $header;
				else $this->container['header'] = array(
						'tag' => 'p',
						'content' => $header,
						'attributes' => $attributes,
					);
			endif;
			return $this;
		}

		/**
		 * Sets whether or not the container is to have an "important" class applied to it.
		 * 
		 * @param bool $important
		 * @return \PHY\Container\_Abstract
		 */
		public function important($important=true) {
			$this->_class('important',$important);
			return $this;
		}

		# Sets the info and displays it.

		/**
		 * Sets additional alert info that is collapsible.
		 * 
		 * @param mixed $info
		 * @return \PHY\Container\_Abstract
		 */
		public function info($info=NULL) {
			if($info !== NULL) $this->container['info'] = $info;
			return $this;
		}

		/**
		 * Set pagination for this Container.
		 * 
		 * Settings needs these defined:
		 *   'url' => '/page.php?page_id=[%i]' # string format [%i] for page_id.
		 *   'total' => 12 # number of pages.
		 *   'id' => 1 # current page id.
		 *   'limit' => 25 # number of items per page.
		 * 
		 * @param array $settings
		 * @param array $attributes
		 * @return _Abstract 
		 */
		public function pagination(array $settings=array(),array $attributes=array()) {
			if(isset($settings['url'],$settings['total']) && $settings['total'] > 1):
				if(is_array($settings['url'])):
					if(!count($settings['url'])):
						return $this;
					elseif(count($settings['url']) === 1):
						$settings['url'] = current($settings['url']);
					else:
						$url = array_shift($settings['url']);
						$page_id = '&page_id=[%i]';
						$parameters = array();
						foreach($settings['url'] as $key => $value):
							if($value === NULL || $value === '[%i]') $page_id = $key.'=[%i]';
							else $parameters[] = $key.'='.$value;
						endforeach;
						$settings['url'] = $url.'?'.join('&',$parameters).$page_id;
					endif;
				endif;
				if(!is_array($attributes)) $attributes = array();
				if(isset($attributes['class'])) $attributes['class'] .= ' pagination';
				else $attributes['class'] = 'pagination';
				if(!isset($settings['limit'])) $settings['limit'] = 10;
				if(!isset($settings['id']) || $settings['id'] < 1 || $settings['id'] > $settings['total']) $settings['id'] = 1;
				$pages = $this->tag->ul;

				$tens = floor($settings['id'] / 10) * 10;

				foreach(range($tens - 30,$tens,10) as $i):
					if($i <= 0) continue;
					elseif($i >= $tens) break;
					$pages->append(
						$this->tag->li($i != $settings['id']?$this->tag->url($i,str_replace('[%i]',$i,$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL):$this->tag->strong($i))
					);
				endforeach;
				foreach(range($settings['id'] - 4,$settings['id'] + 4) as $i):
					if($i <= 0) continue;
					elseif($i > $settings['total']) break;
					$pages->append(
						$this->tag->li($i != $settings['page_id']?$this->tag->url($i,str_replace('[%i]',$i,$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL):$this->tag->strong($i))
					);
				endforeach;
				foreach(range($tens,$tens + 30,10) as $i):
					if($i <= $tens) continue;
					elseif($i > $settings['total']) break;
					$pages->append(
						$this->tag->li($i != $settings['id']?$this->tag->url($i,str_replace('[%i]',$i,$settings['url']),isset($settings['attributes'])?$settings['attributes']:NULL):$this->tag->strong($i))
					);
				endforeach;
				$pages->prepend(
					$this->tag->li($settings['page_id'] > 1?$this->tag->url('&laquo;',str_replace('[%i]',($settings['id'] - 1),$settings['url']),isset($settings['attributes'])?array_merge($settings['attributes'],array('class' => 'ajax button black')):array('class' => 'button black')):$this->tag->span('&laquo;',array('class' => 'button disabled')),NULL)
				);
				$pages->append(
					$this->tag->li($settings['id'] < $settings['total']?$this->tag->url('&raquo;',str_replace('[%i]',($settings['id'] + 1),$settings['url']),isset($settings['attributes'])?array_merge($settings['attributes'],array('class' => 'ajax button black')):array('class' => 'button black')):$this->tag->span('&raquo;',array('class' => 'button disabled')),NULL)
				);
				$this->container['footer'] = array(
					'content' => array(
						$this->tag->p(
							array(
								'Page ',
								$this->tag->strong($settings['id']),
								' of ',
								$this->tag->strong(
									($settings['total'] > 100)?'100+':$settings['total']
								)
							)
						),
						$pages
					),
					'attributes' => $attributes
				);
			endif;
			return $this;
		}

		/**
		 * Prepend content to the Container body.
		 * 
		 * @param mixed $content
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function prepend($content=false,$attributes=NULL) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				array_unshift($this->container['content'],$content);
			else:
				$tag = $this->item;
				$tag = $this->tag->$tag;
				$tag->append($content);
				if(is_array($attributes)) $tag->attributes($attributes);
				array_unshift($this->container['content'],$tag);
			endif;
			return $this;
		}

		/**
		 * Append raw content to the Container body.
		 * 
		 * @param mixed $content
		 * @return \PHY\Container\_Abstract
		 */
		public function raw($content=false) {
			if(is_array($content)) $content = join('',$content);
			if(!$content) return false;
			else $this->container['content'][] = $content;
			return $this;
		}

		/**
		 * Set the Container's title text.
		 * 
		 * @param mixed $title
		 * @param array $attributes
		 * @return \PHY\Container\_Abstract
		 */
		public function title($title=false,array $attributes=NULL) {
			if($title):
				$this->container['title'] = array(
					'tag' => 'h3',
					'content' => $title,
					'attributes' => $attributes
				);
			endif;
			return $this;
		}

		# Sets the width of the container, in pixels or percent.

		public function width($width=0) {
			$width = (int)$width;
			if($width === 0) return false;
			elseif($width < 1) $this->container['width'] = ($width * 100).'%';
			else $this->container['width'] = $width.'px';
			return $this;
		}

		/**
		 * Internal cleaner for attributes.
		 * 
		 * If you send just a string then it will be set as the class
		 * If you send a string with a : then it will set key:value.
		 * 
		 * @param mixed $attributes
		 * @internal
		 * @access protected
		 * @return mixed
		 */
		protected function _attributes($attributes=NULL) {
			if($attributes === NULL) return;
			if(is_string($attributes)):
				$split = explode(':',$attributes);
				if(isset($split[1])) $attributes[$split[0]] = $split[1];
				else $attributes = array('class' => $attributes);
			endif;
			return $attributes;
		}

		/**
		 * Internal setter for class names and whether we want to add new ones
		 * or not.
		 * 
		 * @param string $classes
		 * @param bool $add
		 * @return bool
		 */
		protected function _class($classes=false,$add=true) {
			if(!$classes) return false;
			if(!is_array($classes)) $classes = array($classes);
			if($add):
				if(!is_array($this->container['attributes']) || !isset($this->container['attributes']['class'])):
					$this->container['attributes']['class'] = join(' ',$classes);
				else:
					$this->container['attributes']['class'] = explode(' ',$this->container['attributes']['class']);
					foreach($classes as $class) array_unshift($this->container['attributes']['class'],$class);
					$this->container['attributes']['class'] = join(' ',$this->container['attributes']['class']);
				endif;
			elseif(!is_array($this->container['attributes']) || !isset($this->container['attributes']['class'])):
				return false;
			else:
				foreach($classes as $class):
					$this->container['attributes']['class'] = explode(' ',$this->container['attributes']['class']);
					if($key = array_search($class,$this->container['attributes']['class'])) unset($this->container['attributes']['class'][$key]);
					$this->container['attributes']['class'] = join(' ',$this->container['attributes']['class']);
				endforeach;
			endif;
			return true;
		}

		/**
		 * Render the content.
		 * 
		 * @internal
		 * @access protected
		 * @return \PHY\Markup\_Abstract
		 */
		protected function _content() {
			$content = $this->content;
			$content = $this->tag->$content;
			$content->attributes(array('class' => 'content'));
			if(count($this->container['content'])):
				foreach($this->container['content'] as $row) $content->append($row);
			else:
				$holder = $this->holder;
				$content->append($this->tag->$holder('&nbsp;'));
			endif;
			return $content;
		}

		/**
		 * Render the footer.
		 * 
		 * @internal
		 * @access protected
		 * @return \PHY\Markup\_Abstract
		 */
		protected function _footer() {
			$footer = NULL;
			if($this->container['footer']):
				$attributes = $this->_attributes($this->container['footer']['attributes']);
				$footer = $this->tag->footer($this->container['footer']['content'],$attributes);
			endif;
			return $footer;
		}

		/**
		 * Render the header.
		 * 
		 * @internal
		 * @access protected
		 * @return \PHY\Markup\_Abstract
		 */
		protected function _header() {
			if(!$this->container['title'] && !$this->container['header']) return false;
			# Add header if it exists.
			$header = $this->tag->header;
			$tag = in_array($this->container['h1'],array('h1','h2','h3','h4','h5','h6'))?$this->container['h1']:'h2';
			if($this->container['title']) $header->append(
					$this->tag->$tag(
						$this->container['title']['content'],$this->container['title']['attributes']
					)
				);

			# Otherwise we'll assume it's just content and toss that up instead.
			if($this->container['header']):
				if(is_object($this->container['header'])):
					$header->append($this->container['header']);
				else:
					$attributes = $this->_attributes($this->container['header']['attributes']);
					$header->append(
						$this->tag->p(
							$this->container['header']['content'],$attributes
						)
					);
				endif;
			endif;
			return $header;
		}

	}
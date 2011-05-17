<?php

	namespace PHY\Markup;

	/**
	 * The actual Markup element object. This is where the magic happens.
	 *
	 * @category Markup
	 * @package Markup_Element
	 * @author John Mullanaphy
	 * @final
	 * @internal
	 */
	final class Element {

		private static $ELEMENTS = 0,
		$STORE = array('h1','h2','h3','h4','h5','h6','strong','p'),
		$HEAP = NULL;
		private $attributes = array(),
		$content = array(),
		$tag = 'div',
		$void = false;

		/**
		 * Creates a Markup_Element based on provided data.
		 *
		 * @param string $tag
		 * @param array $attributes
		 * @param bool $void
		 * @return Markup_Element
		 */
		public function __construct($tag='div',$attributes=array(),$void=false) {
			++self::$ELEMENTS;
			if(is_array($attributes)) $this->attributes = $attributes;
			$this->tag = strtolower((string)$tag);
			$this->void = !!$void;
			return $this;
		}

		/**
		 * Sets an attribute based on $tag->$key($value);
		 */
		public function __call($key,$parameters) {
			$this->attributes(array($key => join(' ',$parameters)));
			return $this;
		}

		/**
		 * Returns any readonly and defined values.
		 */
		public function __get($key) {
			if(isset($this->$key)) return $this->$key;
		}

		/**
		 * Generates the HTML and will recursively generate HTML out of inner
		 * content.
		 *
		 * @return string
		 */
		public function __toString() {
			if(in_array($this->tag,self::$STORE)):
				if(self::$HEAP === NULL) self::$HEAP = new \PHY\Markup\Heap;
				$row = new \stdClass;
				$row->content = strip_tags(join('',$this->content));
				$row->tag = $this->tag;
				$row->rank = array_search($this->tag,self::$STORE);
				self::$HEAP->insert($row);
			endif;
			if($this->void) return '<'.$this->tag.$this->_attributes().' />';
			else return '<'.$this->tag.$this->_attributes().'>'.$this->_recursion($this->content).'</'.$this->tag.'>';
		}

		/**
		 * AJAXify an element. Will add a class of "ajax" as well as assign
		 * data-$key = $value based on a supplied array of $key => $value.
		 *
		 * @param array $data
		 * @return Markup_Element
		 */
		public function ajax($data=NULL) {
			$this->attributes(array('class' => 'ajax'));
			return is_array($data)
				?$this->data($data)
				:$this;
		}

		/**
		 * Append content into an element.
		 *
		 * @param mixed $content
		 * @return Markup_Element
		 */
		public function append($content=NULL) {
			if($content === NULL) return;
			elseif(is_array($content)) foreach($content as $row) $this->content[] = $row;
			else $this->content[] = $content;
			return $this;
		}

		/**
		 * Change attributes of an element.
		 *
		 * @param array $attributes
		 * @return Markup_Element
		 */
		public function attributes(array $attributes) {
			foreach($attributes as $key => $value):
				if($key === 'data' && is_array($value)) foreach($value as $k => $v) $this->attributes['data-'.$k] = $v;
				elseif($key === 'class' && isset($this->attributes['class'])) $this->attributes['class'] .= ' '.$value;
				else $this->attributes[$key] = $value;
			endforeach;
			return $this;
		}

		/**
		 * Return all the content of this element.
		 *
		 * @return array
		 */
		public function content() {
			return $this->content;
		}

		/**
		 * Define any data-$key = $value settings based on a $key => $value
		 * array.
		 *
		 * @param array $data
		 * @return Markup_Element
		 */
		public function data(array $data) {
			foreach($data as $key => $value) $this->attributes['data-'.$key] = $value;
			return $this;
		}

		/**
		 * Returns whether this element has no innerHTML or not.
		 * @return bool
		 */
		public function is_empty() {
			if(count($this->content)) foreach($this->content as $content) if($content !== NULL) return false;
			return true;
		}

		/**
		 * Prepend content into an element.
		 *
		 * @param mixed $content
		 * @return Markup_Element
		 */
		public function prepend($content=NULL) {
			if($content === NULL) return;
			elseif(is_array($content)) foreach(array_reverse($content) as $row) array_unshift($this->content,$row);
			else array_unshift($this->content,$content);
			return $this;
		}

		/**
		 * Change the tag type of an element.
		 *
		 * @param string $tag
		 * @return Markup_Element
		 */
		public function tag($tag='div') {
			$this->tag = strtolower((string)$tag);
			return $this;
		}

		/**
		 * Change whether this element is a void or not.
		 *
		 * @param bool $void
		 * @return Markup_Element
		 */
		public function void($void=true) {
			$this->void = !!$void;
			return $this;
		}

		/**
		 * This parses $key => $value attributes into a HTML $key="$value"
		 * string.
		 *
		 * @ignore
		 * @return string HTML attributes.
		 */
		private function _attributes() {
			$return = array();
			$onsubmit = false;
			foreach($this->attributes as $key => $value):
				if($key === 'data' && is_array($value)) foreach($value as $k => $v) $return[] = 'data-'.$k.'="'.htmlentities(
								is_array($v)
									?join(';',$v)
									:$v,ENT_QUOTES,'UTF-8',false).'"';
				else $return[] = $key.'="'.htmlentities(
							is_array($value)
								?join(' ',$value)
								:$value,ENT_QUOTES,'UTF-8',false).'"';
				if($key === 'onsubmit') $onsubmit = true;
			endforeach;
			if($this->tag === 'form' && !$onsubmit && in_array(USER_BROWSER,array('ie','ie6'))) $return[] = 'onsubmit=";var d=document.documentElement;if(d.onsubmit){return d.onsubmit(event);}else{return Event.fire(d,\'submit\',event);}"';
			return ((count($return))
				?' '.join(' ',$return)
				:false);
		}

		/**
		 * Recursively converts Markup objects into HTML by using
		 * Markup_Element::__toString();
		 *
		 * @param mixed $content
		 * @ignore
		 * @return string
		 */
		private function _recursion($content=NULL) {
			$return = array();
			if($content === false || $content === NULL) return;
			elseif(!is_array($content)) $return[] = $content;
			else foreach($content as $element) $return[] = $this->_recursion($element);
			return join('',$return);
		}

		/**
		 * Return a count of all elements that Markup have used.
		 *
		 * @return int
		 */
		static public function elements() {
			return self::$ELEMENTS;
		}

		/**
		 * Return a Heap of all important tags for SEO purposes.
		 *
		 * @return array
		 */
		static public function important() {
			return self::$HEAP;
		}

	}
<?php

	namespace PHY\Markup;

	/**
	 * Abstract class that adds helper methods for various HTML tags.
	 *
	 * @category Markup
	 * @package Markup\_Abstract
	 * @author John Mullanaphy
	 * @abstract
	 */
	abstract class _Abstract {

		protected $element = NULL,
		$events = array(),
		$standard = array(),
		$voids = array(),
		$tags = array();

		/**
		 * Calls a tag based on $Markup->$tag();
		 *
		 * @param mixed $innerHTML
		 * @param array $attributes
		 * @return Markup_Element
		 */
		public function __call($function,$parameters) {
			$function = strtolower($function);
			if(in_array($function,array_keys($this->tags))):
				if(in_array($function,$this->voids)):
					$this->element = new \PHY\Markup\Element($function,((isset($parameters[0]))?$this->_attributes($function,$parameters[0]):NULL),true);
				else:
					$this->element = new \PHY\Markup\Element($function,((isset($parameters[1]))?$this->_attributes($function,$parameters[1]):NULL),false);
					if(isset($parameters[0])) $this->element->append($parameters[0]);
				endif;
				return $this->element;
			else:
				trigger_error('Tag <strong>'.strtoupper($function).'</strong> is not defined in <strong>'.str_replace('Markup_','',get_class($this)).'</strong>',E_USER_NOTICE);
				return false;
			endif;
		}

		/**
		 * Returns a tag object based on $Markup->$tag
		 *
		 * @return Markup_Element
		 */
		public function __get($function) {
			return $this->__call($function,array());
		}

		/**
		 * Cleans attributes of $attributes down to ones that are allowed for
		 * $tag.
		 *
		 * @param string $tag
		 * @param array $attributes
		 * @access protected
		 * @return <type>
		 */
		protected function _attributes($tag=NULL,$attributes=NULL) {
			if($tag === NULL || !is_array($attributes)) return;
			$allowed = $this->standard + $this->events;
			$return = array();
			if(is_array($this->tags[$tag])) foreach($this->tags[$tag] as $key => $value) $allowed[$key] = $value;
			foreach($attributes as $key => $value):
				if(isset($allowed[$key])):
					if(!is_array($allowed[$key])):
						$return[$key] = $value;
					elseif(in_array($value,$allowed[$key])):
						$return[$key] = $value;
					endif;
				elseif($key === 'data' && is_array($value)):
					foreach($value as $k => $v) $return['data-'.$k] = $v;
				elseif(strpos($key,'data-') !== false):
					$return[$key] = $value;
				endif;
			endforeach;
			return $return;
		}

		/**
		 * Adds a tag to the lexicon. If tag is already defined it will add any
		 * new attributes provided and set whether it is a void or not.
		 *
		 * @param string $tag
		 * @param array $attributes
		 * @param bool $void
		 * @final
		 * @return bool
		 */
		final public function add($tag=NULL,$attributes=NULL,$void=false) {
			if(!$tag) return false;
			if(isset($this->tags[$tag])):
				if(is_array($attributes)):
					if(is_array($this->tags[$tag])) foreach($attributes as $key => $value) $this->tags[$tag][$key] = $value;
					else $this->tags[$tag] = $attributes;
				else:
					$this->tags[$tag] = true;
				endif;
				if($void):
					$this->voids[] = $tag;
				else:
					foreach($this->voids as $key => $value) if($tag === $value) unset($this->voids[$key]);
				endif;
			else:
				$this->tags[$tag] = (($attributes !== NULL && (is_array($attributes) || $attributes === true))?$attributes:true);
				if($void) $this->voids[] = $tag;
			endif;
			return true;
		}

		/**
		 * Remove a tag from the lexicon in use.
		 *
		 * IF Attributes are sent it will only remove attributes that are sent,
		 * NOT the tag itself.
		 *
		 * @param string $tag
		 * @param array $attributes
		 * @return bool
		 */
		final public function remove($tag=NULL,$attributes=NULL) {
			if($tag === NULL || !isset($this->tags[$tag])) return false;
			if(is_array($attributes)):
				foreach($attributes as $attribute) if(isset($this->tags[$tag][$attribute])) unset($this->tags[$tag][$attribute]);
				return true;
			else:
				unset($this->tags[$tag]);
				if(in_array($tag,$this->voids)):
					foreach($this->voids as $key => $value):
						if($tag === $value):
							unset($this->voids[$key]);
							return true;
						endif;
					endforeach;
				endif;
				return true;
			endif;
		}

		/**
		 * Create a generic cancel button.
		 *
		 * @param string $value
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function cancel($value=NULL,$attributes=NULL) {
			$attributes = $this->_attributes('button',$attributes);
			if(isset($attributes['class'])) $attributes['class'] = 'button red '.$attributes['class'];
			else $attributes['class'] = 'button red';
			$button = new \PHY\Markup\Element('button',$attributes,true);
			$button->append($value);
			return $button;
		}

		/**
		 * Create a generic checkbox.
		 *
		 * @param string $name Name for the checkbox.
		 * @param mixed $label innerHTML to add after the input box itself.
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function checkbox($name=NULL,$label=NULL,$attributes=NULL) {
			if($name === NULL) return;
			$attributes = $this->_attributes('input',$attributes);
			$attributes['name'] = $name;
			$attributes['type'] = 'checkbox';
			if(!isset($attributes['value'])) $attributes['value'] = 1;
			if(!isset($attributes['id'])) $attributes['id'] = 'checkbox_'.PHY\String::random(8);
			$tag = new \PHY\Markup\Element('label',array('class' => 'checkbox','for' => $attributes['id']));
			$tag->append(new \PHY\Markup\Element('input',$attributes,true));
			$tag->append($label);
			return $tag;
		}

		/**
		 * A generic definition tag.
		 *
		 * @param string $term Term to be defined.
		 * @param mixed $definition Definition for $term.
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function definition($term=NULL,$definition=NULL,$attributes=NULL) {
			if($term === NULL || $definition === NULL) return;
			$attributes = $this->_attributes('dl',$attributes);
			$dl = new \PHY\Markup\Element('dl',$attributes);
			$dt = new \PHY\Markup\Element('dt');
			$dt->append($term);
			$dl->append($dt);
			if(!is_array($definition)) $definition = array($definition);
			foreach($definition as $item):
				if(is_array($item)):
					$dd_attributes = $this->_attributes('dd',$item[1]);
					if(!isset($dd_attributes['id']) && isset($attributes['id'])) $li_attributes['id'] = $attributes['id'].'_dd_'.$i;
					$item = $item[0];
				elseif(isset($attributes['id'])):
					$dd_attributes = array('id' => $attributes['id'].'_dd_'.$i);
				else:
					$dd_attributes = NULL;
				endif;
				$dd = new \PHY\Markup\Element('dd',$dd_attributes);
				$dd->append($item);
				$dl->append($dd);
			endforeach;
			return $dl;
		}

		/**
		 * A simple hidden input field.
		 *
		 * IF $name is an array, it will create hidden inputs as $key => $label
		 * pairings and return an array of hidden fields while $value becomes
		 * $attributes.
		 *
		 * @param string|array $name
		 * @param string $value
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function hidden($name=NULL,$value=NULL,$attributes=NULL) {
			if($name === NULL):
				return;
			elseif(is_array($name)):
				$inputs = array();
				$attributes = $value;
				foreach($name as $k => $v) $inputs[] = $this->hidden($k,$v,$attributes);
				return $inputs;
			endif;
			$attributes = $this->_attributes('input',$attributes);
			$attributes['name'] = $name;
			$attributes['type'] = 'hidden';
			switch($attributes['name']):
				case 'xsrf_id':
					$attributes['value'] = $_COOKIE['xsrf'];
					break;
				default:
					$attributes['value'] = $value;
			endswitch;
			$input = new \PHY\Markup\Element('input',$attributes,true);
			return $input;
		}

		/**
		 * Return a generic img tag.
		 *
		 * @param string $src
		 * @param string $title Optional
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function image($src=NULL,$title=NULL,$attributes=NULL) {
			if($src === NULL) return;
			$attributes = $this->_attributes('img',$attributes);
			if($title !== NULL) $attributes['alt'] = $title;
			$attributes['src'] = $src;
			if(!isset($attributes['alt'])) $attributes['alt'] = is_string($src)?$src:NULL;
			$img = new \PHY\Markup\Element('img',$attributes,true);
			return $img;
		}

		/**
		 * Create an ordered list via a supplied array.
		 *
		 * @param array $content
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function ordered($content=NULL,$attributes=NULL) {
			if($content === NULL) return;
			elseif(!is_array($content)) $content = array($content);
			$attributes = $this->_attributes('ol',$attributes);
			$ol = new \PHY\Markup\Element('ol',$attributes);
			$i = 0;
			foreach($content as $item):
				if(!$item):
					continue;
				elseif(is_array($item)):
					$li_attributes = $this->_attributes('li',$item[1]);
					if(!isset($li_attributes['id']) && isset($attributes['id'])) $li_attributes['id'] = $attributes['id'].'_li_'.$i;
					$item = $item[0];
				elseif(isset($attributes['id'])):
					$li_attributes = array('id' => $attributes['id'].'_li_'.$i);
				else:
					$li_attributes = NULL;
				endif;
				$li = new \PHY\Markup\Element('li',$li_attributes);
				$li->append($item);
				$ol->append($li);
				++$i;
			endforeach;
			return $ol;
		}

		/**
		 * Create a password input box.
		 *
		 * @param string $name
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function password($name=NULL,$attributes=NULL) {
			if($name === NULL) return;
			$attributes = $this->_attributes('input',$attributes);
			$attributes['type'] = 'password';
			$attributes['name'] = $name;
			return new \PHY\Markup\Element('input',$attributes,true);
		}

		/**
		 * Create a group of radio buttons.
		 *
		 * @param string $name
		 * @param array $values
		 * @param array $attributes Optional. 'checked' => $key for choosing which radio is selected.
		 * @return <type>
		 */
		public function radio($name=NULL,array $values,$attributes=NULL) {
			if($name === NULL || !$values) return;
			$radio = array();
			$checked = is_array($attributes) && isset($attributes['checked'])?$attributes['checked']:false;
			$attributes = $this->_attributes('label',$attributes);
			if(!is_array($attributes)) $attributes = array();
			if(isset($attributes['id'])):
				$id = $attributes['id'];
				unset($attributes['id']);
			else:
				$id = 'radio_'.$name;
			endif;
			if(!isset($attributes['class'])) $attributes['class'] = 'radio';
			foreach($values as $key => $value):
				if(is_array($value)):
					$attributes_input = ((isset($value['attributes'])?$this->_attributes('input',$value['attributes']):array()));
					if(isset($value['checked']) && $value['checked'] || $key == $checked) $attributes_input['checked'] = 'checked';
					$attributes_input['name'] = $name;
					$attributes_input['id'] = $id.'_'.$key;
					$attributes_input['type'] = 'radio';
					if(isset($value['content'])) $value = $value['content'];
					else $value = $key;
					if(!isset($attributes_input['value'])) $attributes_input['value'] = $key;
					$attributes['for'] = $attributes_input['id'];
					$label = new \PHY\Markup\Element('label',$attributes);
					$label->append(new \PHY\Markup\Element('input',$attributes_input));
					$label->append($value);
				else:
					$attributes['for'] = $id.'_'.$key;
					$label = new \PHY\Markup\Element('label',$attributes);
					$label->append(
						new \PHY\Markup\Element(
							'input',
							(
							($key == $checked)?array(
								'checked' => 'checked',
								'id' => $id.'_'.$key,
								'name' => $name,
								'type' => 'radio',
								'value' => $key
								):array(
								'id' => $id.'_'.$key,
								'name' => $name,
								'type' => 'radio',
								'value' => $key
								)
							)
						)
					);
					$label->append($value);
				endif;
				$radio[] = $label;
			endforeach;
			return join('',$radio);
		}

		/**
		 * Create a generic reset input button.
		 *
		 * @param string $name
		 * @param string $value
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function reset($name=NULL,$value=NULL,$attributes=NULL) {
			$attributes = $this->_attributes('input',$attributes);
			$attributes['name'] = $name;
			$attributes['type'] = 'reset';
			$attributes['value'] = $value;
			if(isset($attributes['class'])) $attributes['class'] = 'reset '.$attributes['class'];
			else $attributes['class'] = 'reset';
			return new \PHY\Markup\Element('input',$attributes,true);
		}

		/**
		 * Create a generic select drop box.
		 *
		 * @param string $name
		 * @param array $values
		 * @param array $attributes Optional. 'selected' => $key for choosing which option is selected.
		 * @return <type>
		 */
		public function selectbox($name=NULL,array $values,$attributes=NULL) {
			if($name === NULL || !count($values)) return;
			if(!is_array($attributes)):
				$attributes['name'] = $name;
				$selected = false;
			elseif(isset($attributes['selected'])):
				if(!isset($attributes['name'])) $attributes['name'] = $name;
				$selected = $attributes['selected'];
				unset($attributes['selected']);
			else:
				if(!isset($attributes['name'])) $attributes['name'] = $name;
				$selected = false;
			endif;
			$select = new \PHY\Markup\Element('select',$this->_attributes('select',$attributes));
			foreach($values as $key => $value):
				if(is_array($value) && isset($value['content'])):
					if(is_array($value['content'])):
						$optgroup = new \PHY\Markup\Element('optgroup',array());
						foreach($value['content'] as $k => $v):
							$option = new \PHY\Markup\Element('option',(($k === 'selected' || $k === $selected)?array('selected' => 'selected','value' => ((isset($v['value']))?$v['value']:$v['content'])):array('value' => ((isset($v['value']))?$v['value']:$v['content']))));
							$option->append($v['content']);
							$optgroup->append($option);
						endforeach;
						$select->append($optgroup);
					else:
						$option = new \PHY\Markup\Element('option',(($key === 'selected' || $key === $selected)?array('selected' => 'selected','value' => ((isset($value['value']))?$value['value']:$value['content'])):array('value' => ((isset($value['value']))?$value['value']:$value['content']))));
						$option->append($value);
						$select->append($option);
					endif;
				else:
					if(is_array($value)):
						$optgroup = new \PHY\Markup\Element('optgroup',array());
						foreach($value as $k => $v):
							$option = new \PHY\Markup\Element('option',(($k === 'selected' || $k === $selected)?array('selected' => 'selected','value' => $v):array('value' => $v)));
							$option->append($v);
							$optgroup->append($option);
						endforeach;
						$select->append($optgroup);
					else:
						$option = new \PHY\Markup\Element('option',(($key === 'selected' || $key === $selected)?array('selected' => 'selected','value' => $key):array('value' => $key)));
						$option->append($value);
						$select->append($option);
					endif;
				endif;
			endforeach;
			return $select;
		}

		/**
		 * Create a generic submit button.
		 *
		 * @param string $name
		 * @param string $value
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function submit($name=NULL,$value=NULL,$attributes=NULL) {
			if(is_array($value)):
				$attributes = $value;
				$value = $name;
				$name = false;
			elseif($value === NULL):
				$value = $name;
				$name = false;
			endif;
			$attributes = $this->_attributes('input',$attributes);
			if($name) $attributes['name'] = $name;
			$attributes['type'] = 'submit';
			$attributes['value'] = $value;
			if(isset($attributes['class'])) $attributes['class'] = 'submit button '.$attributes['class'];
			else $attributes['class'] = 'submit button';
			return new \PHY\Markup\Element('input',$attributes,true);
		}

		/**
		 * Create a generic textbox. If size = 1 it will be an input box, any
		 * larger and it becomes a textarea.
		 *
		 * @param string $name
		 * @param int $size
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function textbox($name=NULL,$size=1,$attributes=NULL) {
			if($name === NULL) return;
			$value = isset($attributes['value'])?$attributes['value']:NULL;
			if(isset($attributes['hint'])):
				if(!$value):
					if(isset($attributes['class'])) $attributes['class'] = $attributes['class'].' hint';
					else $attributes['class'] = 'hint';
					$value = $attributes['hint'];
				endif;
			endif;
			$attributes = $this->_attributes((($size <= 1)?'input':'textarea'),$attributes);
			$attributes['name'] = $name;
			if($size <= 1):
				if($value !== NULL) $attributes['value'] = htmlentities($value,ENT_QUOTES,'UTF-8',false);
				$attributes['type'] = 'text';
				$input = new \PHY\Markup\Element('input',$attributes,true);
			else:
				if(!isset($attributes['cols'])) $attributes['cols'] = 15;
				if(!isset($attributes['rows'])) $attributes['rows'] = $size;
				$input = new \PHY\Markup\Element('textarea',$attributes);
				if($value !== NULL) $input->append(htmlentities($value,ENT_QUOTES,'UTF-8',false));
			endif;
			return $input;
		}

		/**
		 * Create a generic time tag.
		 *
		 * IF $date is an array, then the current time is used and no format
		 * is set while $date is mapped to $attributes instead.
		 *
		 * IF there is no format then timestamp will come back as a relative
		 * time difference from time().
		 *
		 * @param datetime $date
		 * @param string $format
		 * @param array $attributes
		 * @return Markup_Element
		 */
		public function timestamp($date=NULL,$format=NULL,$attributes=NULL) {
			if(is_array($date)):
				$attributes = $date;
				$date = time();
				$format = NULL;
			else:
				if($date === NULL) $date = time();
				elseif(!is_int($date)) $date = strtotime($date);
				if(is_array($format)):
					$attributes = $format;
					$format = NULL;
				endif;
			endif;
			$attributes = $this->_attributes($attributes);
			$attributes['datetime'] = date('c',$date);
			$time = new \PHY\Markup\Element('time',$attributes,false);
			$time->append($format !== NULL?date($format,$date):PHY\String::date($date));
			return $time;
		}

		/**
		 * Create a generic unordered list out of an array.
		 *
		 * @param array $content
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function unordered($content=NULL,$attributes=NULL) {
			if($content === NULL) return;
			elseif(!is_array($content)) $content = array($content);
			$attributes = $this->_attributes('ul',$attributes);
			$ul = new \PHY\Markup\Element('ul',$attributes);
			$i = 0;
			foreach($content as $item):
				if(!$item):
					continue;
				elseif(is_array($item)):
					$li_attributes = $this->_attributes('li',isset($item[1])?$item[1]:NULL);
					if(!isset($li_attributes['id']) && isset($attributes['id'])) $li_attributes['id'] = $attributes['id'].'_li_'.$i;
					$item = $item[0];
				elseif(isset($attributes['id'])):
					$li_attributes = array('id' => $attributes['id'].'_li_'.$i);
				else:
					$li_attributes = NULL;
				endif;
				$li = new \PHY\Markup\Element('li',$li_attributes);
				$li->append($item);
				$ul->append($li);
				++$i;
			endforeach;
			return $ul;
		}

		/**
		 * Create a generic anchor tag.
		 *
		 * @param mixed $content innerHTML of the anchor tag.
		 * @param string|array $link array will create a ?key=value structure
		 * while the first value in the array will be the link itself.
		 * @param array $attributes Optional
		 * @return Markup_Element
		 */
		public function url($content=NULL,$link=NULL,$attributes=NULL) {
			if($content === NULL || $link === NULL) return;
			if(is_array($link)):
				if(is_string(key($link))) $url = '/rest.php';
				else $url = array_shift($link);
				if(count($link)):
					$get = array();
					if(isset($link['#'])):
						$hash = '#'.$link['#'];
						unset($link['#']);
					else:
						$hash = NULL;
					endif;
					foreach($link as $key => $value) if($value !== NULL) $get[] = $key.'='.$value;
					if($url === '/rest.php') $get[] = 'xsrf='.$_COOKIE['xsrf'];
					$link = $url.'?'.join('&',$get).$hash;
				else:
					$link = $url;
				endif;
			elseif(\PHY\Validate::email($link)):
				$link = 'mailto:'.$link;
			endif;
			$attributes = $this->_attributes('a',$attributes);
			if(!isset($attributes['title']) && (is_string($content) || is_numeric($content))) $attributes['title'] = $content;
			# We have a Shebang, let's make it ajaxified.
			if(substr($link,0,2) === '#!'):
				$link = str_replace('#!','',$link);
				if(!isset($attributes['class'])) $attributes['class'] = 'ajax';
				else $attributes['class'] .= ' ajax';
				if(!isset($attributes['data']) || !is_array($attributes['data'])) $attributes['data'] = array('shebang' => 1);
				else $attributes['data']['shebang'] = 1;
			endif;
			if(strpos($link,'javascript:') !== false && !isset($attributes['onclick'])):
				$attributes['href'] = 'javascript:void(0);';
				$attributes['onclick'] = str_replace('javascript:','',$link);
			else:
				$attributes['href'] = $link;
			endif;
			$a = new \PHY\Markup\Element('a',$attributes);
			$a->append($content);
			return $a;
		}

		/**
		 * Returns a total count of elements used via Markup_Abstract.
		 *
		 * @return int
		 */
		static public function elements() {
			return \PHY\Markup\Element::elements();
		}

		/**
		 * Returns a list of important tags used. For SEO\Metadata purposes.
		 *
		 * @return array
		 */
		static public function important() {
			return \PHY\Markup\Element::important();
		}

	}
<?php

	/**
	 * Work with only whatever tags you want.
	 *
	 * @category Markup
	 * @package Markup_Raw
	 * @author John Mullanaphy
	 */
	class Markup_Raw extends Markup_Abstract {

		public function __call($function,$parameters) {
			$function = strtolower($function);
			if(isset($parameters[1]['void']) && $parameters[1]['void']):
				$this->element = new Markup_Element($function,((isset($parameters[0]))?$this->_attributes($function,$parameters[0]):NULL),true);
			else:
				$this->element = new Markup_Element($function,((isset($parameters[1]))?$parameters[1]:NULL),false);
				if(isset($parameters[0])) $this->element->append($parameters[0]);
			endif;
			return $this->element;
		}

		public function __get($function) {
			$function = strtolower($function);
			$this->element = new Markup_Element($function,NULL,in_array($function,$this->voids),false);
			return $this->element;
		}

		protected function _attributes($tag=NULL,$attributes=NULL) {
			if($tag === NULL || $attributes === NULL) return;
			$return = array();
			if(is_string($attributes)):
				$split = split(':',$attributes);
				if(isset($split[1])) $attributes[$split[0]] = $split[1];
				else $attributes = array('class' => $attributes);
			elseif(!is_array($attributes)):
				return;
			endif;
			foreach($attributes as $key => $value) $return[$key] = $value;
			return $return;
		}

	}
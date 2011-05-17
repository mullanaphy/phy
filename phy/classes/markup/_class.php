<?php

	namespace PHY;

	/**
	 * Markup Factory so you only have to call $Markup = new Markup; or
	 * $Markup = Markup::tag(); instead of needing to worry about what version
	 * to use.
	 *
	 * @category Markup
	 * @package Markup
	 * @final
	 */
	final class Markup {
		const DEFAULT_LANGUAGE = '\PHY\Markup\HTML5';
		static private $instance = NULL;

		/**
		 * On construct you can define a language. Note, if you call it as an
		 * Object then this class will be layered over a Markup_Abstract Class.
		 *
		 * @param string $language
		 * @return Markup_Abstract
		 */
		public function __construct($language=NULL) {
			return self::instance($language);
		}

		/**
		 * Abstracts to self::$instance's __call.
		 */
		public function __call($tag,$parameters) {
			if($tag === 'use') return $this->instance($parameters[0]);
			elseif(method_exists(self::$instance,$tag)) return call_user_func_array(array(self::$instance,$tag),$parameters);
			else return self::$instance->__call($tag,$parameters);
		}

		/**
		 * Abstracts to self::$instance's __call.
		 */
		public static function __callStatic($function,$parameters) {
			if(method_exists(self::$instance,$function)) return call_user_func_array(array(self::$instance,$function),$parameters);
			elseif(self::$instance) return self::$instance->__call($function,$parameters);
			else return 0;
		}

		/**
		 * Abstracts to self::$instance's __get.
		 */
		public function __get($tag) {
			return self::$instance->$tag;
		}

		/**
		 * Actual Factory method for HTML.
		 *
		 * <code>
		 * <?php
		 *    $instance = Markup::tag();
		 *    var_dump($instance); // Returns a Markup_Abstract object.
		 * </code>
		 *
		 * @param string $language
		 * @return Markup_Abstract
		 */
		static public function instance($language=NULL) {
			$default = self::DEFAULT_LANGUAGE;
			if($language === NULL):
				if(self::$instance === NULL):
					self::$instance = new $default;
				endif;
			elseif(is_object($language)):
				if($language instanceof \PHY\Markup\_Abstract):
					self::$instance = $language;
				elseif(self::$instance === NULL):
					self::$instance = new $default;
				endif;
			elseif(class_exists('\PHY\Markup\\'.$language,true)):
				$language = '\PHY\Markup\\'.strtoupper($language);
				self::$instance = new $language;
				if(!(self::$instance instanceof \PHY\Markup\_Abstract)):
					self::$instance = new $default;
				endif;
			else:
				self::$instance = new $default;
			endif;
			return self::$instance;
		}

	}
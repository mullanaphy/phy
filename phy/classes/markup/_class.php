<?php
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
		const DEFAULT_LANGUAGE = 'Markup_HTML5';
		static private $tag = NULL;

		/**
		 * On construct you can define a language. Note, if you call it as an
		 * Object then this class will be layered over a Markup_Abstract Class.
		 *
		 * @param string $language
		 * @return Markup_Abstract
		 */
		public function __construct($language=NULL) {
			return self::tag($language);
		}

		/**
		 * Abstracts to self::$tag's __call.
		 */
		public function __call($function,$parameters) {
			if(method_exists(self::$tag,$function)) return call_user_func_array(array(self::$tag,$function),$parameters);
			else return self::$tag->__call($function,$parameters);
		}

		/**
		 * Abstracts to self::$tag's __get.
		 */
		public function __get($function) {
			return self::$tag->$function;
		}

		/**
		 * Actual Factory method for HTML.
		 *
		 * <code>
		 * <?php
		 *    $tag = Markup::tag();
		 *    var_dump($tag); // Returns a Markup_Abstract object.
		 * </code>
		 *
		 * @param string $language
		 * @return Markup_Abstract
		 */
		static public function tag($language=NULL) {
			$default = self::DEFAULT_LANGUAGE;
			if($language === NULL):
				if(self::$tag === NULL):
					self::$tag = new $default;
				endif;
			elseif(class_exists('markup_'.$language,true)):
				$language = 'Markup_'.strtoupper($language);
				self::$tag = new $language;
				if(!(self::$tag instanceof Markup_Abstract)):
					self::$tag = new $default;
				endif;
			else:
				self::$tag = new $default;
			endif;
			return self::$tag;
		}

	}
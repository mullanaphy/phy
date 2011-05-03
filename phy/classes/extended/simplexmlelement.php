<?php

	namespace PHY\Extended;

	/**
	 * Extended to add a cleaner namespace method.
	 *
	 * @category Extended
	 * @package Extended_SimpleXMLElement
	 * @author John Mullanaphy
	 */
	class SimpleXMLElement extends \SimpleXMLElement {

		private static $namespaces = array();

		/**
		 * Define a namespace to use throughout our XML document.
		 *
		 * @param string $namespace
		 * @param string $url
		 * @return bool
		 */
		public function addNamespace($namespace=false,$url=false) {
			if(!$namespace || !$url || isset($this->namespaces[$namespace])) return false;
			self::$namespaces[$namespace] = $url;
			$this->addAttribute($namespace.':mullanaphy','',$url);
			return true;
		}

		/**
		 * Clean out the extra :mullanaphy in the opener tag.
		 * 
		 * @return string
		 */
		public function asXML() {
			return trim(preg_replace(array('# \w+\:mullanaphy=""#','#>\s*<#'),array('','>'.PHP_EOL.'<'),parent::asXML()));
		}

		/**
		 * The extended version returns itself for chaining purposes.
		 *
		 * @author Yuri Vecchi
		 * @param string $name
		 * @param string $value
		 * @param string $namespace
		 * @return Extended_SimpleXMLElement
		 */
		public function addAttribute($name,$value,$namespace=NULL) {
			if(strpos($name,':') !== false && $namespace === NULL):
				$namespace = explode(':',$name);
				if(isset(self::$namespaces[$namespace[0]])) $namespace = self::$namespaces[$namespace[0]];
				else $namespace = NULL;
			endif;
			parent::addAttribute($name,$value,$namespace);
			return $this;
		}

		/**
		 * Overwritten so we can use namespaces.
		 *
		 * @param string $node
		 * @param string $inner
		 * @param string $namespace
		 * @return SimpleXMLElement
		 */
		public function addChild($node,$inner=NULL,$namespace=NULL) {
			if(strpos($node,':') !== false && $namespace === NULL):
				$namespace = explode(':',$node);
				if(isset(self::$namespaces[$namespace[0]])) $namespace = self::$namespaces[$namespace[0]];
				else $namespace = NULL;
			endif;
			return parent::addChild($node,$inner,$namespace);
		}

	}
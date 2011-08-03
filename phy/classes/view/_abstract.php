<?php

	namespace PHY;

	/**
	 * The default Page class. All Page classs should extend Page.
	 *
	 * @package View
	 * @category View
	 * @author John Mullanaphy
	 * @abstract
	 */
	abstract class View {

		/**
		 * Markup Tag.
		 * 
		 * @var $tag
		 */
		public $tag = NULL;
		/**
		 * Outside input, GET, POST, PUT, or DELETE.
		 *
		 * @var $parameters
		 * @access protected
		 */
		protected $parameters = array();
		/**
		 * Our Template object.
		 *
		 * @var $Template
		 * @access protected
		 */
		protected $Template = array();

		/**
		 * Load a new Page view.
		 *
		 * @param bool $delay If true parsing will be delayed until the __destruct.
		 */
		public function __construct($delay=false) {
			if(method_exists($this,'beforeParameters')) $this->beforeParameters();
			$this->parameters();
			$this->Template = new \PHY\Template;
			if(method_exists($this,'beforeParse')) $this->beforeParse();
			$this->parse();
		}

		/**
		 * If a method doesn't exist then send back a warning.
		 *
		 * @param string $method
		 * @param array $arguments
		 */
		public function __call($method,$arguments) {
			throw new Exception\Method_Does_Not_Exist(__LINE__,$method,$arguments);
		}

		/**
		 * Method for templates and HTML output.
		 */
		abstract public function structure();

		/**
		 * Parses input parameters into $this->parameters.
		 */
		final protected function parameters() {
			$this->parameters = \PHY\Request::toArray();
			$this->parameters['method'] = $_SERVER['REQUEST_METHOD'];
			$this->tag = \PHY\Markup::instance();
		}

		/**
		 * Manually parse a page with this class. Use it when you must surpress the __construct.
		 */
		final protected function parse() {
			if(Headers::mobile()):
				$Reflection = new \ReflectionClass(get_class($this));
				if($Reflection->implementsInterface('\PHY\Interfaces\Page\mobile')) $this->mobile();
				else $this->structure();
			else:
				$this->structure();
			endif;
		}

	}
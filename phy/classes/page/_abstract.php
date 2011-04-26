<?php

	/**
	 * The default Page class. All Page classs should extend Page.
	 *
	 * @package Page
	 * @category Page
	 * @author John Mullanaphy
	 * @abstract
	 */
	abstract class Page {

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
		 * Load a new Page view.
		 *
		 * @param bool $delay If true parsing will be delayed until the __destruct.
		 */
		public function __construct($delay=false) {
			$this->parameters();
			Template::init();
			$this->parse();
			Template::flush();
		}

		/**
		 * If a method doesn't exist then send back a warning.
		 *
		 * @param string $method
		 * @param array $arguments
		 */
		public function __call($method,$arguments) {
			Debug::warning(get_class($this).'::'.$method.'() does not exist.');
		}

		/**
		 * Method for templates and HTML output.
		 */
		abstract public function html();

		/**
		 * Parses input parameters into $this->parameters.
		 */
		final protected function parameters() {
			switch($_SERVER['REQUEST_METHOD']):
				case 'GET':
				case 'HEAD':
					$this->parameters = $_GET;
					break;
				case 'POST':
					$this->parameters = array_merge($_GET,$_POST);
					break;
				case 'PUT':
				case 'DELETE':
					parse_str(file_get_contents('php://input'),$parameters);
					$this->parameters = array_merge($_GET,$_POST,$parameters);
					break;
				default:
					header('HTTP/1.1 501 Not Implemented');
					header('Allow: DELETE, GET, HEAD, POST, PUT',true,501);
					echo 'Unauthorized';
					exit;
			endswitch;
			$this->parameters['method'] = $_SERVER['REQUEST_METHOD'];
			$this->tag = Markup::tag();
		}

		/**
		 * Manually parse a page with this class. Use it when you must surpress the __construct.
		 */
		final protected function parse() {
			if(Headers::mobile()):
				$Reflection = new ReflectionClass(get_class($this));
				if($Reflection->implementsInterface('interface_page_mobile')) $this->mobile();
				else $this->html();
			else:
				$this->html();
			endif;
		}

	}
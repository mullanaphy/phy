<?php

	/**
	 * @package Page_Index
	 * @category Page
	 * @author John Mullanaphy
	 */
	class Page_Index extends Page {

		public function html() {
			Template::title('Welcome');
			Template::section();
			Template::column(.45);
			Template::append($this->welcome());
			Template::column(.45);
			Template::append($this->chained());
		}

		/**
		 * Returns a quick Container that was built with chaining.
		 * 
		 * @return Container
		 */
		public function chained() {
			$Container = new Container;
			$Container->title('Chained')
				->append('This Container was chained together');
			return $Container;
		}

		/**
		 * Returns a quick Welcome.
		 * 
		 * @return Container
		 */
		public function welcome() {
			$Container = new Container;
			$Container->title('Welcome');
			$Container->append('This is just a quick demo index page.');
			return $Container;
		}

	}
<?php

	namespace PHY\Page;

	/**
	 * @package Page_Index
	 * @category Page
	 * @author John Mullanaphy
	 */
	class Index extends Page {

		public function html() {
			$this->Template->title('Welcome');
			$this->Template->section();
			$this->Template->column(.45);
			$this->Template->append($this->welcome());
			$this->Template->column(.45);
			$this->Template->append($this->chained());
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
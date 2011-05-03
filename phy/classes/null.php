<?php

	namespace PHY;

	/**
	 * NULL everywhere! Used for references that need to be NULL.
	 * 
	 * @ignore
	 * @final
	 */
	final class NULL {

		public function __call($key,$parameters) {
			return NULL;
		}

		static public function __callStatic($key,$parameters) {
			return NULL;
		}

		public function __construct() {
			PHY\Debug::warning('You are working with a deleted Object, so a NULL is being returned.');
		}

		public function __get($key) {
			return NULL;
		}

		public function __set($key,$value) {
			return NULL;
		}

		public function __toString() {
			return '';
		}

		public function __unset($key) {
			return NULL;
		}

	}
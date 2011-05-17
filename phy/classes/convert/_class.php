<?php

	namespace PHY;

	/**
	 *
	 * @category Convert
	 * @package Convert
	 * @author John Mullanaphy
	 * @final
	 * @static
	 */
	final class Convert {

		/**
		 * Recursively convert a stdClass into an array.
		 * 
		 * @param stdClass $Class
		 * @return array
		 */
		static public function object_to_array($Class) {
			if(!is_object($Class)) return $Class;
			$Class = (array)$Class;
			foreach($Class as $key => $value) if(is_object($value)) $Class[$key] = self::object_to_array($value);
			return $Class;
		}

		/**
		 * Recursively convert an array into a stdClass.
		 *
		 * @param array $array
		 * @return stdClass
		 */
		static public function array_to_object(array $array) {
			foreach($array as $key => $value) if(is_array($value)) $array[$key] = self::array_to_object($value);
			return (object)$array;
		}

	}
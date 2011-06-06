<?php

	namespace PHY\Markup;
	
	/**
	 * Just a heap used for ranking element tag worth.
	 *
	 * @category Markup
	 * @package Markup\Heap
	 * @author John Mullanaphy
	 * @final
	 * @internal
	 */
	final class Heap extends \SplHeap {

		/**
		 * Compares $row_1 to $row_2
		 *
		 * @param stdClass $row_1
		 * @param stdClass $row_2
		 * @return int
		 */
		public function compare($row_1,$row_2) {
			if($row_1->rank === $row_2->rank) return 0;
			else return $row_1->rank > $row_2->rank?-1:1;
		}

	}
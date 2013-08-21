<?php

    /**
     * PHY
     *
     * LICENSE
     *
     * This source file is subject to the Open Software License (OSL 3.0)
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://opensource.org/licenses/osl-3.0.php
     * If you did not receive a copy of the license and are unable to
     * obtain it through the world-wide-web, please send an email
     * to license@kinopio.net so we can send you a copy immediately.
     *
     */

    namespace PHY;

    /**
     * Debugger class.
     *
     * @package PHY\Debugger
     * @category PHY
     * @copyright Copyright (c) 2011 KinopioNet (http://www.kinopio.net/)
     * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @author John Mullanaphy
     * @todo Rewrite this class, probably break it out some.
     */
    class Debugger
    {

        private $time = null;
        private $memory = 0;

        /**
         * Timer.
         *
         * @staticvar string $time
         * @staticvar string $memory
         * @param bool $reset Resets the timer.
         */
        public function timer($reset = false)
        {
            if ($this->time === null || $reset) {
                $this->time = microtime(true);
                $this->memory = memory_get_usage();
            } else {
                return (string)(round(microtime(true) - $this->time, 5)).' using '.self::parseBytes(memory_get_usage() - $this->memory).' of '.self::parseBytes(memory_get_usage());
            }
        }

        /**
         * Convert bytes into a human readable version.
         *
         * @param int $size
         * @return string
         */
        public static function parseBytes($size = 0)
        {
            $size = (int)$size;
            if (!$size) {
                return 0;
            } else if ($size < 0) {
                $sign = -1;
            } else {
                $sign = 1;
            }
            $size = abs($size);
            $units = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
            return ($sign * (round($size / pow(1024, ($i = floor(log($size, 1024)))), 2))).' '.$units[$i];
        }

    }
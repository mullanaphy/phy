<?php

	if(!function_exists('__exceptions')):

		function __exceptions($exception) {
			echo 'Uncaught exception: ',$exception->getMessage(),PHP_EOL;
		}

		set_exception_handler('__exceptions');
	endif;
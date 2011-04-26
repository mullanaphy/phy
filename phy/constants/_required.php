<?php

	define('DATE_LONG','F jS, Y g:i:sa');
	define('DATE_SHORT','F jS, Y');
	define('DATE_TIMESTAMP','Y-m-d H:i:s');
	define('DATE_TWO_LINES','F jS, Y<\b\r />g:i:sa');
	define('INT_MINUTE',(int)60);
	define('INT_HOUR',(int)(INT_MINUTE * 60));
	define('INT_DAY',(int)(INT_HOUR * 24));
	define('INT_WEEK',(int)(INT_DAY * 7));
	define('INT_MONTH',(int)(INT_DAY * 31));
	define('INT_YEAR',(int)(INT_DAY * 365.25));
<?php

	ob_end_flush();
	header('Content-Length: '.ob_get_length());
	ob_end_flush();
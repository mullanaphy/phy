<?php

	namespace PHY;

	require_once 'phy/_required.php';

	call_user_func(
		function() {
			$view = str_replace(array('/phy','/'),array('','\\'),$_SERVER['REQUEST_URI']);
			if($view&&$view!=='\\'):
				if(class_exists('\PHY\View\\'.$view.'\Controller',false)):
					$_ = '\PHY\View\\'.$view.'\Controller';
					new $_;
				elseif(class_exists('\PHY\View\\'.$view,false)):
					$_ = '\PHY\View\\'.$view;
					new $_;
				else:
					new View\Error;
				endif;
			else:
				new View\Index;
			endif;
		}
	);
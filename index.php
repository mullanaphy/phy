<?php

	# Load up phy.
	require_once 'phy/_required.php';

	call_user_func(
		function() {
			if(isset($_GET['page'])):
				if(class_exists('Page_'.str_replace('-','_',$_GET['page']).'_Controller',true)):
					$_ = 'Page_'.ucfirst(str_replace('-','_',$_GET['page'])).'_Controller';
					new $_;
				elseif(class_exists('Page_'.str_replace('-','_',$_GET['page']),true)):
					$_ = 'Page_'.ucfirst(str_replace('-','_',$_GET['page']));
					new $_;
				else:
					new Page_Error;
					exit;
				endif;
			else:
				new Page_Index;
			endif;
		}
	);
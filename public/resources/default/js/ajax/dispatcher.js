;!function($){$.ajaxDispatcher=function(event,func){var dispatcher='ajax:dispatcher:'+event;if(func)$(document).on(dispatcher,func);return dispatcher;};}(jQuery);
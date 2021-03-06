<?php

	namespace PHY\Markup;
	
	/**
	 * Work with only HTML5 tags and attributes.
	 *
	 * @category Markup
	 * @package Markup\HTML5
	 * @author John Mullanaphy
	 */
	class HTML5 extends \PHY\Markup\_Abstract {

		public $DOCTYPE = '<!DOCTYPE html>',
		$OPENER = '<html lang="en">',
		$CLOSER = '</html>';
		protected $events = array(
			'onabort' => true,
			'onafterprint' => true,
			'onbeforeprint' => true,
			'onbeforeonload' => true,
			'onblur' => true,
			'oncanplay' => true,
			'oncanplaythrough' => true,
			'onchange' => true,
			'onclick' => true,
			'oncontextmenu' => true,
			'ondurationchange' => true,
			'ondblclick' => true,
			'ondrag' => true,
			'ondragend' => true,
			'ondragenter' => true,
			'ondragleave' => true,
			'ondragover' => true,
			'ondragstart' => true,
			'ondrop' => true,
			'onemptied' => true,
			'onended' => true,
			'onerror' => true,
			'onformchange' => true,
			'onforminput' => true,
			'onfocus' => true,
			'onhaschange' => true,
			'oninput' => true,
			'oninvalid' => true,
			'onkeydown' => true,
			'onkeypress' => true,
			'onkeyup' => true,
			'onload' => true,
			'onloadeddata' => true,
			'onloadedmetadata' => true,
			'onloadstart' => true,
			'onmousedown' => true,
			'onmousemove' => true,
			'onmouseout' => true,
			'onmouseover' => true,
			'onmouseup' => true,
			'onmousewheel' => true,
			'onmessage' => true,
			'onoffline' => true,
			'ononline' => true,
			'onpagehide' => true,
			'onpageshow' => true,
			'onpause' => true,
			'onplay' => true,
			'onplaying' => true,
			'onpopstate' => true,
			'onprogress' => true,
			'onredo' => true,
			'onresize' => true,
			'onratechange' => true,
			'onreadystatechange' => true,
			'onscroll' => true,
			'onseeked' => true,
			'onseeking' => true,
			'onselect' => true,
			'onstalled' => true,
			'onstorage' => true,
			'onsubmit' => true,
			'onsuspend' => true,
			'ontimeupdate' => true,
			'onundo' => true,
			'onunload' => true,
			'onvolumechange' => true,
			'onwaiting' => true
			),
		$standard = array(
			'accesskey' => true,
			'class' => true,
			'contenteditable' => array(
				'false',
				'true'
			),
			'contextmenu' => true,
			'dir' => array(
				'ltr',
				'rtl'
			),
			'draggable' => array(
				'auto',
				'false',
				'true'
			),
			'id' => true,
			'irrelevant' => true,
			'item' => true,
			'itemprop' => true,
			'lang' => true,
			'ref' => true,
			'registrationmark' => true,
			'spellcheck' => array(
				'false',
				'true'
			),
			'style' => true,
			'subject' => true,
			'tabindex' => true,
			'template' => true,
			'title' => true
			),
		$tags = array(
			'a' => array(
				'href' => true,
				'hreflang' => true,
				'media' => true,
				'ping' => true,
				'rel' => array(
					'alternate',
					'archives',
					'author',
					'bookmark',
					'contact',
					'external',
					'feed',
					'first',
					'help',
					'icon',
					'index',
					'last',
					'license',
					'next',
					'nofollow',
					'noreferrer',
					'pingback',
					'prefetch',
					'prev',
					'search',
					'stylesheet',
					'sidebar',
					'tag',
					'up'
				),
				'target' => true,
				'type' => true
			),
			'abbr' => true,
			'address' => true,
			'area' => array(
				'alt' => true,
				'coords' => true,
				'href' => true,
				'hreflang' => true,
				'media' => true,
				'ping' => true,
				'rel' => array(
					'alternate',
					'archives',
					'author',
					'bookmark',
					'contact',
					'external',
					'feed',
					'first',
					'help',
					'icon',
					'index',
					'last',
					'license',
					'next',
					'nofollow',
					'noreferrer',
					'pingback',
					'prefetch',
					'prev',
					'search',
					'stylesheet',
					'sidebar',
					'tag',
					'up'
				),
				'shape' => array(
					'rect',
					'rectangle',
					'circ',
					'circle',
					'poly',
					'polygon'
				),
				'target' => true,
				'type' => true
			),
			'article' => array(
				'cite' => true,
				'pubdate' => true
			),
			'aside' => true,
			'audio' => array(
				'autobuffer' => true,
				'autoplay' => true,
				'controls' => true,
				'src' => true
			),
			'b' => true,
			'base' => array(
				'href' => true,
				'target' => true,
			),
			'bdo' => true,
			'blockquote' => array(
				'site' => true
			),
			'body' => true,
			'br' => true,
			'button' => true,
			'canvas' => array(
				'height' => true,
				'width' => true
			),
			'caption' => true,
			'cite' => true,
			'code' => true,
			'col' => array(
				'span' => true
			),
			'colgroup' => array(
				'span' => true
			),
			'command' => array(
				'checked' => true,
				'disabled' => true,
				'icon' => true,
				'label' => true,
				'radiogroup' => true,
				'type' => array(
					'checkbox',
					'command',
					'radio'
				),
			),
			'datalist' => true,
			'dd' => true,
			'del' => array(
				'cite' => true,
				'datetime' => true
			),
			'details' => array(
				'open' => true
			),
			'dialog' => true,
			'dfn' => true,
			'div' => true,
			'dl' => true,
			'dt' => true,
			'em' => true,
			'embed' => array(
				'allowfullscreen' => true,
				'allownetworking' => true,
				'allowscriptaccess' => true,
				'flashvars' => true,
				'height' => true,
				'name' => true,
				'src' => true,
				'swliveconnect' => true,
				'type' => true,
				'width' => true,
				'wmode' => true
			),
			'fieldset' => array(
				'disabled' => true,
				'form' => true,
				'name' => true
			),
			'figcaption' => true,
			'figure' => true,
			'footer' => true,
			'form' => array(
				'accept-charset' => true,
				'action' => true,
				'autocomplete' => array(
					'off',
					'on'
				),
				'enctype' => array(
					'application/x-www-form-urlencoded',
					'multipart/form-data',
					'text/plain'
				),
				'method' => array(
					'delete',
					'get',
					'post',
					'put'
				),
				'name' => true,
				'novalidate' => true,
				'target' => true
			),
			'h1' => true,
			'h2' => true,
			'h3' => true,
			'h4' => true,
			'h5' => true,
			'h6' => true,
			'head' => true,
			'header' => true,
			'hgroup' => true,
			'hr' => true,
			'html' => array(
				'manifest' => true,
				'xmlns' => true
			),
			'i' => true,
			'iframe' => array(
				'allowtransparency' => true,
				'height' => true,
				'name' => true,
				'sandbox' => array(
					'allow-forms',
					'allow-same-origin',
					'allow-scripts'
				),
				'seamless' => true,
				'src' => true,
				'width' => true
			),
			'img' => array(
				'alt' => true,
				'height' => true,
				'ismap' => true,
				'src' => true,
				'usemap' => true,
				'width' => true
			),
			'input' => array(
				'accept' => true,
				'alt' => true,
				'autocomplete' => array(
					'off',
					'on'
				),
				'autofocus' => true,
				'checked' => true,
				'disabled' => true,
				'form' => true,
				'formaction' => true,
				'formenctype' => array(
					'application/x-www-form-urlencoded',
					'multipart/form-data',
					'text/plain'
				),
				'formmethod' => array(
					'delete',
					'get',
					'post',
					'put'
				),
				'formnovalidate' => array(
					'false',
					'true'
				),
				'formtarget' => array(
					'_blank',
					'_parent',
					'_self',
					'_top'
				),
				'height' => true,
				'list' => true,
				'max' => true,
				'maxlength' => true,
				'min' => true,
				'multiple' => true,
				'name' => true,
				'pattern' => true,
				'placeholder' => true,
				'readonly' => true,
				'required' => true,
				'size' => true,
				'src' => true,
				'step' => true,
				'type' => array(
					'button',
					'checkbox',
					'color',
					'date',
					'datetime',
					'datetime-local',
					'email',
					'file',
					'hidden',
					'image',
					'month',
					'number',
					'password',
					'radio',
					'range',
					'reset',
					'search',
					'submit',
					'tel',
					'text',
					'time',
					'url',
					'week'
				),
				'value' => true,
				'width' => true
			),
			'ins' => true,
			'keygen' => array(
				'autofocus' => true,
				'challenge' => true,
				'disabled' => true,
				'form' => true,
				'keytype' => true,
				'name' => true
			),
			'kbd' => true,
			'label' => array(
				'for' => true,
				'form' => true
			),
			'legend' => true,
			'li' => array(
				'value' => true
			),
			'link' => array(
				'href' => true,
				'hreflang' => true,
				'media' => array(
					'all',
					'aural',
					'braille',
					'handheld',
					'print',
					'projection',
					'screen',
					'tty',
					'tv'
				),
				'rel' => array(
					'alternate',
					'archives',
					'author',
					'feed',
					'first',
					'help',
					'icon',
					'index',
					'last',
					'license',
					'next',
					'pingback',
					'prefetch',
					'prev',
					'search',
					'stylesheet',
					'sidebar',
					'tag',
					'up'
				),
				'sizes' => true,
				'type' => array(
					'image/gif',
					'text/css',
					'text/javascript'
				)
			),
			'map' => array(
				'name' => true
			),
			'mark' => true,
			'menu' => array(
				'label' => true,
				'type' => array(
					'context',
					'toolbar',
					'list'
				)
			),
			'meta' => array(
				'charset' => true,
				'content' => true,
				'http-equiv' => array(
					'content-type',
					'expires',
					'refresh',
					'set-cookie'
				),
				'name' => true
			),
			'meter' => array(
				'high' => true,
				'low' => true,
				'max' => true,
				'min' => true,
				'optimum' => true,
				'value' => true
			),
			'nav' => true,
			'noscript' => true,
			'object' => array(
				'data' => true,
				'form' => true,
				'height' => true,
				'name' => true,
				'type' => true,
				'usemap' => true,
				'width' => true
			),
			'ol' => array(
				'reversed' => true,
				'start' => true
			),
			'optgroup' => array(
				'disabled' => true,
				'label' => true
			),
			'option' => array(
				'disabled' => true,
				'label' => true,
				'selected' => true,
				'value' => true,
			),
			'output' => array(
				'for' => true,
				'form' => true,
				'name' => true
			),
			'p' => true,
			'param' => array(
				'name' => true,
				'value' => true
			),
			'pre' => true,
			'progress' => array(
				'max' => true,
				'value' => true
			),
			'q' => array(
				'cite' => true
			),
			'rp' => true,
			'rt' => true,
			'ruby' => true,
			'samp' => true,
			'script' => array(
				'async' => true,
				'charset' => true,
				'defer' => true,
				'src' => true,
				'type' => array(
					'application/ecmascript',
					'application/javascript',
					'text/ecmascript',
					'text/javascript',
					'text/vbscript'
				)
			),
			'section' => array(
				'cite' => true
			),
			'select' => array(
				'autofocus' => true,
				'disabled' => true,
				'form' => true,
				'multiple' => true,
				'name' => true,
				'size' => true
			),
			'small' => true,
			'source' => array(
				'media' => true,
				'src' => true,
				'type' => true
			),
			'span' => true,
			'strong' => true,
			'style' => array(
				'media' => array(
					'all',
					'aural',
					'braille',
					'handheld',
					'print',
					'projection',
					'screen',
					'tty',
					'tv'
				),
				'scoped' => true,
				'type' => 'text/css'
			),
			'summary' => true,
			'sub' => true,
			'sup' => true,
			'table' => array(
				'summary' => true
			),
			'tbody' => true,
			'td' => array(
				'colspan' => true,
				'headers' => true,
				'rowspan' => true
			),
			'textarea' => array(
				'autofocus' => true,
				'cols' => true,
				'disabled' => true,
				'form' => true,
				'maxlength' => true,
				'name' => true,
				'placeholder' => true,
				'readonly' => true,
				'required' => true,
				'rows' => true,
				'wrap' => array(
					'hard',
					'soft'
				),
			),
			'tfoot' => true,
			'th' => array(
				'colspan' => true,
				'headers' => true,
				'rowspan' => true,
				'scope' => array(
					'col',
					'colgroup',
					'row',
					'rowgroup'
				)
			),
			'thead' => true,
			'time' => array(
				'datetime' => true
			),
			'title' => true,
			'tr' => true,
			'ul' => true,
			'var' => true,
			'video' => array(
				'autobuffer' => true,
				'autoplay' => true,
				'controls' => true,
				'height' => true,
				'loop' => true,
				'src' => true,
				'width' => true
			)
			),
		$voids = array(
			'area',
			'base',
			'br',
			'col',
			'command',
			'embed',
			'hr',
			'img',
			'input',
			'keygen',
			'link',
			'meta',
			'param',
			'source'
		);

	}
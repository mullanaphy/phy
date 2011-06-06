<?php

	namespace PHY\Markup;
	
	/**
	 * Work with only HTML3.2 tags and attributes. Works well for email flyers.
	 *
	 * @category Markup
	 * @package Markup\HTML3
	 * @author John Mullanaphy
	 */
	class HTML3 extends \PHY\Markup\_Abstract {

		public $DOCTYPE = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">',
		$OPENER = '<html>',
		$CLOSER = '</html>';
		protected $standard = array(
			'class' => true,
			'id' => true,
			'style' => true,
			'tabindex' => true,
			'title' => true
			),
		$tags = array(
			'address' => true,
			'applet' => array(
				'align' => array(
					'bottom',
					'left',
					'middle',
					'right',
					'top'
				),
				'alt' => true,
				'code' => true,
				'codebase' => true,
				'height' => true,
				'hspace' => true,
				'name' => true,
				'vspace' => true,
				'width' => true
			),
			'area' => array(
				'alt' => true,
				'coords' => true,
				'href' => true,
				'nohref' => true,
				'shape' => array(
					'circle',
					'poly',
					'rect'
				)
			),
			'a' => array(
				'href' => true,
				'name' => true,
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
				'rev' => true,
				'target' => array(
					'_blank',
					'_parent',
					'_self',
					'_top'
				)
			),
			'base' => array(
				'href' => true
			),
			'basefont' => array(
				'size' => true
			),
			'big' => true,
			'blockquote' => true,
			'body' => array(
				'alink' => true,
				'background' => true,
				'bgcolor' => true,
				'link' => true,
				'text' => true,
				'vlink' => true
			),
			'br' => true,
			'b' => true,
			'caption' => array(
				'align' => array(
					'bottom',
					'top'
				)
			),
			'center' => true,
			'cite' => true,
			'code' => true,
			'dd' => true,
			'dfn' => true,
			'dir' => array(
				'compact' => true
			),
			'div' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'dl' => array(
				'compact' => true
			),
			'dt' => true,
			'em' => true,
			'font' => array(
				'color' => true,
				'size' => true
			),
			'form' => array(
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
				'target' => array(
					'_blank',
					'_parent',
					'_self',
					'_top'
				)
			),
			'h1' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'h2' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'h3' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'h4' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'h5' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'h6' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'head' => true,
			'hr' => array(
				'align' => array(
					'center',
					'left',
					'right'
				),
				'noshade' => true,
				'size' => true,
				'width' => true
			),
			'html' => array(
				'version' => true
			),
			'img' => array(
				'align' => array(
					'bottom',
					'left',
					'middle',
					'right',
					'top'
				),
				'alt' => true,
				'border' => true,
				'height' => true,
				'hspace' => true,
				'ismap' => true,
				'src' => true,
				'usemap' => true,
				'vspace' => true,
				'width' => true
			),
			'input' => array(
				'align' => array(
					'bottom',
					'left',
					'middle',
					'right',
					'top'
				),
				'checked' => true,
				'maxlength' => true,
				'name' => true,
				'size' => true,
				'src' => true,
				'type' => array(
					'checkbox',
					'file',
					'hidden',
					'image',
					'password',
					'radio',
					'reset',
					'submit',
					'text'
				),
				'value' => true
			),
			'isindex' => array(
				'prompt' => true
			),
			'i' => true,
			'kbd' => true,
			'li' => array(
				'type' => array(
					1,
					'a',
					'A',
					'circle',
					'disc',
					'i',
					'I',
					'square'
				),
				'value' => true
			),
			'link' => array(
				'href' => true,
				'rel' => true,
				'rev' => true,
				'title' => true
			),
			'map' => array(
				'string' => true
			),
			'menu' => array(
				'compact' => true
			),
			'meta' => array(
				'content' => true,
				'http-equiv' => true,
				'name' => true
			),
			'ol' => array(
				'compact' => true,
				'start' => true,
				'type' => array(
					1,
					'a',
					'A',
					'i',
					'I'
				)
			),
			'option' => array(
				'selected' => true,
				'value' => true
			),
			'param' => array(
				'name' => true,
				'value' => true
			),
			'pre' => array(
				'width' => true
			),
			'p' => array(
				'align' => array(
					'center',
					'left',
					'right'
				)
			),
			'samp' => true,
			'script' => true,
			'select' => array(
				'multiple' => true,
				'name' => true,
				'size' => true
			),
			'small' => true,
			'strike' => true,
			'strong' => true,
			'style' => array(
				'type' => true
			),
			'sub' => true,
			'sup' => true,
			'table' => array(
				'align' => array(
					'center',
					'left',
					'right'
				),
				'border' => true,
				'cellpadding' => true,
				'cellspacing' => true,
				'width' => true
			),
			'td' => array(
				'align' => array(
					'center',
					'left',
					'right'
				),
				'colspan' => true,
				'height' => true,
				'nowrap' => true,
				'rowspan' => true,
				'valign' => array(
					'bottom',
					'middle',
					'top'
				),
				'width' => true
			),
			'textarea' => array(
				'cols' => true,
				'name' => true,
				'rows' => true
			),
			'th' => array(
				'align' => array(
					'center',
					'left',
					'right'
				),
				'colspan' => true,
				'height' => true,
				'nowrap' => true,
				'rowspan' => true,
				'valign' => array(
					'bottom',
					'middle',
					'top'
				),
				'width' => true
			),
			'title' => true,
			'tr' => array(
				'align' => array(
					'center',
					'left',
					'right'
				),
				'valign' => array(
					'bottom',
					'middle',
					'top'
				)
			),
			'tt' => true,
			'ul' => array(
				'compact' => true,
				'type' => array(
					'circle' => true,
					'disc' => true,
					'square' => true
				)
			),
			'u' => true,
			'var' => true
			),
		$voids = array(
			'area',
			'base',
			'basefont',
			'br',
			'hr',
			'img',
			'input',
			'isindex',
			'link',
			'meta',
			'param'
		);

	}
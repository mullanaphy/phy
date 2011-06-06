<?php

	namespace PHY\Container;

	/**
	 * Create an item list based container.
	 *
	 * @category Container
	 * @package Container
	 * @author John Mullanaphy
	 */
	class Items extends \PHY\Container\_Abstract {

		protected $content = 'ul',
		$holder = 'article',
		$item = 'li';

		public function heading($content=false,$tag='h3',$attributes=NULL) {
			if(is_array($content)) $content = join('',$content);
			if(!$content):
				return false;
			elseif(!$this->item || (is_object($content) && in_array($content->tag,self::$ITEMS))):
				$this->container['content'][] = $content;
			else:
				if(is_array($tag)):
					$attributes = $tag;
					$tag = 'h3';
				elseif(!preg_match('#h[0-6]#i',$tag)):
					$tag = 'h3';
				endif;
				if(is_array($attributes)):
					if(isset($attributes['class'])) $attributes['class'] .= ' '.$tag;
					else $attributes['class'] = ' '.$tag;
				else:
					$attributes = array('class' => $tag);
				endif;
				$this->container['content'][] = $this->tag->li($content,$attributes);
			endif;
			return $this;
		}

	}
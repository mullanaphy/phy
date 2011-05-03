<?php

	namespace PHY\Container;

	class Table extends \PHY\Container\_Abstract {

		protected $content = 'table',
		$holder = 'article',
		$item = 'td';
		private $columns = array(
			'count' => 1,
			'dimensions' => array(),
			'iterator' => 0
		);

		# Add content.

		public function append($content=false,$attributes=false) {
			if(!is_array($content)) $this->container['content'][count($this->container['content']) - 1]['content'][] = array($content,$attributes);
			elseif(count($content)) foreach($content as $element) $this->container['content'][count($this->container['content']) - 1]['content'][] = $element;
			else $this->container['content'][count($this->container['content']) - 1]['content'][] = $content;
			if($attributes) $this->container['content'][count($this->container['content']) - 1]['attributes'] = $attributes;
			return $this;
		}

		# Set the column count.

		public function columns() {
			$parameters = func_get_args();
			if(isset($parameters[0]) && is_array($parameters[0])) $parameters = $parameters[0];
			if(is_array($parameters)) $this->columns['dimensions'] = $parameters;
			return $this;
		}

		# Insert a new heading.

		public function heading() {
			if(!isset($this->container['content'][count($this->container['content']) - 1]) || count($this->container['content'][count($this->container['content']) - 1])) $this->container['content'][] = array(
					'type' => 'th',
					'content' => array()
				);
			return $this;
		}

		# Prepend content.

		public function prepend($content=false,$attributes=false) {
			if(!is_array($content)) array_unshift($this->container['content'][count($this->container['content']) - 1]['content'][],array($content,$attributes));
			elseif(count($content)) foreach($content as $element) array_unshift($this->container['content'][count($this->container['content']) - 1]['content'][],$element);
			else array_unshift($this->container['content'][count($this->container['content']) - 1]['content'][],$content);
			if($attributes) $this->container['content'][count($this->container['content']) - 1]['attributes'] = $attributes;
			return $this;
		}

		# Insert a new row.

		public function row($attributes=NULL) {
			if(!isset($this->container['content'][count($this->container['content']) - 1]) || count($this->container['content'][count($this->container['content']) - 1])) $this->container['content'][] = array(
					'type' => 'td',
					'content' => array(),
					'attributes' => $attributes
				);
			return $this;
		}

		# Overwrite the default _content() method.

		protected function _content() {
			$content = $this->tag->table;
			if(isset($this->columns['dimensions']) && is_array($this->columns['dimensions'])):
				$colgroup = $this->tag->colgroup;
				foreach($this->columns['dimensions'] as $column) $colgroup->append($this->tag->col(array('style' => 'width:'.(($column >= 1)?(int)$column.'px':(int)($column * 100).'%'))));
				$content->append($colgroup);
			endif;
			$content->attributes(array('class' => 'content'));
			if(count($this->container['content'])):
				$this->columns['count'] = count($this->columns['dimensions']);
				foreach($this->container['content'] as $row):
					$this->columns['iterator'] = 0;
					$tr = $this->tag->tr;
					if(isset($row['attributes']) && is_array($row['attributes'])) $tr->attributes($row['attributes']);
					foreach($row['content'] as $column):
						if(is_array($column) && isset($column['content'],$column['attributes'])):
							$attributes = $column['attributes'];
							$column = $column['content'];
						else:
							$attributes = NULL;
						endif;
						if($this->columns['iterator'] > $this->columns['count']) break;
						if(is_array($column)):
							if(isset($column[1]) && is_numeric($column[1])):
								$colspan = array('colspan' => $column[1]);
								$this->columns['iterator'] += (int)$column[1];
							else:
								$colspan = isset($column[1])?$column[1]:false;
								++$this->columns['iterator'];
							endif;
							$tr->append(
								$this->tag->$row['type']($column[0],$colspan)
							);
						else:
							$tr->append($this->tag->$row['type']($column));
							++$this->columns['iterator'];
						endif;
					endforeach;
					while($this->columns['iterator']++ < $this->columns['count']) $tr->append($this->tag->td);
					$content->append($tr);
				endforeach;
			endif;
			return $content;
		}

	}
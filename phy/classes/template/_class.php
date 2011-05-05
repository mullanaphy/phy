<?php

	namespace PHY;

	/**
	 * @package Template
	 * @category Frontend
	 * @author John Mullanaphy
	 */
	final class Template {

		private $generated = false,
		$css = array(
			'added' => array(),
			'core' => array(),
			'modules' => array(),
			'print' => array()
			),
		$js = array(
			'added' => array(),
			'core' => array(),
			'modules' => array(),
			'footer' => array()
			),
		$keywords = array(
			'added' => array(),
			'invalid' => array('panda')
			),
		$meta = array(),
		$rss = array(
			'core' => array(),
			'added' => array()
			),
		$scripts = array(),
		$sections = array(),
		$show = array(
			'footer' => true,
			'header' => true,
			'theme' => true,
			'title' => true
			),
		$tag = NULL,
		$title = '';

		/**
		 * You can only initiate one Template object.
		 * 
		 * Will call $this->init(); on initiation.
		 */
		public function __construct() {

			# Meta data.
			$this->meta = array(
				'header_content_type' => array(
					'http-equiv' => 'content-type',
					'content' => 'text/html;charset=utf-8'
				),
				'name_cache_control' => array(
					'name' => 'cache-control',
					'content' => 'public'
				),
				'name_expires' => array(
					'name' => 'expires',
					'content' => date('Y-m-d@H:i:s T',strtotime('1 week'))
				),
				'name_last_modified' => array(
					'name' => 'last-modified',
					'content' => date('Y-m-d@H:i:s T')
				),
				'name_description' => array(
					'name' => 'description',
					'content' => \PHY\Core::config('site/description')
				),
				'name_keywords' => array(
					'name' => 'keywords',
					'content' => \PHY\Core::config('site/keywords')
				),
				'name_author' => array(
					'name' => 'author',
					'content' => \PHY\Core::config('site/name')
				),
				'name_contact' => array(
					'name' => 'contact',
					'content' => \PHY\Core::config('site/email')
				),
				'name_robots' => array(
					'name' => 'robots',
					'content' => 'noodp'
				),
				'name_blogcatalog' => array(
					'name' => 'blogcatalog',
					'content' => '9BC9576594'
				),
				'og:site_name' => array(
					'property' => 'og:site_name',
					'content' => \PHY\Core::config('site/name')
				)
			);

			# HTML Version to use.
			switch(true):
				case (Headers::ie6()):
					$this->tag = new \PHY\Markup('HTML4');
					break;
				case (Headers::bot()):
					$this->tag = new \PHY\Markup('HTML4');
					break;
				case (Headers::mobile()):
					$this->meta[] = array(
						'name' => 'viewport',
						'content' => 'user-scalable=no,width=device-width,minimum-scale=1.0,maximum-scale=1.0'
					);
				default:
					$this->tag = new \PHY\Markup;
			endswitch;

			$this->js['core'] = \PHY\Core::config('files/'.\PHY\Core::config('site/use').'/js');
			$this->css['core'] = \PHY\Core::config('files/'.\PHY\Core::config('site/use').'/css');

			echo $this->tag->DOCTYPE,
			$this->tag->OPENER;

			flush();
			if(ob_list_handlers()) ob_flush();
		}

		/**
		 * Will call $this->generate(); on object destruction. 
		 */
		public function __destruct() {
			if(!$this->generated) $this->generate();
		}

		/**
		 * returns all the content.
		 * 
		 * @return string
		 */
		public function __toString() {
			return $this->head().$this->body().$this->tag->CLOSER;
		}

		/**
		 * Echos out all generated HTML.
		 */
		public function generate() {
			$this->generated = true;
			echo $this->head();
			flush();
			if(ob_list_handlers()) ob_flush();
			echo $this->body().$this->tag->CLOSER;
		}

		/**
		 * Append content into the current Section/Column
		 * 
		 * @param mixed $content
		 * @param array $attributes
		 * @return bool 
		 */
		public function append($content=false,$attributes=NULL) {
			if($content === false) return false;
			if(is_numeric($attributes)) $attributes = array('style' => 'width:'.(($attributes >= 1)
						?(int)$attributes.'px'
						:(int)($attributes * 100).'%'));
			else $attributes = $this->_attributes($attributes);
			if(!count($this->sections)) $this->section('normal');
			elseif(!count($this->sections[count($this->sections) - 1]['columns'])) $this->column();
			$this->sections[count($this->sections) - 1]['columns'][count($this->sections[count($this->sections) - 1]['columns']) - 1]['content'][] = array(
				'attributes' => $attributes,
				'content' => $content
			);
			return true;
		}

		/**
		 * Start a new column.
		 * 
		 * If you send an array of attributes, 'width' => '' will be appended to
		 * style in the same way that sending just a decimal or integer would.
		 * 
		 * @param int|float|array $attributes
		 */
		public function column($attributes=NULL) {
			if(is_numeric($attributes)) $attributes = array('style' => 'width:'.(($attributes >= 1)
						?(int)$attributes.'px'
						:(int)($attributes * 100).'%'));
			else $attributes = $this->_attributes($attributes);
			if(isset($attributes['width'])):
				if(isset($attributes['style'])) $attributes['style'] .= 'width:'.(($attributes['width'] >= 1)
							?(int)$attributes['width'].'px'
							:(int)($attributes['width'] * 100).'%').';';
				else $attributes['style'] = 'width:'.(($attributes['width'] >= 1)
							?(int)$attributes['width'].'px'
							:(int)($attributes['width'] * 100).'%').';';
				unset($attributes['width']);
			endif;
			if(!count($this->sections)) $this->section('normal');
			$this->sections[count($this->sections) - 1]['columns'][] = array(
				'attributes' => $attributes,
				'containers' => array()
			);
		}

		/**
		 * Create a heading section.
		 * 
		 * @param mixed $content
		 * @param string $tag Type of container tag to use.
		 * @param array $attributes
		 * @return bool 
		 */
		public function heading($content=NULL,$tag='h2',$attributes=NULL) {
			if($content === NULL) return false;
			if(is_array($tag)):
				$attributes = $tag;
				$tag = 'h2';
			elseif(!preg_match('#h[0-6]#i',$tag)):
				$tag = 'h2';
			endif;
			if(!is_array($attributes)) $attributes = array();
			if(isset($attributes['class'])) $attributes['class'] = 'heading '.$attributes['class'];
			else $attributes['class'] = 'heading';
			if(!count($this->sections)) $this->section('normal');
			elseif(!count($this->sections[count($this->sections) - 1]['columns'])) $this->column();
			$this->sections[count($this->sections) - 1]['heading'] = $this->tag->$tag($content,$attributes);
			return true;
		}

		/**
		 * Prepend content into the current Section/Column
		 * 
		 * @param mixed $content
		 * @param array $attributes
		 * @return bool 
		 */
		public function prepend($content=false,$attributes=NULL) {
			if($content === false) return false;
			if(is_numeric($attributes)) $attributes = array('style' => 'width:'.(($attributes >= 1)
						?(int)$attributes.'px'
						:(int)($attributes * 100).'%'));
			else $attributes = $this->_attributes($attributes);
			array_unshift(
				$this->sections[count($this->sections) - 1]['columns'][count($this->sections[count($this->sections) - 1]['columns']) - 1]['content'],array(
				'attributes' => $attributes,
				'content' => $content
				)
			);
			return true;
		}

		/**
		 * Add javascript to be run on its own.
		 * 
		 * @param type $content
		 * @param type $attributes
		 * @return array If no content is sent it returns back current scripts.
		 */
		public function script($content=NULL,$attributes=NULL) {
			if($content === NULL) return $this->scripts;

			# Make sure a type is set.
			if(!is_array($attributes)) $attributes = array('type' => 'text/javascript');
			elseif(!isset($attributes['type'])) $attributes['type'] = 'text/javascript';

			# Store it.
			$this->scripts[] = $this->tag->script($content,$attributes);
		}

		/**
		 * Start a new section on our page.
		 * 
		 * IF $attributes is a bool then $expand = $attributes.
		 * 
		 * @param type $type Class name of our section.
		 * @param array $attributes Attributes for the section or a bool.
		 * @param bool $expand If the section should expand the whole viewport.
		 */
		public function section($type='normal',$attributes=NULL,$expand=NULL) {
			if(is_bool($attributes)):
				$expanded = !!$attributes;
				$attributes = $this->_attributes($expand);
			else:
				$expanded = false;
				$attributes = $this->_attributes($attributes);
			endif;
			if(isset($attributes['class'])) $attributes['class'] = $type.' '.$attributes['class'];
			else $attributes['class'] = $type;
			$this->sections[] = array(
				'attributes' => $attributes,
				'columns' => array(),
				'expanded' => $expanded,
				'heading' => NULL
			);
		}

		/**
		 * Set|Get CSS.
		 * 
		 * If no parameters are set then this method will return all current
		 * CSS files that have been set.
		 * 
		 * @param string,... $css
		 * @return mixed
		 */
		public function css($css=NULL) {
			if($css === NULL) return array_merge($this->css['added'],$this->css['modules']);
			foreach(func_get_args() as $css):
				if(is_array($css)):
					foreach($css as $file):
						if(preg_match('#print\.css|print/#',$file)) $this->css['print'][] = $file;
						else $this->css['added'][] = $file;
					endforeach;
				else:
					if(preg_match('#print\.css|print/#',$css)) $this->css['print'][] = $css;
					else $this->css['added'][] = $css;
				endif;
			endforeach;
			$this->css['added'] = array_unique($this->css['added']);
			$this->css['print'] = array_unique($this->css['print']);
			return true;
		}

		/**
		 * Set|Get meta description.
		 * 
		 * If called without a parameter then this will return the currently set
		 * meta description.
		 * 
		 * @param string $description
		 * @return mixed
		 */
		public function description($description=NULL) {
			if($description === NULL) return $this->meta['name_description']['content'];
			$this->meta['name_description']['content'] = $description;
		}

		/**
		 * Set|Get CSS and JS files.
		 * 
		 * If no parameters are set then this method will return all current
		 * files that have been set (CSS and JS).
		 * 
		 * @param string,... $file
		 * @return mixed
		 */
		public function files($files=NULL) {
			if($files === NULL) return array_merge($this->css['added'],$this->css['modules'],$this->js['added'],$this->js['modules']);
			foreach(func_get_args() as $files):
				if(is_array($files)):
					foreach($files as $file):
						if(substr($file,-4) === '.css') $this->css['modules'][] = $file;
						elseif(substr($file,-3) === '.js') $this->js['modules'][] = $file;
					endforeach;
				else:
					if(substr($files,-4) === '.css') $this->css['modules'][] = $files;
					elseif(substr($files,-3) === '.js') $this->js['modules'][] = $files;
				endif;
			endforeach;
			$this->css['modules'] = array_unique($this->css['modules']);
			$this->js['modules'] = array_unique($this->js['modules']);
			return true;
		}

		/**
		 * Turn options off.
		 * 
		 * Can hide:
		 * 	'title'
		 * 	'footer'
		 * 	'header'
		 * 	'theme'
		 * 
		 * If no parameters are set then it will return currently hidden.
		 * 
		 * @param $hide,... Option to hide.
		 * @return mixed
		 */
		public function hide() {
			if(!count(func_get_args())):
				$hidden = array();
				foreach($this->show as $option => $show) if(!$show) $hidden[] = $option;
				return $hidden;
			endif;
			foreach(func_get_args() as $hide) if(isset($this->show[$hide])) $this->show[$hide] = false;
		}

		/**
		 * Set|Get JS.
		 * 
		 * If no parameters are set then this method will return all current
		 * JS files that have been set.
		 * 
		 * @param string,... $js
		 * @return mixed
		 */
		public function js($js=NULL) {
			if($js === NULL) return array_merge($this->js['added'],$this->js['modules']);
			foreach(func_get_args() as $js):
				if(is_array($js)) foreach($js as $file) $this->js['added'][] = $file;
				else $this->js['added'][] = $js;
			endforeach;
			$this->js['added'] = array_unique($this->js['added']);
			return true;
		}

		/**
		 * Set|Get meta keywords.
		 * 
		 * If called without a parameter then this will return the currently set
		 * meta keyword.
		 * 
		 * @param string,... $keyword
		 * @return mixed
		 */
		public function keywords() {
			if(!count(func_get_args())) return $this->meta['name_keywords']['content'];
			$this->meta['name_keywords']['content'] = join(', ',func_get_args());
		}

		/**
		 * Set|Get a meta tag.
		 * 
		 * If called without a parameter then this will return the currently set
		 * meta tags.
		 * 
		 * @param array $attributes Attributes of the new meta tag.
		 * @return mixed
		 */
		public function meta(array $attributes=NULL) {
			if($attributes === NULL) return $this->meta;
			if($attributes) $this->meta[] = $meta;
		}

		/**
		 * Set|Get RSS.
		 * 
		 * If no parameters are set then this method will return all current
		 * RSS files that have been set.
		 * 
		 * @param string,... $rss
		 * @return mixed
		 */
		public function rss($rss=NULL) {
			if($rss === NULL) return $this->rss['added'];
			foreach(func_get_args() as $rss):
				if(is_array($rss)) foreach($rss as $file) $this->rss['added'][] = $file;
				else $this->rss['added'][] = $rss;
			endforeach;
			$this->rss['added'] = array_unique($this->rss['added']);
		}

		/**
		 * Turn options off.
		 * 
		 * Can show:
		 * 	'title'
		 * 	'footer'
		 * 	'header'
		 * 	'theme'
		 * 
		 * If no parameters are set then it will return currently shown.
		 * 
		 * @param $show,... Option to show.
		 * @return mixed
		 */
		public function show() {
			if(!count(func_get_args())):
				$shown = array();
				foreach($this->show as $option => $show) if($show) $shown[] = $option;
				return $shown;
			endif;
			foreach(func_get_args() as $show) if(isset($this->show[$show])) $this->show[$show] = false;
		}

		/**
		 * Set|Get meta title.
		 * 
		 * If called without a parameter then this will return the currently set
		 * meta title.
		 * 
		 * @param string $title
		 * @return mixed
		 */
		public function title($title=false) {
			if($title) $this->title = $title;
			return $this->title;
		}

		/**
		 * Return a Markup object containing all of our body content.
		 * 
		 * @return Markup_Abstract
		 */
		protected function body() {
			$body = $this->tag->body;

			# Process the header.
			if($this->show['header']) $body->append($this->header());

			# Process the actual page.
			$i = false;
			foreach($this->sections as $group):
				if(!$i):
					$i = true;
					if(isset($group['attributes']['class'])) $group['attributes']['class'] = 'first '.$group['attributes']['class'];
					else $group['attributes']['class'] = 'first';
				endif;
				if(isset($group['attributes']['class'])) $group['attributes']['class'] = 'section '.$group['attributes']['class'];
				else $group['attributes']['class'] = 'section';
				$section = $this->tag->div;
				$section->attributes($group['attributes']);
				$holder = $this->tag->div;
				if(!$group['expanded']) $holder->attributes(array('class' => 'holder'));
				foreach($group['columns'] as $col):
					$column = $this->tag->div;
					if(isset($col['attributes']['class'])) $col['attributes']['class'] = 'column '.$col['attributes']['class'];
					else $col['attributes']['class'] = 'column';
					$column->attributes($col['attributes']);
					if(isset($col['content']) && is_array($col['content'])) foreach($col['content'] as $container) $column->append($container);
					$holder->append($column);
				endforeach;
				$section->append($holder);
				if($group['heading'] !== NULL) $section->prepend($group['heading']);
				$body->append($section);
			endforeach;

			# Process the footer.
			if($this->show['footer']) $body->append($this->footer());
			if(!\PHY\Core::config('site/production')):
				foreach($this->js['footer'] as $js) $body->append(
						$this->tag->script(
							NULL,array(
							'src' => (substr($js,0,4) === 'http'
								?$js
								:'/js/'.$js),
							'type' => 'text/javascript'
							)
						)
					);
			else:
				if(!in_array(USER_BROWSER,$this->browsers['bots'] + $this->browsers['text'])):
					$footer = array();
					foreach($this->js['footer'] as $js):
						if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $body->append(
								$this->tag->script(
									NULL,array(
									'src' => $js,
									'type' => 'text/javascript'
									)
								)
							);
						elseif(is_file(BASE_PATH.'js/'.$js)) $footer[] = $js;
					endforeach;
					if(!is_file(BASE_PATH.'js/cached/footer.'.md5(join('',$footer)).'.js')):
						$files_content = NULL;
						foreach($footer as $js):
							$FILE = fopen(BASE_PATH.'js/'.$js,'r');
							$files_content .= '/* '.$js.' */'."\n".fread($FILE,filesize(BASE_PATH.'js/'.$js))."\n";
							fclose($FILE);
						endforeach;
						if(strlen($files_content) > 0):
							$FILE = fopen(BASE_PATH.'js/cached/footer.'.md5(join('',$footer)).'.js','w');
							fwrite($FILE,MinifyJS::minify($files_content));
							fclose($FILE);
						endif;
					endif;
					$body->append(
						$this->tag->script(
							NULL,array(
							'src' => '/scripts/cached/footer.'.md5(join('',$this->js['footer'])).'.js',
							'type' => 'text/javascript'
							)
						)
					);
				endif;
				$body->append(
					$this->tag->script(
						'var gaJsHost=((\'https:\'==document.location.protocol)?\'https://ssl.\':\'http://www.\');document.write(unescape("%3Cscript src=\'"+gaJsHost+"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));',array('type' => 'text/javascript')
					)
				);
				$body->append(
					$this->tag->script(
						'try{var pageTracker=_gat._getTracker(\'UA-2763315-2\');pageTracker._trackPageview();}catch(e){}',array('type' => 'text/javascript')
					)
				);
			endif;
			$body->append(
				$this->tag->script(
					'try{if(console)console.log(\'Generation: '.Debug::timer().'; Elements: '.\PHY\Markup::elements().'; Server: '.$_SERVER['SERVER_ADDR'].'\');}catch(e){};',array('type' => 'text/javascript')
				)
			);
			return $body;
		}

		/**
		 * Internal function for generating all the files and merging if we are
		 * on a production server.
		 * 
		 * @access private
		 * @internal
		 * @return mixed
		 */
		private function _files() {
			$files = array();

			# Devo and Beta servers. We will not combine files on these servers.
			if(in_array(USER_BROWSER,array('ie','ie6')) || !\PHY\Core::config('site/production')):
				foreach(array_merge($this->css['core'],$this->css['added'],$this->css['modules']) as $css) $files[] = $this->tag->link(
							array(
								'href' => '/css/'.$css,
								'rel' => 'stylesheet',
								'type' => 'text/css'
							)
					);

				# Theme.
				/* Goes here */

				# Print CSS.
				if($this->css['print']) foreach($this->css['print'] as $css) $files[] = $this->tag->link(
								array(
									'href' => '/css/'.$css,
									'media' => 'print',
									'rel' => 'stylesheet',
									'type' => 'text/css'
								)
						);

				foreach(array_merge($this->js['core'],$this->js['added'],$this->js['modules']) as $js) $files[] = $this->tag->script(
							NULL,array(
							'src' => (substr($js,0,4) === 'http'
								?$js
								:'/js/'.$js),
							'type' => 'text/javascript'
							)
					);

			# Live servers, we combine the files to make less requests per page.
			elseif(!Headers::bot()):
				# Core CSS.
				if(!is_file(BASE_PATH.'public/css/cached/core.'.md5(join('',$this->css['core'])).'.css')):
					$files_content = NULL;
					foreach($this->css['core'] as $css):
						if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://'):
							$files[] = $this->tag->link(
									array(
										'href' => $css,
										'rel' => 'stylesheet',
										'type' => 'text/css'
									)
							);
						elseif(is_file(BASE_PATH.'public/css/'.$css)):
							$FILE = fopen(BASE_PATH.'public/css/'.$css,'r');
							$files_content .= '/* '.$css.' */'."\n".fread($FILE,filesize(BASE_PATH.'public/css/'.$css))."\n";
							fclose($FILE);
						endif;
					endforeach;
					if(strlen($files_content) > 0):
						$FILE = fopen(BASE_PATH.'public/css/cached/core.'.md5(join('',$this->css['core'])).'.css','w');
						fwrite($FILE,MinifyCSS::minify($files_content));
						fclose($FILE);
					endif;
				endif;
				$files[] = $this->tag->link(
						array(
							'href' => '/css/cached/core.'.md5(join('',$this->css['core'])).'.css',
							'rel' => 'stylesheet',
							'type' => 'text/css'
						)
				);
				# Added CSS.
				if(count($this->css['added'])):
					if(!is_file(BASE_PATH.'public/css/cached/hash.'.md5(join('',$this->css['added'])).'.css')):
						$files_content = NULL;
						foreach($this->css['added'] as $css):
							if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://'):
								$files[] = $this->tag->link(
										array(
											'href' => $css,
											'rel' => 'stylesheet',
											'type' => 'text/css'
										)
								);
							elseif(is_file(BASE_PATH.'public/css/'.$css)):
								$FILE = fopen(BASE_PATH.'public/css/'.$css,'r');
								$files_content .= '/* '.$css.' */'."\n".fread($FILE,filesize(BASE_PATH.'public/css/'.$css))."\n";
								fclose($FILE);
							endif;
						endforeach;
						if(strlen($files_content) > 0):
							$FILE = fopen(BASE_PATH.'public/css/cached/hash.'.md5(join('',$this->css['added'])).'.css','w');
							fwrite($FILE,MinifyCSS::minify($files_content));
							fclose($FILE);
						endif;
					endif;
					$files[] = $this->tag->link(
							array(
								'href' => '/css/cached/hash.'.md5(join('',$this->css['added'])).'.css',
								'rel' => 'stylesheet',
								'type' => 'text/css'
							)
					);
				endif;

				# Modular CSS.
				if(count($this->css['modules'])):
					if(!is_file(BASE_PATH.'public/css/cached/modules.'.md5(join('',$this->css['modules'])).'.css')):
						$files_content = NULL;
						foreach($this->css['modules'] as $css):
							if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://'):
								$files[] = $this->tag->link(
										array(
											'href' => $css,
											'rel' => 'stylesheet',
											'type' => 'text/css'
										)
								);
							elseif(is_file(BASE_PATH.'public/css/'.$css)):
								$FILE = fopen(BASE_PATH.'public/css/'.$css,'r');
								$files_content .= '/* '.$css.' */'."\n".fread($FILE,filesize(BASE_PATH.'public/css/'.$css))."\n";
								fclose($FILE);
							endif;
						endforeach;
						if(strlen($files_content) > 0):
							$FILE = fopen(BASE_PATH.'public/css/cached/modules.'.md5(join('',$this->css['modules'])).'.css','w');
							fwrite($FILE,MinifyCSS::minify($files_content));
							fclose($FILE);
						endif;
					else:
						foreach($this->css['modules'] as $css) if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://') $files[] = $this->tag->link(
										array(
											'href' => $css,
											'rel' => 'stylesheet',
											'type' => 'text/css'
										)
								);
					endif;
					$files[] = $this->tag->link(
							array(
								'href' => '/css/cached/modules.'.md5(join('',$this->css['modules'])).'.css',
								'rel' => 'stylesheet',
								'type' => 'text/css'
							)
					);
				endif;

				# Theme CSS.
				/* */

				# Print CSS.
				if($this->css['print']) foreach($this->css['print'] as $css) $files[] = $this->tag->link(
								array(
									'href' => '/css/'.$css,
									'media' => 'print',
									'rel' => 'stylesheet',
									'type' => 'text/css'
								)
						);

				# Core JS.
				if(!is_file(BASE_PATH.'js/cached/core.'.md5(join('',$this->js['core'])).'.js')):
					$files_content = NULL;
					foreach($this->js['core'] as $js):
						if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://'):
							$files[] = $this->tag->script(
									NULL,array(
									'src' => $js,
									'type' => 'text/javascript'
									)
							);
						elseif(is_file(BASE_PATH.'js/'.$js)):
							$FILE = fopen(BASE_PATH.'js/'.$js,'r');
							$files_content .= '/* '.$js.' */'."\n".fread($FILE,filesize(BASE_PATH.'js/'.$js))."\n";
							fclose($FILE);
						endif;
					endforeach;
					if(strlen($files_content) > 0):
						$FILE = fopen(BASE_PATH.'js/cached/core.'.md5(join('',$this->js['core'])).'.js','w');
						fwrite($FILE,MinifyJS::minify($files_content));
						fclose($FILE);
					endif;
				else:
					foreach($this->js['core'] as $js) if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $files[] = $this->tag->script(
									NULL,array(
									'src' => $js,
									'type' => 'text/javascript'
									)
							);
				endif;
				$files[] = $this->tag->script(
						NULL,array(
						'src' => '/js/cached/core.'.md5(join('',$this->js['core'])).'.js',
						'type' => 'text/javascript'
						)
				);

				# Added JS.
				if(count($this->js['added'])):
					if(!is_file(BASE_PATH.'js/cached/hash.'.md5(join('',$this->js['added'])).'.js')):
						$files_content = NULL;
						foreach($this->js['added'] as $js):
							if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://'):
								$files[] = $this->tag->script(
										NULL,array(
										'src' => $js,
										'type' => 'text/javascript'
										)
								);
							elseif(is_file(BASE_PATH.'js/'.$js)):
								$FILE = fopen(BASE_PATH.'js/'.$js,'r');
								$files_content .= '/* '.$js.' */'."\n".fread($FILE,filesize(BASE_PATH.'js/'.$js))."\n";
								fclose($FILE);
							endif;
						endforeach;
						if(strlen($files_content) > 0):
							$FILE = fopen(BASE_PATH.'js/cached/hash.'.md5(join('',$this->js['added'])).'.js','w');
							fwrite($FILE,MinifyJS::minify($files_content));
							fclose($FILE);
						endif;
					else:
						foreach($this->js['added'] as $js) if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $files[] = $this->tag->script(
										NULL,array(
										'src' => $js,
										'type' => 'text/javascript'
										)
								);
					endif;
					$files[] = $this->tag->script(
							NULL,array(
							'src' => '/js/cached/hash.'.md5(join('',$this->js['added'])).'.js',
							'type' => 'text/javascript'
							)
					);
					if(Headers::ie6()):
						$files[] = $this->tag->script(
								NULL,array(
								'/js/pngfix.js',
								'type' => 'text/javascript'
								)
						);
					endif;
				endif;

				# Modular JS.
				if(count($this->js['modules'])):
					if(!is_file(BASE_PATH.'js/cached/modules.'.md5(join('',$this->js['modules'])).'.js')):
						$files_content = NULL;
						foreach($this->js['modules'] as $js):
							if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://'):
								$files[] = $this->tag->script(
										NULL,array(
										'src' => $js,
										'type' => 'text/javascript'
										)
								);
							elseif(is_file(BASE_PATH.'js/'.$js)):
								$FILE = fopen(BASE_PATH.'js/'.$js,'r');
								$files_content .= '/* '.$js.' */'."\n".fread($FILE,filesize(BASE_PATH.'js/'.$js))."\n";
								fclose($FILE);
							endif;
						endforeach;
						if(strlen($files_content) > 0):
							$FILE = fopen(BASE_PATH.'js/cached/modules.'.md5(join('',$this->js['modules'])).'.js','w');
							fwrite($FILE,MinifyJS::minify($files_content));
							fclose($FILE);
						endif;
					else:
						foreach($this->js['modules'] as $js) if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $files[] = $this->tag->script(
										NULL,array(
										'src' => $js,
										'type' => 'text/javascript'
										)
								);
					endif;
					$files[] = $this->tag->script(
							NULL,array(
							'src' => '/scripts/cached/modules.'.md5(join('',$this->js['modules'])).'.js',
							'type' => 'text/javascript'
							)
					);
				endif;

				# RSS.
				if(count($this->rss['core'] + $this->rss['added'])):
					foreach($this->rss['core'] + $this->rss['added'] as $title => $url):
						$files[] = $this->tag->link(
								array(
									'href' => $url,
									'rel' => 'alternate',
									'title' => $title,
									'type' => 'application/rss+xml'
								)
						);
					endforeach;
				endif;
			else:
				$files = NULL;
			endif;

			return $files;
		}

		/**
		 * Generate and return the HEAD tag of our Page.
		 * 
		 * @return Markup_Abstract
		 */
		public function head() {
			$head = $this->tag->head;

			# Page title.
			$head->append(
				$this->tag->title(
					(
					$this->show['title']
						?\PHY\Core::config('site/name').
						(
						$this->title
							?' - '
							:NULL
						)
						:NULL
					).
					$this->title
				)
			);

			# Shebang handler. Urls that might have been index via Google as #!/page
			if(isset($_GET['_escaped_fragment_'])) $head->append(
					$this->tag->noscript(
						$this->tag->meta(
							array(
								'http-equiv' => 'refresh',
								'content' => '0; URL='.$_GET['_escaped_fragment_']
							)
						)
					)
				);

			# Add meta tags.
			$this->meta['name_keywords']['content'] = 'talent, '.$this->_meta().$this->meta['name_keywords']['content'];
			foreach($this->meta as $meta):
				$tag = $this->tag->meta;
				$tag->attributes($meta);
				$head->append($tag);
			endforeach;
			$head->append($this->_files());
			$this->script(
				'if(window.location.hash.toString().match(\'!\')){var url=window.location.hash.toString().split(\'!\');window.location=url[1];}'.
				'if(typeof $===\'undefined\')var $={};$.user={xsrf:\''.Cookie::get('xsrf_id').'\'};'
			);
			foreach($this->scripts as $script) $head->append($script);

			return $head;
		}

		/**
		 * Generate and return the FOOTER tag of our Page.
		 * 
		 * @return Markup_Abstract
		 */
		public function footer() {
			if(is_file(BASE_PATH.'phy/templates/'.\PHY\Core::config('site/template').'/footer.phtml')):
				ob_start();
				include BASE_PATH.'phy/templates/'.\PHY\Core::config('site/template').'/footer.phtml';
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			else:
				# <footer> tag that holds all this info.
				$footer = $this->tag->header;
				$footer->attributes(array('id' => 'footer'));
				$footer->append('<!-- No footer was defined -->');
				return $footer;
			endif;
		}

		/**
		 * Generate the HEADER section of our BODY section.
		 * 
		 * @return string|Markup_Abstract
		 */
		public function header() {
			if(is_file(BASE_PATH.'phy/templates/'.\PHY\Core::config('site/template').'/header.phtml')):
				ob_start();
				include BASE_PATH.'phy/templates/'.\PHY\Core::config('site/template').'/header.phtml';
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			else:
				# <header> tag that holds all this info.
				$header = $this->tag->header;
				$header->attributes(array('id' => 'header'));

				$header->append('<!-- No header was defined -->');
				return $header;
			endif;
		}

		/**
		 * Internal cleaner for attributes.
		 * 
		 * If you send just a string then it will be set as the class
		 * If you send a string with a : then it will set key:value.
		 * 
		 * @param mixed $attributes
		 * @internal
		 * @return mixed
		 */
		private function _attributes($attributes=NULL) {
			if($attributes === NULL) return;
			if(is_string($attributes)):
				$split = explode(':',$attributes);
				if(isset($split[1])) $attributes[$split[0]] = $split[1];
				else $attributes = array('class' => $attributes);
			endif;
			return $attributes;
		}

		/**
		 * Check our Markup heap for important tags. Return them if they exist.
		 * 
		 * @param type $limit
		 * @return type 
		 */
		private function _extract($limit=15) {
			if(\PHY\Markup::elements() > 20 && \PHY\Markup::important()):
				$rows = array();
				foreach(Markup::important() as $row) $rows[] = $row->content;
				\PHY\Markup::important()->rewind();
				$rows = array_unique($rows);
				$parsed = array();
				foreach($rows as $row) foreach(explode('{}',str_replace(array('&',',','.',':',';','?','!','- ',"'",'"'),'{}',$row)) as $item) if(strlen(trim($item)) > 2) $parsed[] = trim($item);
				return array_slice($parsed,0,$limit);
			else:
				return array();
			endif;
		}

		/**
		 * Attempt to grab relevant meta tags on page generation. We use this if
		 * meta details weren't set.
		 * 
		 * @return string
		 */
		private function _meta() {
			$this->keywords['added'] = $this->_extract(15);
			$keywords = array();
			foreach($this->keywords['added'] as $words):
				if(!$words) continue;
				$words = explode(' ',$words);
				$word = NULL;
				for($i = 0; $i < 3; ++$i):
					if(isset($words[$i]) && strlen($words[$i]) > 3):
						$word = trim($words[$i]);
						$validated = strtolower(preg_replace('#[^abcdefghijklmnopqrstuvwxyz ]#i','',$word));
						if(strlen($validated) > 5 && !in_array(strtolower($validated),$this->keywords['invalid'])):
							if(isset($keywords[$validated])) ++$keywords[$validated];
							else $keywords[$validated] = 1;
						endif;
					endif;
				endfor;
			endforeach;
			arsort($keywords);
			$return = array();
			foreach($keywords as $word => $count) if($count > 1) $return[] = $word;
			return ((count($return))
				?join(', ',$return).', '
				:NULL);
		}

	}
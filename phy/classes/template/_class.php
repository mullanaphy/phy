<?php

	/**
	 * @package Template
	 * @category Frontend
	 * @author John Mullanaphy
	 * @static
	 */
	final class Template {

		protected static $css = array(
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

#####	# Magic methods.
		# Define what HTML tags to use.

		public function __construct() {
			self::init();
		}

		public function __destruct() {
			self::flush();
		}

		# Generate the tag when on the __toString method.

		public function __toString() {
			return (string)self::generate();
		}


		static public function flush() {
			self::head();
			self::body();
			echo self::$tag->CLOSER;
		}

		static public function init() {
			# Meta data.
			self::$meta = array(
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
					'content' => Constant::CONFIG('site/description')
				),
				'name_keywords' => array(
					'name' => 'keywords',
					'content' => Constant::CONFIG('site/keywords')
				),
				'name_author' => array(
					'name' => 'author',
					'content' => Constant::CONFIG('site/name')
				),
				'name_contact' => array(
					'name' => 'contact',
					'content' => Constant::CONFIG('site/email')
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
					'content' => Constant::CONFIG('site/name')
				)
			);

			# HTML Version to use.
			switch(true):
				case (Headers::ie6()):
					self::$tag = new Markup('HTML4');
					break;
				case (Headers::bot()):
					self::$tag = new Markup('HTML4');
					break;
				case (Headers::mobile()):
					self::$meta[] = array(
						'name' => 'viewport',
						'content' => 'user-scalable=no,width=device-width,minimum-scale=1.0,maximum-scale=1.0'
					);
				default:
					self::$tag = new Markup;
			endswitch;

			echo self::$tag->DOCTYPE,
			self::$tag->OPENER;
			flush();
			if(ob_list_handlers()) ob_flush();
		}

#####	# Inserting content.

		public function append($content=false,$attributes=NULL) {
			if($content === false) return false;
			if(is_numeric($attributes)) $attributes = array('style' => 'width:'.(($attributes >= 1)?(int)$attributes.'px':(int)($attributes * 100).'%'));
			else $attributes = self::_attributes($attributes);
			if(!count(self::$sections)) self::section('normal');
			elseif(!count(self::$sections[count(self::$sections) - 1]['columns'])) self::column();
			self::$sections[count(self::$sections) - 1]['columns'][count(self::$sections[count(self::$sections) - 1]['columns']) - 1]['content'][] = array(
				'attributes' => $attributes,
				'content' => $content
			);
			return true;
		}

		public function column($attributes=NULL) {
			if(is_numeric($attributes)) $attributes = array('style' => 'width:'.(($attributes >= 1)?(int)$attributes.'px':(int)($attributes * 100).'%'));
			else $attributes = self::_attributes($attributes);
			if(isset($attributes['width'])):
				if(isset($attributes['style'])) $attributes['style'] .= 'width:'.(($attributes['width'] >= 1)?(int)$attributes['width'].'px':(int)($attributes['width'] * 100).'%').';';
				else $attributes['style'] = 'width:'.(($attributes['width'] >= 1)?(int)$attributes['width'].'px':(int)($attributes['width'] * 100).'%').';';
				unset($attributes['width']);
			endif;
			if(!count(self::$sections)) self::section('normal');
			self::$sections[count(self::$sections) - 1]['columns'][] = array(
				'attributes' => $attributes,
				'containers' => array()
			);
		}

		public function heading($content=NULL,$tag='h2',$attributes=NULL) {
			if($content === NULL) return;
			if(is_array($tag)):
				$attributes = $tag;
				$tag = 'h2';
			elseif(!preg_match('#h[0-6]#i',$tag)):
				$tag = 'h2';
			endif;
			if(!is_array($attributes)) $attributes = array();
			if(isset($attributes['class'])) $attributes['class'] = 'heading '.$attributes['class'];
			else $attributes['class'] = 'heading';
			if(!count(self::$sections)) self::section('normal');
			elseif(!count(self::$sections[count(self::$sections) - 1]['columns'])) self::column();
			self::$sections[count(self::$sections) - 1]['heading'] = self::$tag->$tag(
					$content,
					$attributes
			);
		}

		public function prepend($content=false,$attributes=NULL) {
			if($content === false) return false;
			if(is_numeric($attributes)) $attributes = array('style' => 'width:'.(($attributes >= 1)?(int)$attributes.'px':(int)($attributes * 100).'%'));
			else $attributes = self::_attributes($attributes);
			array_unshift(
				self::$sections[count(self::$sections) - 1]['columns'][count(self::$sections[count(self::$sections) - 1]['columns']) - 1]['content'],
				array(
					'attributes' => $attributes,
					'content' => $content
				)
			);
			return true;
		}

		public function script($content=NULL,$attributes=NULL) {
			if($content === NULL) return self::$scripts;

			# Make sure a type is set.
			if(!is_array($attributes)) $attributes = array('type' => 'text/javascript');
			elseif(!isset($attributes['type'])) $attributes['type'] = 'text/javascript';

			# Store it.
			self::$scripts[] = self::$tag->script($content,$attributes);
		}

		public function section($type='normal',$attributes=NULL,$expand=NULL) {
			if(is_bool($attributes)):
				$expanded = !!$attributes;
				$attributes = self::_attributes($expand);
			else:
				$expanded = false;
				$attributes = self::_attributes($attributes);
			endif;
			if(isset($attributes['class'])) $attributes['class'] = $type.' '.$attributes['class'];
			else $attributes['class'] = $type;
			self::$sections[] = array(
				'attributes' => $attributes,
				'columns' => array(),
				'expanded' => $expanded,
				'heading' => NULL
			);
		}

#####	# Settings values.

		static public function css($css=NULL) {
			if($css === NULL) return array_merge(self::$css['added'],self::$css['modules']);
			foreach(func_get_args() as $css):
				if(is_array($css)):
					foreach($css as $file):
						if(preg_match('#print\.css|print/#',$file)) self::$css['print'][] = $file;
						else self::$css['added'][] = $file;
					endforeach;
				else:
					if(preg_match('#print\.css|print/#',$css)) self::$css['print'][] = $css;
					else self::$css['added'][] = $css;
				endif;
			endforeach;
			self::$css['added'] = array_unique(self::$css['added']);
			self::$css['print'] = array_unique(self::$css['print']);
			return true;
		}

		public function description($description=NULL) {
			if($description === NULL) return self::$meta['name_description']['content'];
			self::$meta['name_description']['content'] = $description;
		}

		static public function files($files=NULL) {
			if($files === NULL) return array_merge(self::$css['added'],self::$css['modules'],self::$js['added'],self::$js['modules']);
			foreach(func_get_args() as $files):
				if(is_array($files)):
					foreach($files as $file):
						if(substr($file,-4) === '.css') self::$css['modules'][] = $file;
						elseif(substr($file,-3) === '.js') self::$js['modules'][] = $file;
					endforeach;
				else:
					if(substr($files,-4) === '.css') self::$css['modules'][] = $files;
					elseif(substr($files,-3) === '.js') self::$js['modules'][] = $files;
				endif;
			endforeach;
			self::$css['modules'] = array_unique(self::$css['modules']);
			self::$js['modules'] = array_unique(self::$js['modules']);
			return true;
		}

		public function hide() {
			if(!count(func_get_args())) return self::$show;
			foreach(func_get_args() as $hide) if(isset(self::$show[$hide])) self::$show[$hide] = false;
		}

		public function js($js=NULL) {
			if($js === NULL) return array_merge(self::$js['added'],self::$js['modules']);
			foreach(func_get_args() as $js):
				if(is_array($js)) foreach($js as $file) self::$js['added'][] = $file;
				else self::$js['added'][] = $js;
			endforeach;
			self::$js['added'] = array_unique(self::$js['added']);
			return true;
		}

		public function keywords() {
			if(!count(func_get_args())) return self::$meta['name_keywords']['content'];
			self::$meta['name_keywords']['content'] = join(', ',func_get_args());
		}

		public function meta($meta=NULL) {
			if(is_array($meta)) self::$meta[] = $meta;
			return self::$meta;
		}

		public function rss($rss=NULL) {
			if($rss === NULL) return self::$rss['added'];
			foreach(func_get_args() as $rss):
				if(is_array($rss)) foreach($rss as $file) self::$rss['added'][] = $file;
				else self::$rss['added'][] = $rss;
			endforeach;
			self::$rss['added'] = array_unique(self::$rss['added']);
		}

		public function show() {
			if(!count(func_get_args())) return self::$show;
			foreach(func_get_args() as $show) if(isset(self::$show[$show])) self::$show[$show] = false;
		}

		public function title($title=false) {
			if($title) self::$title = $title;
			return self::$title;
		}

#####	# Generating.

		protected function body() {
			$body = self::$tag->body;

			# Process the header.
			if(self::$show['header']) $body->append(self::header());

			# Process the actual page.
			$i = false;
			foreach(self::$sections as $group):
				if(!$i):
					$i = true;
					if(isset($group['attributes']['class'])) $group['attributes']['class'] = 'first '.$group['attributes']['class'];
					else $group['attributes']['class'] = 'first';
				endif;
				if(isset($group['attributes']['class'])) $group['attributes']['class'] = 'section '.$group['attributes']['class'];
				else $group['attributes']['class'] = 'section';
				$section = self::$tag->div;
				$section->attributes($group['attributes']);
				$holder = self::$tag->div;
				if(!$group['expanded']) $holder->attributes(array('class' => 'holder'));
				foreach($group['columns'] as $col):
					$column = self::$tag->div;
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
			if(self::$show['footer']) $body->append(self::footer());
			if(!Constant::CONFIG('site/production')):
				foreach(self::$js['footer'] as $js) $body->append(
						self::$tag->script(
							NULL,
							array(
								'src' => (substr($js,0,4) === 'http'?$js:'/js/'.$js),
								'type' => 'text/javascript'
							)
						)
					);
			else:
				if(!in_array(USER_BROWSER,self::$browsers['bots'] + self::$browsers['text'])):
					$footer = array();
					foreach(self::$js['footer'] as $js):
						if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $body->append(
								self::$tag->script(
									NULL,
									array(
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
						self::$tag->script(
							NULL,
							array(
								'src' => '/scripts/cached/footer.'.md5(join('',self::$js['footer'])).'.js',
								'type' => 'text/javascript'
							)
						)
					);
				endif;
				$body->append(
					self::$tag->script(
						'var gaJsHost=((\'https:\'==document.location.protocol)?\'https://ssl.\':\'http://www.\');document.write(unescape("%3Cscript src=\'"+gaJsHost+"google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));',
						array('type' => 'text/javascript')
					)
				);
				$body->append(
					self::$tag->script(
						'try{var pageTracker=_gat._getTracker(\'UA-2763315-2\');pageTracker._trackPageview();}catch(e){}',
						array('type' => 'text/javascript')
					)
				);
			endif;
			$body->append(
				self::$tag->script(
					'try{if(console)console.log(\'Generation: '.Debug::timer().'; Elements: '.Markup_HTML5::elements().'; Server: '.$_SERVER['SERVER_ADDR'].'\');}catch(e){};',
					array('type' => 'text/javascript')
				)
			);
			echo $body;
		}

		protected function _files() {
			$files = array();

			# Devo and Beta servers. We will not combine files on these servers.
			if(in_array(USER_BROWSER,array('ie','ie6')) || !Constant::CONFIG('site/production')):
				foreach(array_merge(self::$css['core'],self::$css['added'],self::$css['modules']) as $css) $files[] = self::$tag->link(
							array(
								'href' => '/css/'.$css,
								'rel' => 'stylesheet',
								'type' => 'text/css'
							)
					);

				# Theme.
				/* Goes here */

				# Print CSS.
				if(self::$css['print']) foreach(self::$css['print'] as $css) $files[] = self::$tag->link(
								array(
									'href' => '/css/'.$css,
									'media' => 'print',
									'rel' => 'stylesheet',
									'type' => 'text/css'
								)
						);

				foreach(array_merge(self::$js['core'],self::$js['added'],self::$js['modules']) as $js) $files[] = self::$tag->script(
							NULL,
							array(
								'src' => (substr($js,0,4) === 'http'?$js:'/js/'.$js),
								'type' => 'text/javascript'
							)
					);

			# Live servers, we combine the files to make less requests per page.
			elseif(!Headers::bot()):
				# Core CSS.
				if(!is_file(BASE_PATH.'public/css/cached/core.'.md5(join('',self::$css['core'])).'.css')):
					$files_content = NULL;
					foreach(self::$css['core'] as $css):
						if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://'):
							$files[] = self::$tag->link(
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
						$FILE = fopen(BASE_PATH.'public/css/cached/core.'.md5(join('',self::$css['core'])).'.css','w');
						fwrite($FILE,MinifyCSS::minify($files_content));
						fclose($FILE);
					endif;
				endif;
				$files[] = self::$tag->link(
						array(
							'href' => '/css/cached/core.'.md5(join('',self::$css['core'])).'.css',
							'rel' => 'stylesheet',
							'type' => 'text/css'
						)
				);
				# Added CSS.
				if(count(self::$css['added'])):
					if(!is_file(BASE_PATH.'public/css/cached/hash.'.md5(join('',self::$css['added'])).'.css')):
						$files_content = NULL;
						foreach(self::$css['added'] as $css):
							if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://'):
								$files[] = self::$tag->link(
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
							$FILE = fopen(BASE_PATH.'public/css/cached/hash.'.md5(join('',self::$css['added'])).'.css','w');
							fwrite($FILE,MinifyCSS::minify($files_content));
							fclose($FILE);
						endif;
					endif;
					$files[] = self::$tag->link(
							array(
								'href' => '/css/cached/hash.'.md5(join('',self::$css['added'])).'.css',
								'rel' => 'stylesheet',
								'type' => 'text/css'
							)
					);
				endif;

				# Modular CSS.
				if(count(self::$css['modules'])):
					if(!is_file(BASE_PATH.'public/css/cached/modules.'.md5(join('',self::$css['modules'])).'.css')):
						$files_content = NULL;
						foreach(self::$css['modules'] as $css):
							if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://'):
								$files[] = self::$tag->link(
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
							$FILE = fopen(BASE_PATH.'public/css/cached/modules.'.md5(join('',self::$css['modules'])).'.css','w');
							fwrite($FILE,MinifyCSS::minify($files_content));
							fclose($FILE);
						endif;
					else:
						foreach(self::$css['modules'] as $css) if(substr($css,0,7) === 'http://' || substr($css,0,8) === 'https://') $files[] = self::$tag->link(
										array(
											'href' => $css,
											'rel' => 'stylesheet',
											'type' => 'text/css'
										)
								);
					endif;
					$files[] = self::$tag->link(
							array(
								'href' => '/css/cached/modules.'.md5(join('',self::$css['modules'])).'.css',
								'rel' => 'stylesheet',
								'type' => 'text/css'
							)
					);
				endif;

				# Theme CSS.
				/* */

				# Print CSS.
				if(self::$css['print']) foreach(self::$css['print'] as $css) $files[] = self::$tag->link(
								array(
									'href' => '/css/'.$css,
									'media' => 'print',
									'rel' => 'stylesheet',
									'type' => 'text/css'
								)
						);

				# Core JS.
				if(!is_file(BASE_PATH.'js/cached/core.'.md5(join('',self::$js['core'])).'.js')):
					$files_content = NULL;
					foreach(self::$js['core'] as $js):
						if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://'):
							$files[] = self::$tag->script(
									NULL,
									array(
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
						$FILE = fopen(BASE_PATH.'js/cached/core.'.md5(join('',self::$js['core'])).'.js','w');
						fwrite($FILE,MinifyJS::minify($files_content));
						fclose($FILE);
					endif;
				else:
					foreach(self::$js['core'] as $js) if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $files[] = self::$tag->script(
									NULL,
									array(
										'src' => $js,
										'type' => 'text/javascript'
									)
							);
				endif;
				$files[] = self::$tag->script(
						NULL,
						array(
							'src' => '/js/cached/core.'.md5(join('',self::$js['core'])).'.js',
							'type' => 'text/javascript'
						)
				);

				# Added JS.
				if(count(self::$js['added'])):
					if(!is_file(BASE_PATH.'js/cached/hash.'.md5(join('',self::$js['added'])).'.js')):
						$files_content = NULL;
						foreach(self::$js['added'] as $js):
							if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://'):
								$files[] = self::$tag->script(
										NULL,
										array(
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
							$FILE = fopen(BASE_PATH.'js/cached/hash.'.md5(join('',self::$js['added'])).'.js','w');
							fwrite($FILE,MinifyJS::minify($files_content));
							fclose($FILE);
						endif;
					else:
						foreach(self::$js['added'] as $js) if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $files[] = self::$tag->script(
										NULL,
										array(
											'src' => $js,
											'type' => 'text/javascript'
										)
								);
					endif;
					$files[] = self::$tag->script(
							NULL,
							array(
								'src' => '/js/cached/hash.'.md5(join('',self::$js['added'])).'.js',
								'type' => 'text/javascript'
							)
					);
					if(Headers::ie6()):
						$files[] = self::$tag->script(
								NULL,
								array(
									'/js/pngfix.js',
									'type' => 'text/javascript'
								)
						);
					endif;
				endif;

				# Modular JS.
				if(count(self::$js['modules'])):
					if(!is_file(BASE_PATH.'js/cached/modules.'.md5(join('',self::$js['modules'])).'.js')):
						$files_content = NULL;
						foreach(self::$js['modules'] as $js):
							if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://'):
								$files[] = self::$tag->script(
										NULL,
										array(
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
							$FILE = fopen(BASE_PATH.'js/cached/modules.'.md5(join('',self::$js['modules'])).'.js','w');
							fwrite($FILE,MinifyJS::minify($files_content));
							fclose($FILE);
						endif;
					else:
						foreach(self::$js['modules'] as $js) if(substr($js,0,7) === 'http://' || substr($js,0,8) === 'https://') $files[] = self::$tag->script(
										NULL,
										array(
											'src' => $js,
											'type' => 'text/javascript'
										)
								);
					endif;
					$files[] = self::$tag->script(
							NULL,
							array(
								'src' => '/scripts/cached/modules.'.md5(join('',self::$js['modules'])).'.js',
								'type' => 'text/javascript'
							)
					);
				endif;

				# RSS.
				if(count(self::$rss['core'] + self::$rss['added'])):
					foreach(self::$rss['core'] + self::$rss['added'] as $title => $url):
						$files[] = self::$tag->link(
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

		public function head() {
			$head = self::$tag->head;

			# Page title.
			$head->append(
				self::$tag->title(
					(
					self::$show['title']?Constant::CONFIG('site/name').
						(
						self::$title?' - ':NULL
						):NULL
					).
					self::$title
				)
			);

			# Shebang handler. Urls that might have been index via Google as #!/page
			if(isset($_GET['_escaped_fragment_'])) $head->append(
					self::$tag->noscript(
						self::$tag->meta(
							array(
								'http-equiv' => 'refresh',
								'content' => '0; URL='.$_GET['_escaped_fragment_']
							)
						)
					)
				);

			# Add meta tags.
			self::$meta['name_keywords']['content'] = 'talent, '.self::_meta().self::$meta['name_keywords']['content'];
			foreach(self::$meta as $meta):
				$tag = self::$tag->meta;
				$tag->attributes($meta);
				$head->append($tag);
			endforeach;
			$head->append(self::_files());
			self::script(
					'if(window.location.hash.toString().match(\'!\')){var url=window.location.hash.toString().split(\'!\');window.location=url[1];}'.
					'if(typeof $===\'undefined\')var $={};$.user={xsrf:\''.Cookie::get('xsrf_id').'\'};'
			);
			foreach(self::$scripts as $script) $head->append($script);

			echo $head;
			flush();
			if(ob_list_handlers()) ob_flush();
		}

		public function footer() {
			if(is_file(BASE_PATH.'phy/templates/'.Constant::CONFIG('site/template').'/footer.phtml')):
				ob_start();
				include 'phy/templates/'.Constant::CONFIG('site/template').'/footer.phtml';
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			else:
				# <footer> tag that holds all this info.
				$footer = self::$tag->header;
				$footer->attributes(array('id' => 'footer'));
				$footer->append('[FOOTER]');
				return $footer;
			endif;
		}

		public function header() {
			if(is_file(BASE_PATH.'phy/templates/'.Constant::CONFIG('site/template').'/header.phtml')):
				ob_start();
				include 'phy/templates/'.Constant::CONFIG('site/template').'/header.phtml';
				$content = ob_get_contents();
				ob_end_clean();
				return $content;
			else:
				# <header> tag that holds all this info.
				$header = self::$tag->header;
				$header->attributes(array('id' => 'header'));

				$header->append('[HEADER]');
				return $header;
			endif;
		}

#####	# Parsing.

		protected function _attributes($attributes=NULL) {
			if($attributes === NULL) return;
			if(is_string($attributes)):
				$split = explode(':',$attributes);
				if(isset($split[1])) $attributes[$split[0]] = $split[1];
				else $attributes = array('class' => $attributes);
			endif;
			return $attributes;
		}

		protected function _extract($limit=15) {
			if(Markup_HTML5::elements() > 20 && Markup_HTML5::important()):
				$rows = array();
				foreach(Markup_HTML5::important() as $row) $rows[] = $row->content;
				Markup_HTML5::important()->rewind();
				$rows = array_unique($rows);
				$parsed = array();
				foreach($rows as $row) foreach(explode('{}',str_replace(array('&',',','.',':',';','?','!','- ',"'",'"'),'{}',$row)) as $item) if(strlen(trim($item)) > 2) $parsed[] = trim($item);
				return array_slice($parsed,0,$limit);
			else:
				return array();
			endif;
		}

		protected function _meta() {
			self::$keywords['added'] = self::_extract(15);
			$keywords = array();
			foreach(self::$keywords['added'] as $words):
				if(!$words) continue;
				$words = explode(' ',$words);
				$word = NULL;
				for($i = 0; $i < 3; ++$i):
					if(isset($words[$i]) && strlen($words[$i]) > 3):
						$word = trim($words[$i]);
						$validated = strtolower(preg_replace('#[^abcdefghijklmnopqrstuvwxyz ]#i','',$word));
						if(strlen($validated) > 5 && !in_array(strtolower($validated),self::$keywords['invalid'])):
							if(isset($keywords[$validated])) ++$keywords[$validated];
							else $keywords[$validated] = 1;
						endif;
					endif;
				endfor;
			endforeach;
			arsort($keywords);
			$return = array();
			foreach($keywords as $word => $count) if($count > 1) $return[] = $word;
			return ((count($return))?join(', ',$return).', ':NULL);
		}

	}

?>
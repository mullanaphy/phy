<?php

	namespace PHY\Image;

	/**
	 * Crop, convert, resize, and add watermarks to any image. Utilizes GD.
	 *
	 * @category Image
	 * @package Image\Cropper
	 * @author John Mullanaphy
	 * @return Cropper
	 */
	final class Cropper {

		private $source = NULL,
		$file_type = NULL,
		$coordinates = array(0,0),
		$original_size = array(0,0),
		$size = array(0,0),
		$path = '',
		$quality = 0.0,
		$watermark = '',
		$watermark_percentage = 100,
		$watermark_size = .75;

#####	# Magic methods.

		/**
		 * Open up an image to manipulate.
		 * 
		 * @param string $source
		 * @param string $path
		 * @param array $coordinates
		 * @param array $original_size
		 * @param array $size
		 * @param float $quality
		 * @return Cropper 
		 */
		public function __construct($source=NULL,$path=NULL,$coordinates=array(0,0),$original_size=array(0,0),$size=array(194,194),$quality=100) {
			$this->source = NULL;
			if($source !== NULL):
				$file_type = strtolower(substr($source,strrpos($source,'.')));
				$file_type = strtolower($file_type);
				if(in_array($file_type,array('.jpg','.jpeg','.jpe','.jfif','.jfi','.jif'))) $this->source = imagecreatefromjpeg($source);
				elseif($file_type === '.png') $this->source = imagecreatefrompng($source);
				elseif($file_type === '.gif') $this->source = imagecreatefromgif($source);
				$this->file_type = $file_type;
			endif;
			$this->coordinates = $coordinates;
			$this->size = $size;
			$this->original_size = $original_size;
			$this->path = $path;
			$this->quality = $quality;
			$this->watermark = false;
			$this->watermark_percentage = 100;
			$this->watermark_size = .75;
			return $this;
		}

		/**
		 * Set the source of the image to be manipulated.
		 * 
		 * @param string $source
		 * @return string|bool
		 */
		public function source($source=NULL) {
			if($source !== NULL):
				$file_type = strtolower(substr($source,strrpos($source,'.')));
				$file_type = strtolower($file_type);
				if(in_array($file_type,array('.jpg','.jpeg','.jpe','.jfif','.jfi','.jif'))) $this->source = imagecreatefromjpeg($source);
				elseif($file_type === '.png') $this->source = imagecreatefrompng($source);
				elseif($file_type === '.gif') $this->source = imagecreatefromgif($source);
				$this->file_type = $file_type;
				list($width,$height,$file_type,$attributes) = getimagesize($source);
				$this->original_size($width,$height);
				return $this->source;
			else:
				return false;
			endif;
		}

		/**
		 * Define the original size to crop from.
		 * 
		 * @param int|array $x
		 * @param int $y
		 * @return array
		 */
		public function original_size($x,$y=false) {
			if(is_array($x) && isset($x[0],$x[1])):
				$y = $x[1];
				$x = $x[0];
			endif;
			$this->original_size = array($x,$y);
			return $this->original_size;
		}

		/**
		 * Set the size of the image to be created.
		 * 
		 * @param int|array $x
		 * @param int $y
		 * @return array
		 */
		public function size($x,$y=0) {
			if(is_array($x) && isset($x[0],$x[1])):
				$y = $x[1];
				$x = $x[0];
			endif;
			if($y == 0) $y = $x;
			$this->size = array($x,$y);
			return $this->size;
		}

		/**
		 * Set the coordinates to start a crop from.
		 * 
		 * @param int|array $x
		 * @param int $y
		 * @return array
		 */
		public function coordinates($x=0,$y=0) {
			if(is_array($x) && isset($x[0],$x[1])):
				$y = $x[1];
				$x = $x[0];
			endif;
			$this->coordinates = array($x,$y);
			return $this->coordinates;
		}

		/**
		 * Define the path where the new image will be created.
		 *
		 * @param string $path
		 * @return string
		 */
		public function path($path='') {
			$this->path = $path;
			return $this->path;
		}

		/**
		 * Set the image quality. For saving a JPG only.
		 * 
		 * @param float $quality
		 * @return float 
		 */
		public function quality($quality=.9) {
			$this->quality = $quality;
			return $this->quality;
		}

		/**
		 * Set a watermark to use.
		 * 
		 * @param string $watermark
		 * @param int $percentage
		 * @param float $size
		 * @return string
		 */
		public function watermark($watermark=false,$percentage=100,$size=.75) {
			if($size > 1) $size /= 100;
			$this->watermark = $watermark;
			$this->watermark_percentage = $percentage;
			$this->watermark_size = $size;
			return $this->watermark;
		}

		/**
		 * Generate the new image.
		 * 
		 * @param string $destination
		 * @param string $source
		 * @return resource
		 */
		public function generate($destination,$source=false) {
			if($source):
				$file_type = strtolower(substr($source,strrpos($source,'.')));
				$file_type = strtolower($file_type);
				if(in_array($file_type,array('.jpg','.jpeg','.jpe','.jfif','.jfi','.jif'))):
					$source = imagecreatefromjpeg($source);
				elseif($file_type === '.png'):
					$source = imagecreatefrompng($source);
				elseif($file_type === '.gif'):
					$source = imagecreatefromgif($source);
				endif;
			else:
				$file_type = $this->file_type;
				$source = $this->source;
			endif;

			$destination = $this->path.$destination;
			if($this->watermark):
				$watermark = imagecreatefrompng($this->watermark);
				$x = imagesx($watermark);
				$y = imagesy($watermark);
				$c_x = $this->size[0] - $x;
				$c_y = $this->size[1] - $y;
				$image = imagecreatetruecolor($this->size[0],$this->size[1]);
				imagecopyresampled($image,$source,0,0,$this->coordinates[0],$this->coordinates[1],$this->size[0],$this->size[1],$this->original_size[0],$this->original_size[1] * $this->watermark_size);
				self::_alpha($image,$watermark,$c_x,$c_y,0,0,$x,$y,$this->watermark_percentage);
			else:
				$image = imagecreatetruecolor($this->size[0],$this->size[1]);
				$white = imagecolorallocate($image,255,255,255);
				imagefilledrectangle($image,0,0,$this->size[0],$this->size[1],$white);
				imagecopyresampled($image,$source,0,0,$this->coordinates[0],$this->coordinates[1],$this->size[0],$this->size[1],$this->original_size[0],$this->original_size[1]);
			endif;

			if(is_file($destination)) unlink($destination);
			$file_type = strtolower(substr($destination,strrpos($destination,'.')));
			$file_type = strtolower($file_type);

			if(in_array($file_type,array('.jpg','.jpeg','.jpe','.jfif','.jfi','.jif'))):
				imageinterlace($image,true);
				return imagejpeg($image,$destination,$this->quality);
			elseif($file_type === '.png'):
				return imagepng($image,$destination);
			elseif($file_type === '.gif'):
				return imagegif($image,$destination);
			else:
				return false;
			endif;
		}

		/**
		 * Sets alpha channel for images.
		 * 
		 * @access private
		 * @ignore
		 * @param string $destination
		 * @param string $source
		 * @param int $destination_x
		 * @param int $destination_y
		 * @param int $source_x
		 * @param int $source_y
		 * @param int $source_w
		 * @param int $source_h
		 * @param int $percentage
		 */
		private static function _alpha($destination,$source,$destination_x,$destination_y,$source_x,$source_y,$source_w,$source_h,$percentage) {
			if(!isset($percentage)) return false;
			$percentage /= 100;

			# Get image width and height
			$w = imagesx($source);
			$h = imagesy($source);

			# Turn alpha blending off
			imagealphablending($source,false);

			# Find the most opaque pixel in the image (the one with the smallest alpha value)
			$minalpha = 127;
			for($x = 0; $x < $w; ++$x):
				for($y = 0; $y < $h; ++$y):
					$alpha = (imagecolorat($source,$x,$y) >> 24) & 0xFF;
					if($alpha < $minalpha) $minalpha = $alpha;
				endfor;
			endfor;

			# loop through image pixels and modify alpha for each
			for($x = 0; $x < $w; ++$x):
				for($y = 0; $y < $h; ++$y):
					# get current alpha value (represents the TRANSPARENCY!)
					$colorxy = imagecolorat($source,$x,$y);
					$alpha = ($colorxy >> 24) & 0xFF;

					# calculate new alpha
					if($minalpha !== 127) $alpha = 127 + 127 * $percentage * ($alpha - 127) / (127 - $minalpha);
					else $alpha+=127 * $percentage;

					# get the color index with new alpha
					$alphacolorxy = imagecolorallocatealpha($source,($colorxy >> 16) & 0xFF,($colorxy >> 8) & 0xFF,$colorxy & 0xFF,$alpha);

					# set pixel with the new color + opacity
					if(!imagesetpixel($source,$x,$y,$alphacolorxy)) return false;
				endfor;
			endfor;

			# The image copy
			imagecopy($destination,$source,$destination_x,$destination_y,$source_x,$source_y,$source_w,$source_h);
		}

	}
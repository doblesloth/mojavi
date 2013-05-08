<?php
/**
 * Class used to resize images and perform other image manipulation
 * @author hobby
 */
class ImageResize {
	
	private $source_image_location;
	private $destination_image_width;
	private $destination_image_height;
	private $destination_image_padding;
	
	/**
	 * Returns the source_image_location
	 * @return string
	 */
	function getSourceImageLocation() {
		if (is_null($this->source_image_location)) {
			$this->source_image_location = "";
		}
		return $this->source_image_location;
	}
	
	/**
	 * Sets the source_image_location
	 * @param $arg0 string
	 */
	function setSourceImageLocation($arg0) {
		$this->source_image_location = $arg0;
		return $this;
	}
	
	/**
	 * Returns the destination_image_width
	 * @return integer
	 */
	function getDestinationImageWidth() {
		if (is_null($this->destination_image_width)) {
			$this->destination_image_width = 128;
		}
		return $this->destination_image_width;
	}
	
	/**
	 * Sets the destination_image_width
	 * @param $arg0 integer
	 */
	function setDestinationImageWidth($arg0) {
		$this->destination_image_width = $arg0;
		return $this;
	}
	
	/**
	 * Returns the destination_image_height
	 * @return integer
	 */
	function getDestinationImageHeight() {
		if (is_null($this->destination_image_height)) {
			$this->destination_image_height = 128;
		}
		return $this->destination_image_height;
	}
	
	/**
	 * Sets the destination_image_height
	 * @param $arg0 integer
	 */
	function setDestinationImageHeight($arg0) {
		$this->destination_image_height = $arg0;
		return $this;
	}
	
	/**
	 * Returns the destination_image_padding
	 * @return integer
	 */
	function getDestinationImagePadding() {
		if (is_null($this->destination_image_padding)) {
			$this->destination_image_padding = 5;
		}
		return $this->destination_image_padding;
	}
	
	/**
	 * Sets the destination_image_padding
	 * @param $arg0 integer
	 */
	function setDestinationImagePadding($arg0) {
		$this->destination_image_padding = $arg0;
		return $this;
	}
	
	/**
	 * Resizes the image
	 * @return string
	 */
	function resize($source_image_contents = null) {
		if ($source_image_contents == null) {
			if (file_exists($this->getSourceImageLocation())) {
				$source_image_contents = file_get_contents($this->getSourceImageLocation());	
			}
		}
		$src_img = imagecreatefromstring($source_image_contents);
		$src_width = imagesx($src_img);
		$src_height = imagesy($src_img);
		$dest_height = $this->getDestinationImageHeight();
		$dest_width = $this->getDestinationImageWidth();
		if ($src_width > $src_height) {
			$dest_proportion =  $src_height / $src_width;
			$dest_height = $this->getDestinationImageHeight() * $dest_proportion - $this->getDestinationImagePadding();
			$dest_width = $dest_width - $this->getDestinationImagePadding();
		} else {
			$dest_proportion = $src_width / $src_height;
			$dest_width = $this->getDestinationImageWidth() * $dest_proportion - $this->getDestinationImagePadding();
			$dest_height = $dest_height - $this->getDestinationImagePadding();
		}
		$dest_x = ($this->getDestinationImageWidth() / 2) - ($dest_width / 2);
		$dest_y = ($this->getDestinationImageHeight() / 2) - ($dest_height / 2);
		
		$dest_img = imagecreatetruecolor($this->getDestinationImageWidth(), $this->getDestinationImageHeight());
		imagealphablending($dest_img, true); // setting alpha blending on
		imagesavealpha($dest_img, true); // save alphablending setting (important)
		$background = imagecolorallocatealpha($dest_img, 255, 255, 255, 127);
		imagefill($dest_img, 0, 0, $background);
		imagecopyresampled($dest_img, $src_img, $dest_x, $dest_y, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
		ob_start();
		imagepng($dest_img);
		$image_output = ob_get_clean();
		return $image_output; 
	}
	
	/**
	 * Resizes an image and returns the image contents
	 * @param string $image_location
	 * @param integer $dest_width
	 * @param integer $dest_height
	 * @param integer $dest_padding
	 * @return string
	 */
	static function resizeImage($image_location, $dest_width = 128, $dest_height = 128, $dest_padding = 5) {
		$image_resize = new ImageResize();
		$image_resize->setSourceImageLocation($image_location);
		$image_resize->setDestinationImageHeight($dest_height);
		$image_resize->setDestinationImageWidth($dest_width);
		$image_resize->setDestinationImagePadding($dest_padding);
		return $image_resize->resize();
	}
	
	/**
	 * Resizes an image and returns the image contents
	 * @param string $image_location
	 * @param integer $dest_width
	 * @param integer $dest_height
	 * @param integer $dest_padding
	 * @return string
	 */
	static function resizeImageFromString($image_source_contents, $dest_width = 128, $dest_height = 128, $dest_padding = 5) {
		$image_resize = new ImageResize();
		$image_resize->setDestinationImageHeight($dest_height);
		$image_resize->setDestinationImageWidth($dest_width);
		$image_resize->setDestinationImagePadding($dest_padding);
		return $image_resize->resize($image_source_contents);
	}
}
?>
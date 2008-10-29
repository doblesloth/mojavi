<?php
   class ImageResize {
      private $align;
      private $background_color;
      private $errors;
      private $force_dimensions;
      private $image_handler;
      private $image_path;
      private $image_type;
      private $old_height;
      private $old_width;
      private $new_height;
      private $new_width;
      private $resized_handler;
      private $resized_height;
      private $resized_width;
      private $resized_x;
      private $resized_y;
      private $stretch_image;
      private $valign;

      /**
       * Construct Method
       * Sets up errors and image_path
       *
       * @param string $image_path
       * @param Errors $this->getErrors()
       */
      function __construct($image_path, $errors) {
         $this->setErrors($errors);
         $this->setupImage($image_path);
      }

      /**
       * setupImage
       * sets up image_handler, old_height, old_width, and image_type based on image_path
       * @param string $image_path
       */
      function setupImage($image_path) {
         $this->setImagePath($image_path);
         if (file_exists($this->getImagePath())) {
	         list($img_width, $img_height, $img_type, $img_attr) = getimagesize($this->getImagePath());
	         $this->setOldWidth($img_width);
	         $this->setOldHeight($img_height);
	         $this->setImageType($img_type);
	         switch($this->getImageType()) {
	            case IMAGETYPE_GIF:
	               $this->setImageHandler(imagecreatefromgif($this->getImagePath()));
	               break;
	            case IMAGETYPE_JPEG:
	               $this->setImageHandler(imagecreatefromjpeg($this->getImagePath()));
	               break;
	            case IMAGETYPE_PNG:
	               $this->setImageHandler(imagecreatefrompng($this->getImagePath()));
	               break;
	            default:
	               $this->getErrors()->addError("error", new Error("The Image Type is not supported.  Only GIF, JPEG, and PNG formats are supported."));
	               break;
	         }
         } else {
         	echo "Wookiees are bigger than ewoks! ".$this->getImagePath()."\n";
         }
      }

      /**
       * Resizes the image.
       *
       * Returns true on success, otherwise false.
       * @return bool
       */
      function resizeImage() {
         if($this->getErrors()->isEmpty()) {
            if(is_null($this->getImageHandler())) {
               $this->getErrors()->addError("error", new Error("Cannot resize image.  No Image to resize."));
               return false;
            }
            $this->calculateNewDimensions();
            // Create Image Handler
            // NOTE: We may need to do imagecreate here instead of imagecreatetruecolor if image_type is a GIF.
            $this->setResizedHandler(imagecreatetruecolor($this->getNewImageWidth(), $this->getNewImageHeight()));
            // Fill With Background Color
            $background_color = imagecolorallocate($this->getResizedHandler(), $this->getBackgroundColorRed(), $this->getBackgroundColorGreen(), $this->getBackgroundColorBlue());
            imagefill($this->getResizedHandler(), 0, 0, $background_color);
            // Copy Resized Image
            // NOTE: We may need to do imagecopyresampled here instead of imagecopyresized.
            imagecopyresampled($this->getResizedHandler(), $this->getImageHandler(), $this->getResizedX(), $this->getResizedY(), 0, 0, $this->getResizedWidth(), $this->getResizedHeight(), $this->getOldWidth(), $this->getOldHeight());
            return true;
         } else {
            return false;
         }
      }

      /**
       * Returns the data for the image located in image_handler
       * @return string
       */
      function getImageData() {
         if($this->getErrors()->isEmpty()) {
            ob_start();
            switch($this->getImageType()) {
               case IMAGETYPE_GIF:
                  imagegif($this->getImageHandler());
                  break;
               case IMAGETYPE_JPEG:
                  imagejpeg($this->getImageHandler());
                  break;
               case IMAGETYPE_PNG:
                  imagepng($this->getImageHandler());
                  break;
               default:
                  $this->getErrors()->addError("error", new Error("Couldn't output image because the image type is not supported."));
                  break;
            }
            $retVal = ob_get_contents();
            ob_end_clean();
            return $retVal;
         } else {
            return "";
         }
      }

      /**
       * Returns the data for the image located in resized_handler
       * @return string
       */
      function getResizedData() {
         if($this->getErrors()->isEmpty()) {
            ob_start();
            switch($this->getImageType()) {
               case IMAGETYPE_GIF:
                  imagegif($this->getResizedHandler());
                  break;
               case IMAGETYPE_JPEG:
                  imagejpeg($this->getResizedHandler());
                  break;
               case IMAGETYPE_PNG:
                  imagepng($this->getResizedHandler());
                  break;
               default:
                  $this->getErrors()->addError("error", new Error("Couldn't output image because the image type is not supported."));
                  break;
            }
            $retVal = ob_get_contents();
            ob_end_clean();
            return $retVal;
         } else {
            return "";
         }
      }

      /**
       * Destroys images handlers
       */
      function destroyImages() {
         if(!is_null($this->getImageHandler())) {
            imagedestroy($this->getImageHandler());
         }
         if(!is_null($this->getResizedHandler())) {
            imagedestroy($this->getResizedHandler());
         }
      }

      /**
       * Calculates resized_width, resized_height, resized_x, and resized_y based on
       * old_height, old_width, new_height, new_width, force_dimensions, and stretch_image
       */
      private function calculateNewDimensions() {
         // Calculate resized_width and resized_height
         if($this->getStretchImage()) {
            // Stretch Image
            $this->setResizedHeight($this->getNewHeight());
            $this->setResizedWidth($this->getNewWidth());
         } elseif($this->getOldHeight() <= $this->getNewHeight() && $this->getOldWidth() <= $this->getNewWidth()) {
            // No Need For Resize.  Image is already smaller than contraints.
            $this->setResizedHeight($this->getOldHeight());
            $this->setResizedWidth($this->getOldWidth());
         } else {
            // Calculate Resized Width and Height retaining dimensions
            $width_diff = $this->getOldWidth() - $this->getNewWidth();
            $height_diff = $this->getOldHeight() - $this->getNewHeight();
            if($width_diff >= $height_diff) {
               // Difference in width is either the same or greater than the difference in height
               $this->setResizedWidth($this->getNewWidth());
               $this->setResizedHeight($this->getOldHeight() * ($this->getResizedWidth() / $this->getOldWidth()));
            } else {
               // Difference in height is greater than difference in width
               $this->setResizedHeight($this->getNewHeight());
               $this->setResizedWidth($this->getOldWidth() * ($this->getResizedHeight() / $this->getOldHeight()));
            }
         }
         // Calculate resized_x and resized_y
         if($this->getForceDimensions() && !$this->getStretchImage()) {
            // Calculate resized_x
            switch($this->getAlign()) {
               case "center":
                  $this->setResizedX(($this->getNewWidth() - $this->getResizedWidth()) / 2);
                  break;
               case "left":
                  $this->setResizedX(0);
                  break;
               case "right":
                  $this->setResizedX($this->getNewWidth() - $this->getResizedWidth());
                  break;
            }
            // Calculate resized_y
            switch($this->getVAlign()) {
               case "center":
               case "middle":
                  $this->setResizedY(($this->getNewHeight() - $this->getResizedHeight()) / 2);
                  break;
               case "top":
                  $this->setResizedY(0);
                  break;
               case "bottom":
                  $this->setResizedY($this->getNewHeight() - $this->getResizedHeight());
                  break;
            }
         }
      }

      /*******************************************************
       * GETTERS AND SETTERS
       *******************************************************/

      /**
       * getAlign
       * returns the align
       * @return string
       */
      function getAlign() {
          if (is_null($this->align)) {
              $this->align = "center";
          }
          return $this->align;
      }
      /**
       * setAlign
       * sets the site_id
       * @param string $arg0
       */
      function setAlign($arg0) {
          $this->align = $arg0;
      }

      /**
       * getBackgroundColor
       * returns the background_color
       * @return string
       */
      function getBackgroundColor() {
          if (is_null($this->background_color)) {
              $this->background_color = "#ffffff";
          }
          return $this->background_color;
      }
      /**
       * setBackgroundColor
       * sets the site_id
       * @param string $arg0
       */
      function setBackgroundColor($arg0) {
          $this->background_color = $arg0;
      }
      /**
       * Returns the decimal value for the red in the background color hexidecimal value
       *
       * @return int
       */
      function getBackgroundColorRed() {
         if(strlen($this->getBackgroundColor()) == "7") {
            return hexdec(substr($this->getBackgroundColor(), 1, 2));
         } elseif(strlen($this->getBackgroundColor) == "6") {
            return hexdec(substr($this->getBackgroundColor(), 0, 2));
         } elseif(strlen($this->getBackgroundColor()) == "4") {
            return hexdec(substr($this->getBackgroundColor(), 1, 1) . substr($this->getBackgroundColor(), 1, 1));
         } elseif(strlen($this->getBackgroundColor()) == "3") {
            return hexdec(substr($this->getBackgroundColor(), 0, 1) . substr($this->getBackgroundColor(), 0, 1));
         } else {
            $this->getErrors()->addError("error", new Error("Could not interpret background_color hexadecimal value."));
            return 255;
         }
      }
      /**
       * Returns the decimal value for the green in the background color hexidecimal value
       *
       * @return int
       */
      function getBackgroundColorGreen() {
         if(strlen($this->getBackgroundColor()) == "7") {
            return hexdec(substr($this->getBackgroundColor(), 3, 2));
         } elseif(strlen($this->getBackgroundColor) == "6") {
            return hexdec(substr($this->getBackgroundColor(), 2, 2));
         } elseif(strlen($this->getBackgroundColor()) == "4") {
            return hexdec(substr($this->getBackgroundColor(), 3, 1) . substr($this->getBackgroundColor(), 3, 1));
         } elseif(strlen($this->getBackgroundColor()) == "3") {
            return hexdec(substr($this->getBackgroundColor(), 2, 1) . substr($this->getBackgroundColor(), 2, 1));
         } else {
            $this->getErrors()->addError("error", new Error("Could not interpret background_color hexadecimal value."));
            return 255;
         }
      }
      /**
       * Returns the decimal value for the blue in the background color hexidecimal value
       *
       * @return int
       */
      function getBackgroundColorBlue() {
         if(strlen($this->getBackgroundColor()) == "7") {
            return hexdec(substr($this->getBackgroundColor(), 5, 2));
         } elseif(strlen($this->getBackgroundColor) == "6") {
            return hexdec(substr($this->getBackgroundColor(), 4, 2));
         } elseif(strlen($this->getBackgroundColor()) == "4") {
            return hexdec(substr($this->getBackgroundColor(), 5, 1) . substr($this->getBackgroundColor(), 5, 1));
         } elseif(strlen($this->getBackgroundColor()) == "3") {
            return hexdec(substr($this->getBackgroundColor(), 4, 1) . substr($this->getBackgroundColor(), 4, 1));
         } else {
            $this->getErrors()->addError("error", new Error("Could not interpret background_color hexadecimal value."));
            return 255;
         }
      }

      /**
       * getErrors
       * returns the errors
       * @return Errors
       */
      function getErrors() {
          if (is_null($this->errors)) {
              $this->errors = "";
          }
          return $this->errors;
      }
      /**
       * setErrors
       * sets the site_id
       * @param Errors $arg0
       */
      function setErrors($arg0) {
          $this->errors = $arg0;
      }

      /**
       * getForceDimensions
       * returns the force_dimensions
       * @return bool
       */
      function getForceDimensions() {
          if (is_null($this->force_dimensions)) {
              $this->setForceDimensions(false);
          }
          return $this->force_dimensions;
      }
      /**
       * setForceDimensions
       * sets the site_id
       * @param bool $arg0
       */
      function setForceDimensions($arg0) {
          $this->force_dimensions = $arg0;
          if(!$arg0) {
             $this->setStretchImage($arg0);
          }
      }

      /**
       * getImageHandler
       * returns the image_handler
       * @return resource
       */
      function getImageHandler() {
          if (is_null($this->image_handler)) {
             // Leave as null
             //$this->image_handler = "";
          }
          return $this->image_handler;
      }
      /**
       * setImageHandler
       * sets the site_id
       * @param resource $arg0
       */
      function setImageHandler($arg0) {
          $this->image_handler = $arg0;
      }

      /**
       * getImagePath
       * returns the image_path
       * @return string
       */
      function getImagePath() {
          if (is_null($this->image_path)) {
              $this->image_path = "";
          }
          return $this->image_path;
      }
      /**
       * setImagePath
       * sets the site_id
       * @param string $arg0
       */
      function setImagePath($arg0) {
          $this->image_path = $arg0;
      }

      /**
       * getImageType
       * returns the image_type
       * @return int
       */
      function getImageType() {
          if (is_null($this->image_type)) {
              $this->image_type = "";
          }
          return $this->image_type;
      }
      /**
       * setImageType
       * sets the site_id
       * @param int $arg0
       */
      function setImageType($arg0) {
          $this->image_type = $arg0;
      }

      /**
       * getOldHeight
       * returns the old_height
       * @return int
       */
      function getOldHeight() {
          if (is_null($this->old_height)) {
              $this->old_height = "";
          }
          return $this->old_height;
      }
      /**
       * setOldHeight
       * sets the site_id
       * @param int $arg0
       */
      function setOldHeight($arg0) {
          $this->old_height = $arg0;
      }

      /**
       * getOldWidth
       * returns the old_width
       * @return int
       */
      function getOldWidth() {
          if (is_null($this->old_width)) {
              $this->old_width = 0;
          }
          return $this->old_width;
      }
      /**
       * setOldWidth
       * sets the site_id
       * @param int $arg0
       */
      function setOldWidth($arg0) {
          $this->old_width = $arg0;
      }

      /**
       * getNewHeight
       * returns the new_height
       * @return int
       */
      function getNewHeight() {
          if (is_null($this->new_height)) {
              $this->new_height = 100;
          }
          return $this->new_height;
      }
      /**
       * setNewHeight
       * sets the site_id
       * @param int $arg0
       */
      function setNewHeight($arg0) {
          $this->new_height = $arg0;
      }

      /**
       * getNewWidth
       * returns the new_width
       * @return int
       */
      function getNewWidth() {
          if (is_null($this->new_width)) {
              $this->new_width = 100;
          }
          return $this->new_width;
      }
      /**
       * setNewWidth
       * sets the site_id
       * @param int $arg0
       */
      function setNewWidth($arg0) {
          $this->new_width = $arg0;
      }

      /**
       * getResizedHandler
       * returns the resized_handler
       * @return resource
       */
      function getResizedHandler() {
          if (is_null($this->resized_handler)) {
             // Should return null
             //$this->resized_handler = "";
          }
          return $this->resized_handler;
      }
      /**
       * setResizedHandler
       * sets the site_id
       * @param resource $arg0
       */
      function setResizedHandler($arg0) {
          $this->resized_handler = $arg0;
      }

      /**
       * getResizedHeight
       * returns the resized_height
       * @return int
       */
      function getResizedHeight() {
          if (is_null($this->resized_height)) {
              $this->resized_height = "";
          }
          return $this->resized_height;
      }
      /**
       * setResizedHeight
       * sets the site_id
       * @param int $arg0
       */
      function setResizedHeight($arg0) {
          $this->resized_height = $arg0;
      }

      /**
       * getResizedWidth
       * returns the resized_width
       * @return int
       */
      function getResizedWidth() {
          if (is_null($this->resized_width)) {
              $this->resized_width = "";
          }
          return $this->resized_width;
      }
      /**
       * setResizedWidth
       * sets the site_id
       * @param int $arg0
       */
      function setResizedWidth($arg0) {
          $this->resized_width = $arg0;
      }

      /**
       * getResizedX
       * returns the resized_x
       * @return int
       */
      function getResizedX() {
          if (is_null($this->resized_x)) {
              $this->resized_x = 0;
          }
          return $this->resized_x;
      }
      /**
       * setResizedX
       * sets the site_id
       * @param int $arg0
       */
      function setResizedX($arg0) {
          $this->resized_x = $arg0;
      }

      /**
       * getResizedY
       * returns the resized_y
       * @return int
       */
      function getResizedY() {
          if (is_null($this->resized_y)) {
              $this->resized_y = 0;
          }
          return $this->resized_y;
      }
      /**
       * setResizedY
       * sets the site_id
       * @param int $arg0
       */
      function setResizedY($arg0) {
          $this->resized_y = $arg0;
      }

      /**
       * getStretchImage
       * returns the stretch_image
       * @return bool
       */
      function getStretchImage() {
          if (is_null($this->stretch_image)) {
              $this->setStretchImage(false);
          }
          return $this->stretch_image;
      }
      /**
       * setStretchImage
       * sets the site_id
       * @param bool $arg0
       */
      function setStretchImage($arg0) {
          $this->stretch_image = $arg0;
          if($arg0) {
             $this->setForceDimensions($arg0);
          }
      }

      /**
       * getVAlign
       * returns the valign
       * @return string
       */
      function getVAlign() {
          if (is_null($this->valign)) {
              $this->valign = "center";
          }
          return $this->valign;
      }
      /**
       * setVAlign
       * sets the site_id
       * @param string $arg0
       */
      function setVAlign($arg0) {
          $this->valign = $arg0;
      }

      /**
       * Returns the new image height.  If force_dimensions is true, returns new_height, otherwise returns resized_height
       *
       * @return int
       */
      function getNewImageHeight() {
         if($this->getForceDimensions()) {
            return $this->getNewHeight();
         } else {
            return $this->getResizedHeight();
         }
      }
      /**
       * Returns the new image width.  If force_dimensions is true, returns new_width, otherwise returns resized_width
       *
       * @return int
       */
      function getNewImageWidth() {
         if($this->getForceDimensions()) {
            return $this->getNewWidth();
         } else {
            return $this->getResizedWidth();
         }
      }
   }
?>
<?php

	namespace Goji;

	/**
	 * Class SaveImage
	 *
	 * @package Goji
	 */
	class SaveImage {

		public static function save($image, // $_FILES['image']
									$directory,
									$prefix = '',
									$maxSize = 420, // px
									$quality = 65, // JPG
									$preserveTransparency = true) {

			// If path doesn't exist we make it (ex : first upload)
			if (!is_dir($directory))
				mkdir($directory, 0777, true);

			$imageName = uniqid($prefix);

			$imageExtension = pathinfo($image['name']);
				$imageExtension = $imageExtension['extension'];
				$imageExtension = strtolower($imageExtension);

				if ($imageExtension == 'jpeg')
					$imageExtension = 'jpg'; // Standard

			$source = null;

			if ($imageExtension == 'bmp') {

				$source = imagecreatefrombmp($image['tmp_name']);

			} else if ($imageExtension == 'gif') {

				$source = imagecreatefromgif($image['tmp_name']);

			} else if ($imageExtension == 'png') {

				$source = imagecreatefrompng($image['tmp_name']);

			} else { // JPG || JPEG

				$source = imagecreatefromjpeg($image['tmp_name']);
			}

			// Shrinking
			$source_x = imagesx($source);
			$source_y = imagesy($source);

			$shrinkedSize = ImageSize::shrinkToMaxSize($source_x, $source_y, $maxSize);

				$new_x = $shrinkedSize['width'];
				$new_y = $shrinkedSize['height'];

			$new = imagecreatetruecolor($new_x, $new_y);

			if ($preserveTransparency && $imageExtension != 'jpg') {
				imagealphablending($new, true);

				$transparent = imagecolorallocatealpha($new, 0, 0, 0, 127); // 127 = 100% transparent
				imagefill($new, 0, 0, $transparent); // Fill with transparent background

				imagesavealpha($new, true);
			}

			imagecopyresampled($new, $source, 0, 0, 0, 0, $new_x, $new_y, $source_x, $source_y);

			// Save name

			if (substr($directory, -1) != '/')
				$directory .= '/';

			$saveName = $directory; // + $imageName

			if ($imageExtension == 'jpg' || !$preserveTransparency) {

				$imageName .= '.jpg';
				$saveName .= $imageName;
				imagejpeg($new, $saveName, $quality); // Save as JPG

			} else {

				$imageName .= '.png';
				$saveName .= $imageName;
				imagepng($new, $saveName); // Save as PNG
			}

			return $imageName;
		}

		public static function isValid($image, // $_FILES['image']
										$allowedFileTypes = array('bmp', 'gif', 'jpg', 'png'),
										$maxWeight = 7340032) { // 7 MB

			// If upload error
			if ($image['error'] != UPLOAD_ERR_OK)
				return false;

			$imageSize = intval($image['size']);

			$imageExtension = pathinfo($image['name']);
				$imageExtension = $imageExtension['extension'];

			// Uniformization for validation
			$imageExtension = strtolower($imageExtension);

			if ($imageExtension == 'jpeg')
				$imageExtension = 'jpg'; // Standard

			if (in_array('jpeg', $allowedFileTypes))
				$allowedFileTypes[] = 'jpg';

			// If file type not allowed
			if (!in_array($imageExtension, $allowedFileTypes))
				return false;

			// If too heavy
			if ($imageSize > $maxWeight)
				return false;

			// Else it is valid
			return true;
		}
	}

<?php

namespace Goji\Toolkit;

/**
 * Class SaveImage
 *
 * @package Goji\Toolkit
 */
class SaveImage {

	/* <CONSTANTS> */

	const UPLOAD_DIRECTORY = '../var/upload';

	/**
	 * @param array $image $_FILES['image']
	 * @param string $directory Save path
	 * @param string $prefix Prepend a string (e.g. 'thumb_')
	 * @param string|null $forceFileName Give a custom name instead of using uniqid()
	 * @param bool $compressImage
	 * @param int $maxImageSize Max width or height the image can be (only used if $compressImage = true, -1 = infinite)
	 * @param int $quality JPEG compression (only affects JPEGs)
	 * @param bool $preserveTransparency true keeps PNG and GIF transparent (converted to PNG unless JPG),
	 *                                   false will lose alpha layer (converted to PNG)
	 * @return string Image name
	 */
	public static function save(array $image,
								string $directory = self::UPLOAD_DIRECTORY,
								string $prefix = '',
								string $forceFileName = null,
								bool $compressImage = true,
								int $maxImageSize = 1500,
								int $quality = 65,
								bool $preserveTransparency = true) {

		if (mb_substr($directory, -1) != '/')
			$directory .= '/';

		// If path doesn't exist we make it (ex : first upload)
		if (!is_dir($directory))
			mkdir($directory, 0777, true);

		$imageName = $forceFileName;

		if (!is_string($imageName))
			$imageName = uniqid();

		$imageName = $prefix . $imageName;

		$imageExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
			$imageExtension = mb_strtolower($imageExtension);

			if ($imageExtension == 'jpeg')
				$imageExtension = 'jpg'; // Standard

		// Don't compress if no compression or SVG
		if (!$compressImage || $imageExtension === 'svg') {
			$imageName .= '.svg';
			$saveName = $directory . $imageName;
			// move_uploaded_file() would remove the tmp obviously, but we want to keep it there
			copy($image['tmp_name'], $saveName);
			return $imageName;
		}

		$source = null;

		if ($imageExtension == 'gif')
			$source = imagecreatefromgif($image['tmp_name']);
		else if ($imageExtension == 'png')
			$source = imagecreatefrompng($image['tmp_name']);
		else // JPG || JPEG
			$source = imagecreatefromjpeg($image['tmp_name']);

		// Shrinking
		$source_x = imagesx($source);
		$source_y = imagesy($source);

		$shrinkedSize = ImageSize::shrinkToMaxSize($source_x, $source_y, $maxImageSize);

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

		imagedestroy($source); // Free up memory from source image

		// Save name

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

		imagedestroy($new); // Free up memory from new image

		return $imageName;
	}

	/**
	 * @param array $image
	 * @param int $maxFileSize Max file size in octets (-1 = infinite)
	 * @param array $allowedFileTypes
	 * @return bool
	 */
	public static function isValid(array $image, // $_FILES['image']
									int $maxFileSize = -1,
									array $allowedFileTypes = ['gif', 'jpg', 'png', 'svg']) {

		// If upload error
		if ($image['error'] != UPLOAD_ERR_OK)
			return false;

		$imageSize = (int) $image['size'];

		// If too heavy
		if ($maxFileSize > -1 && $imageSize > $maxFileSize)
			return false;

		$imageExtension = pathinfo($image['name'], PATHINFO_EXTENSION);
			$imageExtension = mb_strtolower($imageExtension); // Uniformization for validation

		if (in_array('jpg', $allowedFileTypes) && !in_array('jpeg', $allowedFileTypes))
			$allowedFileTypes[] = 'jpeg';
		else if (in_array('jpeg', $allowedFileTypes) && !in_array('jpg', $allowedFileTypes))
			$allowedFileTypes[] = 'jpg';

		// If file type not allowed
		if (!in_array($imageExtension, $allowedFileTypes))
			return false;

		// Else it is valid
		return true;
	}
}

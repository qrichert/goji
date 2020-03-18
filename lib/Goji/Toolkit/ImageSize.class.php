<?php

namespace Goji\Toolkit;

/**
 * Class ImageSize
 *
 * @package Goji\Toolkit
 */
class ImageSize {

	/**
	 * Resize image to maxium dimensions while keeping aspect ratio.
	 *
	 * $maxSize == -1 -> Don't resize
	 *
	 * @param int $width
	 * @param int $height
	 * @param int $maxSize
	 * @return array
	 */
	public static function shrinkToMaxSize(int $width, int $height, int $maxSize) {

		if ($maxSize == -1) {
			return [
				'width'  => $width,
				'height' => $height
			];
		}

		$scaleFactor = 1;

		if ($width >= $height && $width > $maxSize) // Wider than high && Too wide
			$scaleFactor = $maxSize / $width;
		else if ($height > $maxSize) // Higher than wide && Too high
			$scaleFactor = $maxSize / $height;

		return [
			'width'  => round($width  * $scaleFactor),
			'height' => round($height * $scaleFactor)
		];
	}
}

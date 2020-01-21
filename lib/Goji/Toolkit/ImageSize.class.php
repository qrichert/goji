<?php

namespace Goji\Toolkit;

/**
 * Class ImageSize
 *
 * @package Goji\Toolkit
 */
class ImageSize {

	public static function shrinkToMaxSize($width, $height, $maxSize) {

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

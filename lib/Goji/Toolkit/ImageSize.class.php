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

			if ($width >= $height
				&& $width > $maxSize) {

				$scaleFactor = $maxSize / $width;

			} else if ($height > $width
				&& $height > $maxSize) {

				$scaleFactor = $maxSize / $height;
			}

			return [
				'width'  => round($width  * $scaleFactor),
				'height' => round($height * $scaleFactor)
			];
		}
	}

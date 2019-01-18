<?php

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

			return array(
				'width'  => round($width  * $scaleFactor),
				'height' => round($height * $scaleFactor)
			);
		}
	}

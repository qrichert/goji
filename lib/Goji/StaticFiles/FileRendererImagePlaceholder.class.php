<?php

namespace Goji\StaticFiles;

use Goji\Core\HttpResponse;
use Goji\Toolkit\SwissKnife;

class FileRendererImagePlaceholder extends FileRendererAbstract {

	public function renderMerged() {
		$this->renderFlat();
	}

	public function renderFlat() {

		HttpResponse::setContentType('image/jpeg', null);

	// Options

		$width = (int) ($_GET['width'] ?? $_GET['w'] ?? 1920);
		$height = (int) ($_GET['height'] ?? $_GET['h'] ?? 1080);

		$backgroundColor = (string) ($_GET['background'] ?? $_GET['bg'] ?? null); // color-text-xxx(x)-light
			$backgroundColor = SwissKnife::hexColorToRGB($backgroundColor, '#cccccc');
		$foregroundColor = (string) ($_GET['foreground'] ?? $_GET['fg'] ?? null);
			$foregroundColor = SwissKnife::hexColorToRGB($foregroundColor, '#969696');

		$text = (string) ($_GET['text'] ?? $_GET['t'] ?? "{$width}×{$height}");

		$font = 'Lato-Regular';

	// Math

		$fontSize = $width / 14;

		if ($width >= $height) {
			if ($fontSize > $height * 0.3)
				$fontSize = $height * 0.3;
		} else {
			// (font size / 1.5) * text length is a pretty accurate guess of text width

			$textLength = mb_strlen($text) * 1.2; // * 1.2 for margins

			while (($fontSize / 1.5) * $textLength > $width)
				$fontSize *= 0.95;
		}

		$textBBox = imagettfbbox($fontSize, 0, $font, $text); // Get rendered text bounding box

		// (Image Size - Text Size) / 2
		$textPosX = ($width / 2) - (($textBBox[4] - $textBBox[6]) / 2);
		$textPosY = ($height / 2) + ($fontSize / 2); // /!\ Text X, Y = Bottom Left corner in imagettftext()

	// Rendering

		$placeholder = imagecreatetruecolor($width, $height);
		$backgroundColor = imagecolorallocate($placeholder, ...$backgroundColor);
		$foregroundColor = imagecolorallocate($placeholder, ...$foregroundColor);

		imagefill($placeholder, 0, 0, $backgroundColor);
		imagettftext($placeholder, $fontSize, 0, $textPosX, $textPosY, $foregroundColor, $font, $text);

		imagejpeg($placeholder);
		imagedestroy($placeholder);
		exit;
	}
}

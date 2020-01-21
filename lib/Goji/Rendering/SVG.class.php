<?php

namespace Goji\Rendering;

use Exception;

/**
 * Class SVG
 *
 * @package Goji\Rendering
 */
class SVG {

	/* <CONSTANTS> */

	const E_FILE_CANNOT_BE_READ = 0;

	/**
	 * Outputs SVG code starting at <svg... (without XML declaration)
	 *
	 * @param $file
	 * @throws \Exception
	 */
	public static function includeFile($file): void {

		$f = fopen($file, 'r');

		if ($f === false)
			throw new Exception("Cannot open SVG file for reading: '$file'", self::E_FILE_CANNOT_BE_READ);

		while ($line = fgets($f)) {

			$svgTagPos = mb_stripos($line, '<svg '); // mb_stripos() == mb_strpos() but case insensitive

			if ($svgTagPos !== false) {

				echo mb_substr($line, $svgTagPos);
				fpassthru($f);

				break;
			}
		}

		fclose($f);
	}
}

<?php

	namespace Goji\Toolkit;

	/**
	 * Class SVG
	 *
	 * @package Goji\Toolkit
	 */
	class SVG {

		/* <CONSTANTS> */

		const E_FILE_CANNOT_BE_READ = 0;

		/**
		 * Outputs SVG code starting at <svg... (without XML declaration)
		 *
		 * @param $file
		 */
		public static function includeFile($file): void {

			$f = fopen($file, 'r');

			if ($f === false)
				return;

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

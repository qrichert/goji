<?php

	namespace Goji\Parsing;

	use Exception;

	/**
	 * Class SimpleMinifierAbstract
	 *
	 * @package Goji\Parsing
	 */
	abstract class SimpleMinifierAbstract {

		/* <CONSTANTS> */

		const E_FILE_NOT_FOUND = 0;

		/**
		 * @param string $code
		 * @return string
		 */
		abstract public static function minify(string $code): string;

		/**
		 * @param array|string $file
		 * @return string|null
		 * @throws \Exception
		 */
		public static function minifyFile($file): ?string { // $file = (string) | (array)

			$code = '';

			$file = (array) $file;

			foreach ($file as $f) {

				if (is_file($f))
					$code .= file_get_contents($f);
				else
					throw new Exception("File not found: $f", self::E_FILE_NOT_FOUND);
			}

			if (!empty($code))
				return static::minify($code);
			else
				return null;
		}

		/**
		 * Returns full path of a file starting at the web root (file name not included).
		 *
		 * Web root must be in a folder called 'public'
		 *
		 * If you call multiple files like css/main.css|css/responsive.css
		 * The browser messes up and tries to load external files like this :
		 * /css/main.css|css/responsive.css
		 *
		 * It invents a folder called 'main.css|css'
		 * So we transform relative paths like 'img/' to absolute paths like '/WEBROOT/css/img/'
		 *
		 * @param string $file
		 * @return string
		 */
		protected static function getWebRootPath(string $file): string {
			return WEBROOT . '/' . dirname($file) . '/';
		}
	}

<?php

	namespace Goji;

	/**
	 * Class SimpleMinifierAbstract
	 *
	 * @package Goji
	 */
	abstract class SimpleMinifierAbstract {

		abstract public static function minify($code);

		public static function minifyFile($file) { // $file = (string) | (array)

			$code = '';

			if (is_array($file)) {

				foreach ($file as $f) {

					if (is_file($f)) {

						$code .= file_get_contents($f);
					}
				}

			} else {

				if (is_file($file))
					$code = file_get_contents($file);
			}

			if (!empty($code))
				return static::minify($code);
			else
				return null;
		}

		/*
			Returns full path of a file starting at the web root (file name not included).
			Web root must be in a folder called 'public'
		*/
		protected static function getWebRootPath($file) {

			/*
				If you call multiple files like css/main.css|css/responsive.css
				The browser messes up and tries to load external files like this :
				/css/main.css|css/responsive.css
				It invents a folder called 'main.css|css'
				So we transform relative paths like 'img/' to absolute paths like '/WEBROOT/css/img/'
			*/

			// Get request path (ex: '/WEBROOT/folder/public/css/main.css|css/responsive.css')
			$path = '';

			if (strpos($_SERVER['REQUEST_URI'], 'public/') !== false) { // We're in a sub folder (www.domain.com/testsite/public/)

				// We need to extract the whole path until public/
				$path = $_SERVER['REQUEST_URI']; // /WEBROOT/folder/public/css/main.css|css/responsive.css
				$path = substr($path, 0, strpos($path, '/public/')) . '/public'; // /WEBROOT/folder/public

				$dir = dirname($file); // css/main.css -> css

				if ($dir[0] != '/') // Make it absolute unless it is already (css -> /css)
					$dir = '/' . $dir;

				$path = $path . $dir;

				// $path = /subfolder/public/css

			} else { // We're on WEBROOT (www.domain.com/)

				$path = dirname($file); // css/main.css -> css

				if ($path[0] != '/') // Make it absolute unless it is already
					$path = '/' . $path;

				// $path = /css
			}

			if (substr($path, -1) != '/')
				$path = $path . '/';

			return $path;
		}
	}

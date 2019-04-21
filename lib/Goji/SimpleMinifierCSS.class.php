<?php

	namespace Goji;

	/**
	 * Class SimpleMinifierCSS
	 *
	 * @package Goji
	 */
	class SimpleMinifierCSS extends SimpleMinifierAbstract {

		public static function minify($code, $replaceCSSVariablesByValue = true) {

			$cssUnits = implode('|', array('cm', 'mm', 'in', 'px', 'pt', 'pc', 'ex', 'ch',
										   'em', 'rem', 'vw', 'vh', 'vmin', 'vmax', '%',
										   'deg', 'ms', 's'));

			// Remove comments
  			$code = preg_replace('#/\*[^*]*\*+([^/][^*]*\*+)*/#', '', $code); // Multi line : /* hello, world */

			// Backup values within single or double quotes
			preg_match_all('#(\'[^\']*?\'|"[^"]*?")#ims', $code, $hit, PREG_PATTERN_ORDER);

			for ($i = 0; $i < count($hit[1]); $i++) {
				$code = str_replace($hit[1][$i], '##########' . $i . '##########', $code);
			}

			// Remove ';' of last property
			$code = preg_replace('#;[\s\r\n\t]*?}[\s\r\n\t]*#ims', "}\r\n", $code);
			// Remove white-space between ';' and property
			$code = preg_replace('#;[\s\r\n\t]*?([\r\n]?[^\s\r\n\t])#ims', ';$1', $code);
			// Remove white-space around ':' (not selectors ! only those followed by a space)
			$code = preg_replace('#[\s\r\n\t]*:[\s\r\n\t]+([^\s\r\n\t])#ims', ':$1', $code);
			// Remove white-space around ','
			$code = preg_replace('#[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])#ims', ',$1', $code);
			// Remove white-space around '{'
			$code = preg_replace('#[\s\r\n\t]*{[\s\r\n\t]*?([^\s\r\n\t])#ims', '{$1', $code);
			// Remove white-space around '}'
			$code = preg_replace('#[\s\r\n\t]*\}[\s\r\n\t]*?([^\s\r\n\t])#ims', '}$1', $code);
			// Remove white-space around '['
			$code = preg_replace('#[\s\r\n\t]*\[[\s\r\n\t]*?([^\s\r\n\t])#ims', '[$1', $code);
			// Remove white-space around ']'
			$code = preg_replace('#[\s\r\n\t]*\][\s\r\n\t]*?([^\s\r\n\t])#ims', ']$1', $code);
			// Remove white-space around '='
			$code = preg_replace('#[\s\r\n\t]*=[\s\r\n\t]*?([^\s\r\n\t])#ims', '=$1', $code);
			// Remove white-space around '>'
			$code = preg_replace('#[\s\r\n\t]*>[\s\r\n\t]*?([^\s\r\n\t])#ims', '>$1', $code);
			// Remove white-space around '~'
			$code = preg_replace('#[\s\r\n\t]*~[\s\r\n\t]*?([^\s\r\n\t])#ims', '~$1', $code);
			// Remove white-space around '|'
			$code = preg_replace('#[\s\r\n\t]*\|[\s\r\n\t]*?([^\s\r\n\t])#ims', '|$1', $code);
			// Remove white-space around '$'
			$code = preg_replace('#[\s\r\n\t]*\$[\s\r\n\t]*?([^\s\r\n\t])#ims', '$$1', $code);
			// Don't remove white-space around '*', could be a selector: nav * a
			// Same for calc() operators +, -, / etc.
			// Remove white-space between numbers and units
			$code = preg_replace('#([\d\.]+)[\s\r\n\t]+(' . $cssUnits . ')#ims', '$1$2', $code);
			// Replace '0px' by 0 -> Nope, because of calc(0px - var()); doesn't work with calc(0 - var());
			//$code = preg_replace('#([\D]0)px#ims', '$1', $code);
			// Remove redundant white-space
			$code = preg_replace('#\p{Zs}+#ims', ' ', $code);
			// Remove new lines
			$code = str_replace(array("\r\n", "\r", "\n", PHP_EOL), '', $code);

			/*
				Limitations of this algorithm:
				------------------------------

				/!\ Variables must be treated as constants /!\

					Declare them in :root only. Values cannot be changed.

					This code will hard-write the variables into the code.

					So if you have :

					:root { --color: red; }
					elem { color: var(--color); }
					@media { :root { --color: blue; } }

					The result will look like this :

					:root{}
					elem{color:red;}
					@media{:root{}}

					So the color will not be changeable by altering the variable value like normal.

				/!\ Merge files for multi-file variables /!\

					If variables must spread across files, you must combine all thos files into one.
					Simply join them with '|' like 'src="css/main.css|css/responsive.css"'

				/!\ If you need these functionalities, don't replace variables by value /!\

					Make sure $replaceCSSVariablesByValue is set to false when calling minify():

					minify($code, false)

					Replacing variables by value is mainly useful for IE which does not support them.
					If you need them but still want to support IE, use polyfills instead.
			*/
			if ($replaceCSSVariablesByValue) {

				// Replace variables (custom properties) by their values
				while (preg_match('#[\s\r\n\t{;](--[a-z][a-z0-9-_]*)\s*:\s*?(.+)(?:;|})#isU', $code, $var)) {

					// --var-name: value;
					// ((--var-name): (value);)
					// $0 = --var-name: value; // whole match
					// $1 = --var-name // variable name
					// $2 = value // variable value

					// To make sure the regex does not match in this case:
					// nav__header--big:hover {} (--big: hover)
					// We make sure the character that comes just before '--' is not selector material
					// This character gets included in the matched string however, and we must delete it
					$var[0] = substr($var[0], 1);

					// If --var is the last property it may not have an end ';' and the regex will end on '}' instead
					// We don't want to delete '}' in the next step, so we remove it
					if (substr($var[0], -1) == '}')
						$var[0] = substr($var[0], 0, -1);

					// Replace var(--var-name) by value in whole file
					$code = preg_replace('#var\s*\(\s*' . $var[1] . '\s*\)#imU', $var[2], $code);
					// Remove variable declaration
					$code = str_replace($var[0], '', $code);
				}
			}

			/*
				Leave '(' && ')', calc() needs white-spaces, it's too much a hassle to handle that
				They are not very used anyway and most of them will be taken care of
				thanks to surrounding elements like ') ;' -> ');'

				// We want to remove '(' && ')' AFTER variables have been deleted, otherwise things like these will happen:
				// margin: 0 0 var(--gutter-default) 0;
				// margin: 0 0 var(--gutter-default)0; -> removed spaces around ')'
				// margin: 0 0 20px0; -> replaced variable

				// Remove white-space around '('
				$code = preg_replace('#[\s\r\n\t]*\([\s\r\n\t]*?([^\s\r\n\t])#ims', '($1', $code);
				// Remove white-space around ')'
				$code = preg_replace('#[\s\r\n\t]*\)[\s\r\n\t]*?([^\s\r\n\t])#ims', ')$1', $code);
			*/

			// Restore backupped values within single or double quotes
			for ($i = 0; $i < count($hit[1]); $i++) {
				$code = str_replace('##########' . $i . '##########', $hit[1][$i], $code);
			}

			// Charset: MUST be at the beginning (first char) + follow exact format: @charset "<charset>";
			if (preg_match('#@charset\s+[\'"](?:.+)[\'"]\s*;#imU', $code)) {

				// Extract first charset
				preg_match('#@charset\s+[\'"](.+)[\'"]\s*;#imU', $code, $charset);
				$charset = $charset[1]; // @charset 'utf-8'; -> utf-8
				$charset = '@charset "' . $charset . '";'; // utf-8 -> @charset "utf-8"; (with correct syntax)

				// Remove all @charset
				$code = preg_replace('#@charset\s+[\'"](?:.+)[\'"]\s*;#imU', '', $code);

				// Add charset back at start of file
				$code = $charset . $code;
			}

			return $code;
		}

		public static function minifyFile($file, $replaceCSSVariablesByValue = true) { // $file = (string) | (array)

			$code = '';

			if (is_array($file)) {

				foreach ($file as $f) {

					if (is_file($f)) {

						$path = self::getWebRootPath($f);

						if (substr($path, -3) == '/./') // public/./ (if file is at root)
							$path = substr($path, 0, strlen($path) - 2); // Remove ./

						$content = file_get_contents($f);

						// Now we make every url('') that is NOT absolute, absolute
						// Can start with @import url(URL) || @import (URL) || url(URL)
						// URL may be sourrounded by quotes " or ' but not necessarily
						// URL can not start with a protocol '[a-z]{1,5}://', 'data:', '/'

						// Match @import url() || @import || url() -- If parentheses quotes are optional
						preg_match_all('#(@import\s+url|@import|url)\s*(?:\(?\s*(\'[^\']*?\'|"[^"]*?")\s*\)?|\(\s*(.+?)\s*\))#ims', $content, $hit, PREG_PATTERN_ORDER);

						for ($i = 0; $i < count($hit[0]); $i++) {
							// $hit[0] = full match
							// $hit[1] = first capturing group (@import url|@import|@url)
							// $hit[2] = second capturing group URL w/ quotes (parentheses optional)
							// $hit[3] = third capturing group URL w/o quotes (if only parentheses, no quotes)

							$fullMatch = $hit[0][$i]; // @import url('css/main.css')

							$originalUrl = !empty($hit[2][$i]) ? $hit[2][$i] : $hit[3][$i]; // 'css/main.css'

							$url = $originalUrl;
								$url = str_replace("'", '', $url); // css/main.css
								$url = str_replace('"', '', $url);

							// Ignore if if starts with protocol, date: or a slash (absolute path)
							if (preg_match('#^([a-z]{1,5}:\/\/|data:|/)#i', $url))
								continue;

							$url = $path . $url; // /ROOT/css/main.css

							$url = "'" . $url . "'"; // '/ROOT/css/main.css'

							$fullMatch = str_replace($originalUrl, $url, $fullMatch);

							$content = str_replace($hit[0][$i], $fullMatch, $content);
						}

						$code .= $content;
					}
				}

			} else {

				if (is_file($file))
					$code = file_get_contents($file);
			}

			if (!empty($code))
				return self::minify($code, $replaceCSSVariablesByValue);
			else
				return null;
		}
	}

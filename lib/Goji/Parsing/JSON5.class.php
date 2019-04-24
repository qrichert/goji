<?php

	namespace Goji\Parsing;

	class JSON5 {

		/**
		 * Converts JSON5 string to regular JSON.
		 *
		 * Doesn't preserve aspect.
		 *
		 * @param string $json5
		 * @return string
		 */
		public static function toJSON($json5) {

			// Backup values within single or double quotes
			preg_match_all(RegexPatterns::quotedStrings(), $json5, $hit, PREG_PATTERN_ORDER);

			$hitCount = count($hit[1]);
			for ($i = 0; $i < $hitCount; $i++) {
				$json5 = str_replace($hit[1][$i], '##########' . $i . '##########', $json5);
			}

			// Remove comments
			$json5 = preg_replace('#/\*[^*]*\*+([^/][^*]*\*+)*/#', '', $json5); // Multi line : /* hello, world */
			$json5 = preg_replace('#//.*$#m', '', $json5); // Single line : // hello, world

			// Now we make strings (quotes values) comply by:
			// Replacing single quotes with double quotes
			for ($i = 0; $i < $hitCount; $i++) {

				// Now quotes
				$quoteType = mb_substr($hit[1][$i], 0, 1);

				// We don't want single quotes, double quotes are fine
				if ($quoteType == "'") {

					// Remove first and last character (i.e. single quotes)
					$hit[1][$i] = mb_substr($hit[1][$i], 1, -1);

					// Escape unsecaped double quotes
					$hit[1][$i] = preg_replace(RegexPatterns::unescapedDoubleQuotes(), '\"', $hit[1][$i]);

					// We don't need to escape single quotes anymore
					$hit[1][$i] = str_replace("\'", "'", $hit[1][$i]);

					// Replace them with double quotes
					$hit[1][$i] = '"' . $hit[1][$i] . '"';
				}

				// Remove escaped new lines \\n
				$hit[1][$i] = preg_replace(RegexPatterns::escapedNewLines(), '\\n', $hit[1][$i]);
				// Remove forbidden characters from strings
				$hit[1][$i] = preg_replace('#(\r\n|\n|\r|\t)#', '', $hit[1][$i]);
			}

			// Remove trailing commas ['hello', 'world',] -> ['hello', 'world']
			$json5 = preg_replace('#,[\s\r\n\t]*(\}|\]|\))#ims', '$1', $json5);
			// Remove leading decimal point (add 0)
			$json5 = preg_replace('#(\D)(\.\d+)#', '${1}0$2', $json5);
			// Remove trailing decimal point (add 0)
			$json5 = preg_replace('#(\d)\.(\D)#', '$1$2', $json5);
			// Remove + sign: +1337 -> 1337
			$json5 = preg_replace('#\+(\d)#', '$1', $json5);
			// Replace hexadecimal numbers y decimal numbers
			$json5 = preg_replace_callback(RegexPatterns::hexadecimalNumber(), function($matches) {
				return hexdec($matches[0]);
			}, $json5);

			// Add quotes around Identifier Names (i.e. keys)
			// In JSON it can be anything (ECMA-404) it can be anything that goes into double quotes
			// But in JSON5 (ECMAScript 5.1) it can be anything that could be a JavaScript variable name
			// which is, pretty much any character that isn't white space or a symbol that has meaning
			// like +-/\*:.;(), etc.
			// It can't start with a number (but can contain numbers)
			// It is necessarily preceded by { or ,
			$symbols = <<<'EOT'
#+-/\*:.;,()[]{}§€£~"'<>=!?@¨^
EOT;
				$symbols = str_replace(array('#', ']'), array('\#', '\]'), $symbols);

			$re = '#';
				$re .= '(\{|,|\[)(?:[\s\r\n\t\p{Zs}]|\r\n)*'; // Any { or , or [ - followed by white-space?
				$re .= '([^' . $symbols . '\s\r\n\t\p{Zs}\d' . ']'; // Followed by a character !symbols !white-space !number
				$re .= '[^' . $symbols . '\s\r\n\t\p{Zs}' . ']*)'; // Followed by anything !symbols !white-space
				$re .= '[\s\r\n\t\p{Zs}]*:'; // Followed by white-space? and colon :
				$re .= '#';

			$json5 = preg_replace($re, '$1"$2":', $json5); // { or , or [ + " + key + "

			// Restore backupped values within single or double quotes
			for ($i = 0; $i < $hitCount; $i++) {
				$json5 = str_replace('##########' . $i . '##########', $hit[1][$i], $json5);
			}

			return $json5;
		}

		/**
		 * JSON5 to array.
		 *
		 * @param string $json5
		 * @return array
		 */
		public static function decode($json5): array {
			return json_decode(self::toJSON($json5), true);
		}
	}

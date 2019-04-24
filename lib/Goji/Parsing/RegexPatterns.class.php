<?php

	namespace Goji\Parsing;

	class RegexPatterns {

		/**
		 * Matches single or double quoted strings.
		 *
		 * Implements Friedl's "unrolling-the-loop" technique.
		 *
		 * Returns:
		 * #(\'[^\'\\]*(?:\\.[^\'\\]*)*\'|\"[^\"\\]*(?:\\.[^\"\\]*)*\")#s
		 *
		 * @return string
		 */
		public static function quotedStrings(): string {

			/*
			 * Original regex from nanorc.sample: \"(\\.|[^\"])*\"
			 */
/*
			$re = <<<'EOT'
#(\'(\\.|[^\'])*\'|\"(\\.|[^\"])*\")#s
EOT;
*/
			/*
			 * This is apparently better.
			 * Implements Friedl's "unrolling-the-loop" technique: "[^"\\]*(?:\\.[^"\\]*)*"
			 */
			return <<<'EOT'
#(\'[^\'\\]*(?:\\.[^\'\\]*)*\'|\"[^\"\\]*(?:\\.[^\"\\]*)*\")#s
EOT;
		}

		/**
		 * Matches unescaped double quotes.
		 *
		 * foo \"bar" foo \\" bar
		 * -> Matches the one after bar and the one after \\ (\\" <- escapes the \, not the ")
		 * -> Doesn't match the first one \", it is escaped
		 *
		 * @return string
		 */
		public static function unescapedDoubleQuotes(): string {

			return <<<'EOT'
#(?<!\\)(?:\\{2})*\K\"#
EOT;
		}

		/**
		 * Matches hexadecimal numbers like 0xDECAF.
		 *
		 * @return string
		 */
		public static function hexadecimalNumber(): string {

			return '#0x[\da-f]+#i';
		}

		/**
		 * Matches escaped new lines.
		 *
		 * Lines that are \
		 * cut with a backslash.
		 *
		 * @return string
		 */
		public static function escapedNewLines(): string {
			return <<<'EOT'
#\\(\r\n|\n|\r)#
EOT;
		}
	}

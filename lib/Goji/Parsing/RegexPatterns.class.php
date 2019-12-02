<?php

	namespace Goji\Parsing;

	/**
	 * Class RegexPatterns
	 *
	 * @package Goji\Parsing
	 */
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
		 * Matches unescaped single quotes.
		 *
		 * foo \'bar' foo \\' bar
		 * -> Matches the one after bar and the one after \\ (\\' <- escapes the \, not the ')
		 * -> Doesn't match the first one \', it is escaped
		 *
		 * @return string
		 */
		public static function unescapedSingleQuotes(): string {

			return <<<'EOT'
				#(?<!\\)(?:\\{2})*\K\'#
				EOT;
		}

		/**
		 * Matches a group of unescaped capturing parenthesis.
		 *
		 * some-other-page-([0-9]+)-(?:-([0-9]+\)))?
		 * -> Matches ([0-9]+) and ([0-9]+\))
		 * -> (?: is non capturing and \) is escaped
		 *
		 * @return string
		 */
		public static function unescapedParenthesisGroups(): string {

			return <<<'EOT'
				#((?<!\\)(?:\\{2})*\K\((?!\?:).*(?<!\\)(?:\\{2})*\K\))#U
				EOT;
		}

		/**
		 * Matches unscaped metacharacters.
		 *
		 * (?: (?! (?= (?<! (?<= |?*+.()[]{}
		 *
		 * @return string
		 */
		public static function unescapedMetacharacters(): string {

			return <<<'EOT'
				#(?<!\\)(?:\\{2})*\K(\(\?:|\(\?!|\(\?=|\(\?<!|\(\?<=|[|?*+.()[\]{}])#U
				EOT;
		}

		/**
		 * Matches C style multiline comments.
		 *
		 * /* comment * /
		 *
		 * @return string
		 */
		public static function multiLineCStyleComments(): string {

			return '#/\*[^*]*\*+([^/][^*]*\*+)*/#';
		}

		/**
		 * Matches C style single-line comments.
		 *
		 * // comment
		 *
		 * @return string
		 */
		public static function singleLineCStyleComments(): string {

			return '#//.*$#m';
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

		/**
		 * Matches white space.
		 *
		 * @return string
		 */
		public static function whiteSpace(): string {

			return <<<'EOT'
				#[\s\r\n\t\p{Z}]+#
				EOT;
		}

		/**
		 * <input name="foo[bar][baz][]'>
		 *
		 * @return string
		 */
		public static function htmlInputNameArrayKeys(): string {

			return <<<'EOT'
				#\[?([^\[\]]+)\]?#
				EOT;
		}

		/**
		 * $0 = full URL
		 * $3$4$5 = domain
		 *
		 * @return string
		 */
		public static function url(): string {

			return <<<'EOT'
			#\b((https?|ftp|file)://|(www|ftp)(\.))([-A-Z0-9+&@\#%?=~_|$!:,.;]*[A-Z0-9+&@\#%=~_|$])([-A-Z0-9+&@\#/%?=~_|$!:,.;]*[A-Z0-9+&@\#/%=~_|$])#i
			EOT;
		}

		/**
		 * To check whether a string is a domain name
		 *
		 * @return string
		 */
		public static function validateDomainName(): string {

			return <<<'EOT'
			#^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$#i
			EOT;
		}
	}

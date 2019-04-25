<?php

	namespace Goji\Parsing;

	/**
	 * Class Parser
	 *
	 * @package Goji\Parsing
	 */
	class Parser {

		/**
		 * (Generator) Yields string line by line.
		 *
		 * @param string $str
		 * @return \Generator
		 */
		public static function getStringLineByLine(string $str) {

			$token = "\n\t";

			// Get first line
			$line = strtok($str, $token);

			while ($line !== false) {
				yield $line;
				$line = strtok($token);
			}
		}

		/**
		 * Escape every /* that belong to a quoted string.
		 *
		 * @param string $line
		 * @param string $escapeSequence
		 * @return string
		 */
		private static function escapeCStyleCommentStartSequenceInString(string $line, string $escapeSequence): string {

			$offset = 0;
			$commentPos = mb_strpos($line, '/*', $offset);

			// In case there are several /*s
			while ($commentPos !== false) {

				$itsAComment = false;
				$lineStartToCurrentPos = mb_substr($line, 0, ($offset + $commentPos)); // Portion before comment

				// Count unescaped double quotes before comment start
				preg_match_all(RegexPatterns::unescapedDoubleQuotes(), $lineStartToCurrentPos, $quoteMatches, PREG_PATTERN_ORDER);
				$nbUnescapedDoubleQuotes = count($quoteMatches[0]);

				// Count unescaped single quotes before comment start
				preg_match_all(RegexPatterns::unescapedSingleQuotes(), $lineStartToCurrentPos, $quoteMatches, PREG_PATTERN_ORDER);
				$nbUnescapedSingleQuotes = count($quoteMatches[0]);

				if ($nbUnescapedDoubleQuotes > 0 && $nbUnescapedDoubleQuotes % 2 === 0)
					$itsAComment = true;

				if ($nbUnescapedSingleQuotes > 0 && $nbUnescapedSingleQuotes % 2 === 0)
					$itsAComment = true;

				// If no quotes
				if ($nbUnescapedDoubleQuotes === 0
				    && $nbUnescapedSingleQuotes === 0)
					$itsAComment = true;

				if (!$itsAComment) {

					// We grab the other half of the string
					$otherHalf = mb_substr($line, ($offset + $commentPos));
					$otherHalfEscaped = mb_substr($otherHalf, 2); // Remove the /* (first two chars)
						$otherHalfEscaped = $escapeSequence . $otherHalfEscaped; // Prepend escape sequence

					$line = str_replace($otherHalf, $otherHalfEscaped, $line);

					// We offset mb_strpos() so that it doesn't analyze the same thing twice
					$offset = $commentPos + mb_strlen($escapeSequence);

				} else {

					// We offset mb_strpos() so that it doesn't find the same /* again
					$offset = $commentPos + 2; // + mb_strlen('/*')
				}

				$commentPos = mb_strpos($line, '/*', $offset);
			}

			return $line;
		}

		/**
		 * @param string $str
		 * @return string
		 */
		public static function removeMultiLineCStyleComments(string $str): string {

			// Those are tricky. Because "foo /*" /* bar */ will break.
			// The /* will be taken as comment and it will erase the "
			// So we need to go through the file line by line to separate
			// real comments from mistake comments

			$commentStartEscapeSequence = '##########commentstart##########';
			$lines = Parser::getStringLineByLine($str);
			foreach ($lines as $line) {

				$tmpLine = self::escapeCStyleCommentStartSequenceInString($line, $commentStartEscapeSequence);
				$str = str_replace($line, $tmpLine, $str);
			}

			// Here false comments are escaped.
			// We can safely remove real comments.
			$str = preg_replace(RegexPatterns::multiLineCStyleComments(), '', $str);

			// And now we end by putting back escaped comment starts
			$str = str_replace($commentStartEscapeSequence, '/*', $str);

			return $str;
		}

		/**
		 * @param string $str
		 * @return string
		 */
		public static function removeSingleLineCStyleComments(string $str): string {

			// Those are tricky. Because "http://www.domain.com" will break
			// The :// will be taken as comment and it will erase the "
			// So we need to go through the file line by line to separate
			// real comments from mistake comments

			$lines = Parser::getStringLineByLine($str);
			foreach ($lines as $line) {

				// If there's no single line comment, we're good
				$commentPos = mb_strpos($line, '//');
				if ($commentPos === false)
					continue;

				// We know there's a comment, and we know where it is in the string.
				// foo // bar -> we know it's at pos 4

				// To know if it's a comment or not, we need to know if it's in a string or not.
				// That's easy, we count the number of (unescaped) quotes that come before the comment starts.
				// If it's even, it's a comment, if it's odd, it's not

				$itsAComment = false;
				$tmpLine = mb_substr($line, 0, $commentPos); // foo <- with space

				// Count unescaped double quotes
				preg_match_all(RegexPatterns::unescapedDoubleQuotes(), $tmpLine, $quoteMatches, PREG_PATTERN_ORDER);
				$nbUnescapedDoubleQuotes = count($quoteMatches[0]);

				// Count unescaped single quotes
				preg_match_all(RegexPatterns::unescapedSingleQuotes(), $tmpLine, $quoteMatches, PREG_PATTERN_ORDER);
				$nbUnescapedSingleQuotes = count($quoteMatches[0]);

				if ($nbUnescapedDoubleQuotes > 0 && $nbUnescapedDoubleQuotes % 2 === 0)
					$itsAComment = true;

				if ($nbUnescapedSingleQuotes > 0 && $nbUnescapedSingleQuotes % 2 === 0)
					$itsAComment = true;

				// If no quotes
				if ($nbUnescapedDoubleQuotes === 0
				    && $nbUnescapedSingleQuotes === 0)
					$itsAComment = true;

				if ($itsAComment) { // If it's a comment we can delete it right now

					$commentString = mb_substr($line, $commentPos);
					$tmpLine = str_replace($commentString, '', $line); // Remove comment from line
					$str = str_replace($line, $tmpLine, $str); // Replace entire line in file
					// Doing it in two times is safer, because if the comment is repeated later on
					// in a string that shouldn't be deleted
				}
			}

			return $str;
		}
	}

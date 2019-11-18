<?php

	namespace Goji\Rendering;

	use Goji\Parsing\RegexPatterns;

	/**
	 * Class BasicFormatting
	 *
	 * @package Goji\Rendering
	 */
	class BasicFormatting {

		/* <CONSTANTS> */

		const BACK_UP = 'back-up';
		const RESTORE = 'restore';

		/**
		 * @param string $text
		 * @param string $way
		 * @param $hitCount
		 * @param $hit
		 */
		protected static function escapeQuotedStrings(string &$text, string $way, &$hitCount, &$hit): void {

			if ($way == self::BACK_UP) { // Backup

				// Backup values within single or double quotes
				preg_match_all(RegexPatterns::quotedStrings(), $text, $hit, PREG_PATTERN_ORDER);

				$hitCount = count($hit[1]);
				for ($i = 0; $i < $hitCount; $i++) {
					$text = str_replace($hit[1][$i], '££££££££££' . $i . '££££££££££', $text);
				}

			} else { // Restore

				// Restore back-upped values within single or double quotes
				for ($i = 0; $i < $hitCount; $i++) {
					$text = str_replace('££££££££££' . $i . '££££££££££', $hit[1][$i], $text);
				}
			}
		}

		/**
		 * Converts links to HTML links.
		 *
		 * https://domainname.com/page?v=123 -> <a href="" title=""></a>
		 *
		 * Normal : https://domainname.com/page?v=123 (full link)
		 * Cleaned : domainname.com (domain name)
		 *
		 * @param string $text
		 * @param bool $clean (optional) default = false
		 * @return string
		 */
		public static function textLinksToHTML(string $text, $clean = false): string {

			// Back-up '...'
			$text = str_replace('...', '£@$€£@$€£@$€£@$€', $text);

			$text = preg_replace('#((https?://)?([\d\w\.-]+\.[\w\.]{2,6})([^\s\]\[\<\>]*/?))#i',
				'<a href="$1" ' . ($clean ? 'title="$1"' : '') . '>' . ($clean ? '$3' : '$1') . '</a>',
				$text);

			// Restore '...'
			$text = str_replace('£@$€£@$€£@$€£@$€', '...', $text);

			return $text;
		}

		/**
		 * A mix of markdownToHTML() and textLinksToHTML()
		 *
		 * @param string $text
		 * @return string
		 */
		public static function formatTextInlineAndEscape(string $text): string {

			// First, just escape regular HTML
			// We don't want any <br /> yet cause it would
			// mess with Markdown
			$text = htmlspecialchars($text);

			$text = BasicMarkdown::headingsToHTML($text, true); // true = <h1> to <span>
			$text = BasicMarkdown::inlineToHTML($text);
			//$text = BasicMarkdown::blocksToHTML($text, true);
			//$text = BasicMarkdown::listsToHTML($text, true);

			// Backup links, we don't want to use textLinksToHTML() on <a> tags
			preg_match_all('#(<a (.+?)</a>)#i', $text, $hit, PREG_PATTERN_ORDER);

			$hitCount = count($hit[1]);
			for ($i = 0; $i < $hitCount; $i++) {
				$text = str_replace($hit[1][$i], '&@ù&@ù&@ù&@ù&@ù' . $i . '&@ù&@ù&@ù&@ù&@ù', $text);
			}

			$text = self::textLinksToHTML($text, true); // true = leave domain, cut fluff

			// Restore backupped links
			for ($i = 0; $i < $hitCount; $i++) {
				$text = str_replace('&@ù&@ù&@ù&@ù&@ù' . $i . '&@ù&@ù&@ù&@ù&@ù', $hit[1][$i], $text);
			}

			// Now we can add in the <br>s
			$text = nl2br($text);

			return $text;
		}
	}

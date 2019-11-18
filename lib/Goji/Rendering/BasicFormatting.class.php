<?php

	namespace Goji\Rendering;

	use Goji\Parsing\RegexPatterns;

	/**
	 * Class BasicFormatting
	 *
	 * @package Goji\Rendering
	 */
	class BasicFormatting {

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
		public static function textLinksToHTML($text, $clean = false) {

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
		 * Transforms texts with Markdown syntax into HTML.
		 *
		 * @param string $text Text to transform
		 * @return string Transformed text (HTML)
		 */
		public static function markdownInlineToHTML($text) {

			// Backup values within single or double quotes
			preg_match_all(RegexPatterns::quotedStrings(), $text, $hit, PREG_PATTERN_ORDER);

			$hitCount = count($hit[1]);
			for ($i = 0; $i < $hitCount; $i++) {
				$text = str_replace($hit[1][$i], '££££££££££' . $i . '££££££££££', $text);
			}

			// ITALIC / BOLD / UNDERLINE / LINE-THROUGH / INLINE CODE
			$text = preg_replace('#\*{2}(.+?)\*{2}#is', '@@@@@@@@@@strong€€€€€€€€€€$1@@@@@@@@@@/strong€€€€€€€€€€', $text);
			$text = preg_replace('#\*{1}(.+?)\*{1}#is', '@@@@@@@@@@em€€€€€€€€€€$1@@@@@@@@@@/em€€€€€€€€€€', $text);
			$text = preg_replace('#\*{1}(.+?)\*{1}#is', '@@@@@@@@@@em€€€€€€€€€€$1@@@@@@@@@@/em€€€€€€€€€€', $text);
			$text = preg_replace('#__(.+?)__#is', '@@@@@@@@@@span style="text-decoration: underline;"€€€€€€€€€€$1@@@@@@@@@@/span€€€€€€€€€€', $text);
			$text = preg_replace('#~~(.+?)~~#is', '@@@@@@@@@@span style="text-decoration: line-through;"€€€€€€€€€€$1@@@@@@@@@@/span€€€€€€€€€€', $text);
			$text = preg_replace('#`(.+?)`#is', '@@@@@@@@@@code€€€€€€€€€€$1@@@@@@@@@@/code€€€€€€€€€€', $text);

			// IMAGES / LINKS
			$text = preg_replace('#!\[(.+?)\]\((.+?)\)#is', '@@@@@@@@@@img src="$2" alt="$1"€€€€€€€€€€', $text);
			$text = preg_replace('#\[(.+?)\]\((.+?)\)#is', '@@@@@@@@@@a href="$2"€€€€€€€€€€$1@@@@@@@@@@/a€€€€€€€€€€', $text);

			// Restore backupped values within single or double quotes
			for ($i = 0; $i < $hitCount; $i++) {
				$text = str_replace('££££££££££' . $i . '££££££££££', $hit[1][$i], $text);
			}

			$text = str_replace('@@@@@@@@@@', '<', $text);
			$text = str_replace('€€€€€€€€€€', '>', $text);

			return $text;
		}

		/**
		 * Mardown lists to ul/ol/li
		 *
		 * @param string $text
		 * @return string
		 */
		public static function markdownListsToHTML(string $text): string {

			// Backup values within single or double quotes
			preg_match_all(RegexPatterns::quotedStrings(), $text, $hit, PREG_PATTERN_ORDER);

			$hitCount = count($hit[1]);
			for ($i = 0; $i < $hitCount; $i++) {
				$text = str_replace($hit[1][$i], '££££££££££' . $i . '££££££££££', $text);
			}

			$lines = preg_split('#\R#', $text);
			$linesCount = count($lines);

			// For <ul> / <li>. If first we prepend a <li> tag
			// true as default, since when we encounter one, it's the first
			$firstLiTag = true;

			for ($i = 0; $i < $linesCount; $i++) {

				// UL LIST
				if (preg_match('#^-(.+)#i', $lines[$i])) {

					// Put <li></li> around and remove the dash (-) and any following white space
					$lines[$i] = preg_replace('#^-\s*(.+)#i', '@@@@@@@@@@li€€€€€€€€€€$1@@@@@@@@@@/li€€€€€€€€€€', $lines[$i]);

					// Check if first <li>
					if ($firstLiTag) {

						$lines[$i] = '@@@@@@@@@@ul€€€€€€€€€€' . $lines[$i]; // Prepend <ul>
						$firstLiTag = false; // Next one won't be first anymore
					}

					// Check if last <li>
					// IF current line is last line OR next line is not <li>
					if (($i == ($linesCount - 1))
					    || !(preg_match('#^-(.+)#i', $lines[$i + 1]))) {

						$lines[$i] .= '@@@@@@@@@@/ul€€€€€€€€€€';
						$firstLiTag = true; // Next one will be first again
					}

					continue;
				}

				// OL LIST
				if (preg_match('#^[0-9]{0,3}\.(.+)#i', $lines[$i])) { // {0,3} = 0-999

					// Put <li></li> around and remove the dash (-) and any following white space
					$lines[$i] = preg_replace('#^[0-9]{0,3}\.\s*(.+)#i', '@@@@@@@@@@li€€€€€€€€€€$1@@@@@@@@@@/li€€€€€€€€€€', $lines[$i]);

					// Check if first <li>
					if ($firstLiTag) {

						$lines[$i] = '@@@@@@@@@@ol€€€€€€€€€€' . $lines[$i]; // Prepend <ul>
						$firstLiTag = false; // Next one won't be first anymore
					}

					// Check if last <li>
					// IF current line is last line OR next line is not <li>
					if (($i == ($linesCount - 1))
					    || !(preg_match('#^[0-9]{0,3}\.(.+)#i', $lines[$i + 1]))) {

						$lines[$i] .= '@@@@@@@@@@/ol€€€€€€€€€€';
						$firstLiTag = true; // Next one will be first again
					}

					continue;
				}
			}

			$text = implode(PHP_EOL, $lines);

			// CLEANING OUT MY CLOSET
			// We don't want any <br /> after <ul>, <ol>, <li> because
			// these are block elements and would provoke a double line break.
			// But we only remove 1 to 2 line breaks, more than that is probably done on purpose by the user
			$text = preg_replace('#(@@@@@@@@@@/?(?:ul|ol|li)€€€€€€€€€€)\R{1,2}#i', '$1', $text);

			// Restore backupped values within single or double quotes
			for ($i = 0; $i < $hitCount; $i++) {
				$text = str_replace('££££££££££' . $i . '££££££££££', $hit[1][$i], $text);
			}

			$text = str_replace('@@@@@@@@@@', '<', $text);
			$text = str_replace('€€€€€€€€€€', '>', $text);

			return $text;
		}

		/**
		 * A mix of markdownToHTML() and textLinksToHTML()
		 *
		 * @param string $text
		 * @return string
		 */
		public static function formatTextInlineAndEscape($text) {

			// First, just escape regular HTML
			// We don't want any <br /> yet cause it would
			// mess with Markdown
			$text = htmlspecialchars($text);

			$text = self::markdownInlineToHTML($text); // true = <h1> to <span>

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

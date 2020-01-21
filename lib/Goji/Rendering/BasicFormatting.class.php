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

		$text = preg_replace(RegexPatterns::url(),
			'<a href="$0" ' . ($clean ? 'title="$0"' : '') . '>' . ($clean ? '$3$4$5' : '$0') . '</a>',
			$text);

		return $text;
	}

	/**
	 * A mix of markdownToHTML() and textLinksToHTML()
	 *
	 * @param string $text
	 * @param bool $escapeHTML
	 * @return string
	 */
	public static function formatTextInlineAndEscape(string $text, bool $escapeHTML = true): string {

		// First, just escape regular HTML
		if ($escapeHTML)
			$text = htmlspecialchars($text);

		$text = str_replace('%{WEBROOT}', WEBROOT, $text);

		$text = BasicMarkdown::headingsToHTML($text, true); // true = <h1> to <span>
		$text = BasicMarkdown::inlineToHTML($text);
		$text = BasicMarkdown::blocksToHTML($text, true);
		$text = BasicMarkdown::listsToHTML($text, true);
		$text = BasicMarkdown::alignmentToHTML($text, true);

		// Backup links and images, we don't want to use textLinksToHTML() on <a> or <img> tags
		preg_match_all('#(<a (.+?)</a>|<img (.+?)>)#is', $text, $hit, PREG_PATTERN_ORDER);

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

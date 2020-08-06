<?php

namespace Goji\Rendering;

use Goji\Parsing\RegexPatterns;
use Goji\Toolkit\SwissKnife;

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
	 * @param bool $fakeHeadings
	 * @return string
	 */
	public static function formatTextInlineAndEscape(string $text, bool $escapeHTML = true, bool $fakeHeadings = true): string {

		// First, just escape regular HTML
		if ($escapeHTML)
			$text = htmlspecialchars($text);

		$text = str_replace('web://', WEBROOT . '/', $text);

		$text = BasicMarkdown::headingsToHTML($text, $fakeHeadings); // true = <h1> to <span>
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

	/**
	 * Raw content to HTML
	 *
	 * @param $content
	 * @return string
	 */
	public static function renderAsHTML(string $content): string {

		// Backup %{CTA}
		preg_match_all('#%\{CTA(.*)%\{/CTA\}#isU', $content, $hit, PREG_PATTERN_ORDER);

		$hitCount = count($hit[0]);
		for ($i = 0; $i < $hitCount; $i++) {
			$content = str_replace($hit[0][$i], '$£$£$£$£$£' . $i . '$£$£$£$£$£', $content);
		}

		$content = BasicFormatting::formatTextInlineAndEscape($content, true, false);

		// Restore backupped values within single or double quotes
		for ($i = 0; $i < $hitCount; $i++) {
			$content = str_replace('$£$£$£$£$£' . $i . '$£$£$£$£$£', $hit[0][$i], $content);
		}

		$content = TemplateExtensions::ctaToHTML($content, '#', true);
		$content = TemplateExtensions::embedInstagram($content);
		$content = TemplateExtensions::embedYouTube($content);

		return $content;
	}

	/**
	 * Raw content to clean text
	 *
	 * @param string $content
	 * @return string
	 */
	public static function renderClean(string $content): string {
		return strip_tags(self::renderAsHTML($content));
	}

	/**
	 * Renders text without HTML tags and
	 *
	 * @param string $content
	 * @param int $maxLength
	 * @return string
	 */
	public static function renderCleanAndCut(string $content, int $maxLength = 250) {

		$content = self::renderClean($content);

		if (mb_strlen($content) > $maxLength)
			$content = SwissKnife::ceil_str($content, $maxLength) . '...';

		return $content;
	}
}

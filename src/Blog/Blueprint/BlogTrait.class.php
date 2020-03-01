<?php

namespace Blog\Blueprint;

use Goji\Rendering\BasicFormatting;
use Goji\Rendering\TemplateExtensions;
use Goji\Toolkit\SwissKnife;

trait BlogTrait {

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

		$content = BasicFormatting::formatTextInlineAndEscape($content);

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

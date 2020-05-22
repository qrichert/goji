<?php

namespace Blog\Blueprint;

use Goji\Rendering\BasicFormatting;

trait BlogTrait {

	/**
	 * Raw content to HTML
	 *
	 * @param $content
	 * @return string
	 */
	public static function renderAsHTML(string $content): string {
		return BasicFormatting::renderAsHTML($content);
	}

	/**
	 * Raw content to clean text
	 *
	 * @param string $content
	 * @return string
	 */
	public static function renderClean(string $content): string {
		return BasicFormatting::renderClean($content);
	}

	/**
	 * Renders text without HTML tags and
	 *
	 * @param string $content
	 * @param int $maxLength
	 * @return string
	 */
	public static function renderCleanAndCut(string $content, int $maxLength = 250) {
		return BasicFormatting::renderCleanAndCut($content, $maxLength);
	}
}

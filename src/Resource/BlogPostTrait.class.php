<?php

	namespace App\Resource;

	use Goji\Rendering\BasicFormatting;

	trait BlogPostTrait {

		/**
		 * Raw content to HTML
		 *
		 * @param $content
		 * @return string
		 */
		public static function renderAsHTML(string $content): string {

			$content = BasicFormatting::formatTextInlineAndEscape($content);

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
	}

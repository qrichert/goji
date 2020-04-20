<?php

namespace Goji\Rendering;

use Goji\Parsing\RegexPatterns;

/**
 * Class BasicMarkdown
 *
 * @package Goji\Rendering
 */
class BasicMarkdown {

	/**
	 * Transforms texts with Markdown syntax into HTML.
	 *
	 * @param string $text Text to transform
	 * @return string Transformed text (HTML)
	 */
	public static function inlineToHTML(string $text): string {

		// ITALIC / BOLD / UNDERLINE / LINE-THROUGH / INLINE CODE
		$text = preg_replace('#(?<!\\\\)\*\*(.+?)(?<!\\\\)\*\*#is', '@@@@@@@@@@strong€€€€€€€€€€$1@@@@@@@@@@/strong€€€€€€€€€€', $text);
		$text = preg_replace('#(?<!\\\\)\*(.+?)(?<!\\\\)\*#is', '@@@@@@@@@@em€€€€€€€€€€$1@@@@@@@@@@/em€€€€€€€€€€', $text);
		$text = preg_replace('#(?<!\\\\)__(.+?)(?<!\\\\)__#is', '@@@@@@@@@@span style="text-decoration: underline;"€€€€€€€€€€$1@@@@@@@@@@/span€€€€€€€€€€', $text);
		$text = preg_replace('#(?<!\\\\)~~(.+?)(?<!\\\\)~~#is', '@@@@@@@@@@span style="text-decoration: line-through;"€€€€€€€€€€$1@@@@@@@@@@/span€€€€€€€€€€', $text);
		$text = preg_replace('#(?<!\\\\)`(.+?)(?<!\\\\)`#is', '@@@@@@@@@@code€€€€€€€€€€$1@@@@@@@@@@/code€€€€€€€€€€', $text);

		// Escape removal
		$text = preg_replace('#\\\\([*_~`])#', '$1', $text);

		// IMAGES / LINKS
		$text = preg_replace('#!\[(.*?)\]\((.+?)\)#is', '@@@@@@@@@@img src="$2" alt="$1" class="markdown-image"€€€€€€€€€€', $text);
		$text = preg_replace('#\[(.*?)\]\((.+?)\)#is', '@@@@@@@@@@a href="$2"€€€€€€€€€€$1@@@@@@@@@@/a€€€€€€€€€€', $text);

		$text = str_replace('@@@@@@@@@@', '<', $text);
		$text = str_replace('€€€€€€€€€€', '>', $text);

		return $text;
	}

	/**
	 * @param string $text
	 * @param bool $fakeHeadings
	 * @param bool|null $breakParagraphs (default !$fakeHeadings)
	 * @param bool $underlinedHeadings
	 * @return string
	 */
	public static function headingsToHTML(string $text, bool $fakeHeadings = false, bool $breakParagraphs = null, bool $underlinedHeadings = false): string {

		if ($breakParagraphs === null)
			$breakParagraphs = !$fakeHeadings;

		// TITLES

		// Hashtags
		for ($i = 6; $i >= 1; $i--) { // From h6 to h1
			$text = preg_replace('/^\s*#{' . $i . '}\s?(.+)$/m', '@@@@@@@@@@h' . $i . '€€€€€€€€€€$1@@@@@@@@@@/h' . $i . '€€€€€€€€€€', $text);
		}

		// Underlined
		if ($underlinedHeadings) {
			$text = preg_replace('#^(.+?)\R={3,}\s*?$#m', '@@@@@@@@@@h1€€€€€€€€€€$1@@@@@@@@@@/h1€€€€€€€€€€', $text);
			$text = preg_replace('#^(.+?)\R-{3,}\s*?$#m', '@@@@@@@@@@h2€€€€€€€€€€$1@@@@@@@@@@/h2€€€€€€€€€€', $text);
		}

		$text = preg_replace_callback('#@@@@@@@@@@h([1-6])€€€€€€€€€€(.*?)@@@@@@@@@@/h[1-6]€€€€€€€€€€#im', function($matches) use($fakeHeadings, $breakParagraphs) {

			// No newlines inside heading
			$heading = preg_replace(RegexPatterns::newLines(), '', $matches[2]);

			// Means we don't want to use real titles not to mess up the document
			// This replaces all headings (<h[1-6]>) with a regular paragraph and a markdown-heading h[1-6] class
			if ($fakeHeadings)
				$heading = '@@@@@@@@@@span class="markdown-heading h' . $matches[1] . '"€€€€€€€€€€' . $heading . '@@@@@@@@@@/span€€€€€€€€€€';
			else
				$heading = '@@@@@@@@@@h' . $matches[1] . '€€€€€€€€€€' . $heading . '@@@@@@@@@@/h' . $matches[1] . '€€€€€€€€€€';

			// For block headings we need to break the <p>'s
			// <p>lorem<h2>title</h2>ipsum</p> -> <p>lorem</p><h2>title</h2><p>ipsum</p>
			if ($breakParagraphs)
				$heading = "</p>$heading<p>";

			return $heading;

		}, $text);

		// No <br> after reopening of a paragraph
		$text = preg_replace('#€€€€€€€€€€(<p>)?\R+#', '€€€€€€€€€€$1', $text);

		$text = str_replace('@@@@@@@@@@', '<', $text);
		$text = str_replace('€€€€€€€€€€', '>', $text);

		return $text;
	}

	/**
	 * Mardown lists to ul/ol/li
	 *
	 * @param string $text
	 * @param bool $fakeLists
	 * @return string
	 */
	public static function listsToHTML(string $text, bool $fakeLists = false): string {

		/*
		 * Lists after titles can be buggy because the heading function removes the new line after it
		 * We revert this behaviour for lists so that we can identify them
		 *
		 *                          ⌄ Bug here because no new line
		 * <h2>hello, world!</h2><p>- List item 1
		 * - List item 2
		 * ...
		 *
		 * Becomes:
		 *
		 * <h2>hello, world!</h2><p>
		 * - List item 1
		 * - List item 2
		 * ...
		 */
		$text = str_replace('<p>-', "<p>$*£$*£$*£$*£\n-", $text);

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

		if ($fakeLists) {

			// <ol>, <ul>
			$text = preg_replace('#@@@@@@@@@@(ul|ol)€€€€€€€€€€(.+?)@@@@@@@@@@/(ul|ol)€€€€€€€€€€#i',
			                     '@@@@@@@@@@span class="markdown-list $1"€€€€€€€€€€$2@@@@@@@@@@/span€€€€€€€€€€', $text);

			// <li>
			$text = preg_replace('#@@@@@@@@@@li€€€€€€€€€€(.+?)@@@@@@@@@@/li€€€€€€€€€€#i',
			                     '@@@@@@@@@@span class="markdown-list li"€€€€€€€€€€$1@@@@@@@@@@/span€€€€€€€€€€', $text);
		}

		$text = str_replace('@@@@@@@@@@', '<', $text);
		$text = str_replace('€€€€€€€€€€', '>', $text);

		// We can remove the newlines we added now, or else there will be too much space
		$text = str_replace("<p>$*£$*£$*£$*£\n", '<p>', $text);

		return $text;
	}

	/**
	 * <hr>
	 *
	 * @param string $text
	 * @param bool $fakeBlocks
	 * @return string
	 */
	public static function blocksToHTML(string $text, bool $fakeBlocks = false): string {

		// <hr>
		$text = preg_replace('#^\s*?-{3,}\s*?$#m', '@@@@@@@@@@hr€€€€€€€€€€', $text);

		// CLEANING OUT MY CLOSET
		// We don't want any <br /> after <hr> because
		// these are block elements and would provoke a double line break.
		// But we only remove 1 to 2 line breaks, more than that is probably done on purpose by the user
		$text = preg_replace('#(@@@@@@@@@@/?(?:hr)€€€€€€€€€€)\R{1,2}#i', '$1', $text);

		if ($fakeBlocks) {
			$text = preg_replace('#@@@@@@@@@@hr€€€€€€€€€€#i',
			                     '@@@@@@@@@@span class="markdown-hr"€€€€€€€€€€@@@@@@@@@@/span€€€€€€€€€€', $text);
		}

		$text = str_replace('@@@@@@@@@@', '<', $text);
		$text = str_replace('€€€€€€€€€€', '>', $text);

		return $text;
	}

	/**
	 * Alignment:
	 * |+ left               -|
	 * |-              right +|
	 * ||       center       ||
	 *
	 * @param string $text
	 * @param bool $fakeBlocks
	 * @return string
	 */
	public static function alignmentToHTML(string $text, bool $fakeBlocks = false): string {

		$tag = $fakeBlocks ? 'span' : 'div';
		$fakeClass = $fakeBlocks ? 'markdown-aligned' : '';

		// The \R{0,2} part is to clean unwanted <br>s (always remove one)
		$text = preg_replace('#(?<!\\\\)\|\+(.+?)(?<!\\\\)-\|(\s*\R{0,2})#is', "@@@@@@@@@@{$tag} class=\"aligned--left {$fakeClass}\"€€€€€€€€€€$1$2@@@@@@@@@@/{$tag}€€€€€€€€€€", $text);
		$text = preg_replace('#(?<!\\\\)\|-(.+?)(?<!\\\\)\+\|(\s*\R{0,2})#is', "@@@@@@@@@@{$tag} class=\"aligned--right {$fakeClass}\"€€€€€€€€€€$1$2@@@@@@@@@@/{$tag}€€€€€€€€€€", $text);
		$text = preg_replace('#(?<!\\\\)\|\|(.+?)(?<!\\\\)\|\|(\s*\R{0,2})#is', "@@@@@@@@@@{$tag} class=\"aligned--center {$fakeClass}\"€€€€€€€€€€$1$2@@@@@@@@@@/{$tag}€€€€€€€€€€", $text);

		// Escape removal
		$text = str_replace('\|', '|', $text);

		$text = str_replace('@@@@@@@@@@', '<', $text);
		$text = str_replace('€€€€€€€€€€', '>', $text);

		return $text;
	}
}

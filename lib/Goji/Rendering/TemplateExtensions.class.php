<?php

namespace Goji\Rendering;

use Goji\Debug\Logger;
use Goji\Parsing\JSON5;

/**
 * Class TemplateExtensions
 *
 * @package Goji\Rendering
 */
class TemplateExtensions {

	/**
	 * Replaces Call To Action template shortcut with HTML
	 *
	 * %{CTA}Send Me A Message%{/CTA}
	 * %{CTA small}Send Me A Message%{/CTA}
	 * %{CTA smaller}Send Me A Message%{/CTA}
	 *
	 * By default, the href of the <a> will be '#', you can set it using
	 * the second parameter $aHref.
	 *
	 * @param string $templateString
	 * @param string $aHref Default href="" to use for link
	 * @param bool $inline Use <span> instead of <div>
	 * @return string
	 */
	public static function ctaToHTML(string $templateString, string $aHref = '#', bool $inline = false): string {

		return preg_replace_callback('#%\{CTA( .*)?\}(.*)%\{/CTA\}#isU', function($match) use($aHref, $inline) {

			if (preg_match('#\[(.+?)\]\((.+?)\)#i', $match[2])) {
				$aHref = preg_replace('#\[(.+?)\]\((.+?)\)#i', '$2', $match[2]);
				$match[2] = preg_replace('#\[(.+?)\]\((.+?)\)#i', '$1', $match[2]);
			}

			$match[2] = trim($match[2]);

			$htmlTag = $inline ? 'span' : 'div';

			return <<<EOT
				<{$htmlTag} class="call-to-action__wrapper">
					<a href="$aHref" class="call-to-action{$match[1]}">{$match[2]}</a>
				</{$htmlTag}>
				EOT;

		}, $templateString);
	}

	/**
	 * Replaces book template shortcut with HTML:
	 *
	 * %{BOOK}
	 *     id: "blue-ocean-strategy",
	 *     image: "img/books/blue-ocean-strategy.jpg",
	 *     side: "left",
	 *     alt: "Blue Ocean Strategy - W. Chan Kim, Renée Mauborgne",
	 *     text: "<p>
	 *                Lorem ipsum dolor sit amet, consectetur adipisicing elit.
	 *                Alias delectus dolorem dolorum eaque eligendi, eos esse
	 *                facilis illo incidunt ipsa, iusto laborum officiis perspiciatis
	 *                reiciendis rerum similique ut, veniam voluptates!
	 *            <p>"
	 * %{/BOOK}
	 *
	 * @param string $templateString
	 * @return string
	 */
	public static function booksToHTML(string $templateString): string {

		return preg_replace_callback('#%\{BOOK\}(.*)%\{/BOOK\}#isU', function($match) {

			$json = JSON5::decode('{' . $match[1] . '}', true); // Add {}, it's JSON

			if ($json === null) {
				Logger::warning("Book can't be displayed because JSON can't be read. Content returned.");
				return $match[1];
			}

			$json['id'] = !empty($json['id']) ? ('data-id="' . $json['id'] . '"') : '';
			$json['side'] = isset($json['side']) && in_array($json['side'], ['left', 'right']) ? $json['side'] : 'left';
			$json['alt'] = isset($json['alt']) ? htmlspecialchars($json['alt']) : '';
			$json['image'] = SimpleTemplate::rsc($json['image']);

			$out = '';

			// Regular book
			if (!isset($json['text'])) {

				$out .= <<<EOT
					<div class="book {$json['side']}" {$json['id']}>
						<img src="{$json['image']}" alt="{$json['alt']}">
					</div>
					EOT;

				return $out;
			}

			// Book and text
			if ($json['side'] == 'left') {

				$out .= <<<EOT
					<section class="side-by-side right-bigger">
						<div class="image">
							<div class="book left" {$json['id']}>
								<img src="{$json['image']}" alt="{$json['alt']}">
							</div>
						</div>
						<div>
							{$json['text']}
						</div>
					</section>
					EOT;
			} else {

				$out .= <<<EOT
					<section class="side-by-side reverse-on-squeeze left-bigger">
						<div>
							{$json['text']}
						</div>
						<div class="image">
							<div class="book right" {$json['id']}>
								<img src="{$json['image']}" alt="{$json['alt']}">
							</div>
						</div>
					</section>
					EOT;
			}

			return $out;

		}, $templateString);
	}

	/**
	 * Renders Instagram embed post from post ID
	 *
	 * https://www.instagram.com/p/B8KRPhqIRZ36IyVi7akGTw8l9KsVRJDhrj-omk0/
	 *                            |                   ID                  |
	 * ---->                       B8KRPhqIRZ36IyVi7akGTw8l9KsVRJDhrj-omk0
	 *
	 * %{INSTAGRAM B8KRPhqIRZ36IyVi7akGTw8l9KsVRJDhrj-omk0}
	 *
	 * @param string $templateString
	 * @return string
	 */
	public static function embedInstagram(string $templateString): string {

		$instagramEmbedCode = @file_get_contents('../template/embed/instagram.html');

		if ($instagramEmbedCode === false) {
			Logger::log("Warning: Instagram embed template not found in 'template/embed/instagram.html'");
			return $templateString;
		}

		$instagramEmbedCode = "</p>$instagramEmbedCode<p>";

		return preg_replace_callback('#%\{INSTAGRAM (.*)\}#isU', function($match) use($instagramEmbedCode) {

			return str_replace('%{POST_ID}', trim($match[1]), $instagramEmbedCode);

		}, $templateString);
	}

	/**
	 * Renders YouTube embedded video from video ID
	 *
	 * @param string $templateString
	 * @return string
	 */
	public static function embedYouTube(string $templateString): string {

		return preg_replace_callback('#%\{YOUTUBE ([^\#]*)(?:\#(l|m|s|fl|fm|fs))?\}#isU', function($match) {

			$match[1] = trim($match[1]);
			$match[2] = trim($match[2] ?? '');

			return <<<EOT
				</p>
				<div class="video-wrapper markdown-video {$match[2]}">
					<iframe
						width="560"
						height="315"
						src="https://www.youtube.com/embed/{$match[1]}?rel=0&amp;showinfo=0"
						frameborder="0"
						allow="autoplay; encrypted-media"
						allowfullscreen
					></iframe>
				</div>
				<p>
				EOT;

		}, $templateString);
	}
}

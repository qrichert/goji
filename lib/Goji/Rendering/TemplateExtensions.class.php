<?php

namespace Goji\Rendering;

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
	 * @param string $aHref
	 * @return string
	 */
	public static function ctaToHTML(string $templateString, string $aHref = '#'): string {

		return preg_replace_callback('#%\{CTA( .*)?\}(.*)%\{/CTA\}#isU', function($match) use($aHref) {

			return <<<EOT
				<div class="call-to-action__wrapper">
					<a href="$aHref" class="call-to-action{$match[1]}">{$match[2]}</a>
				</div>
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
				trigger_error("Book can't be displayed because JSON can't be read. Content returned.", E_USER_WARNING);
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
}

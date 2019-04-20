<?php

	namespace Goji;

	class SwissKnife {

		public static function linkFiles($type, $files) {

			if (!is_array($files) || count($files) == 0)
				return;

			$linkStatement = '';

			if ($type == 'css')
				$linkStatement = '<link rel="stylesheet" type="text/css" href="%{PATH}">';
			else if ($type = 'js')
				$linkStatement = '<script src="%{PATH}"></script>';
			else
				return;

			if (LINKED_FILES_MODE == 'merged') {

				$f = implode(urlencode('|'), $files);
				echo str_replace('%{PATH}', $f, $linkStatement) . PHP_EOL;

			} else {

				foreach ($files as $f) {
					echo str_replace('%{PATH}', $f, $linkStatement) . PHP_EOL;
				}
			}
		}

		/**
		 * Returns the value of the first occurrence of a query string parameter.
		 *
		 * In PHP, $_GET['param'] always returns the value of the last occurrence of 'param'.
		 *
		 * For example :
		 * ```php
		 * ?param=foo&param=bar
		 * $_GET['param'] == 'bar'
		 * ```
		 *
		 * Sometimes it causes security issues because the user could override the
		 * system value.
		 *
		 * This function returns the value of the first time the parameter appears,
		 * thus ignoring any user addition.
		 *
		 * @param string $param The parameter you want the value of
		 * @param string $queryString The query string in which to look for the value (could be $_SERVER['QUERY_STRING'])
		 * @return string|null The value of the first occurrence of $param, null if not found
		 */
		public static function getFirstParamOccurrence($param, $queryString) {

			// Ex :
			// $param = 'param'
			// $queryString = 'param=foo&param=bar'

			$param = $param . '='; // $param = 'param='
			$paramLength = strlen($param); // $paramLength = 6 (param + =)

			$query = explode('&', $queryString); // [0] => param=foo, [1] => param=bar

			foreach ($query as $p) {

				// if (substr('param=foo', 0, 6) == 'param='))
				if (substr($p, 0, $paramLength) == $param) { // substr('param=foo', 0, 6) == 'param='

					// substr('param=foo', 6) -> Remove first 6 chars
					return urldecode(substr($p, $paramLength)); // |param=|foo -> foo
				}
			}

			return null; // If not found
		}

		public static function print_array($array) {
			echo '<pre>';
			print_r($array);
			echo '</pre>';
		}

		public static function log_array($array) {
			error_log(print_r($array, true));
		}

		public static function log_var_dump($var) {
			ob_start();
			var_dump($var);
			error_log(ob_get_clean());
		}

		// Cuts string if longer than $max
		public static function ceil_str($str, $max) {
			return (strlen($str) > $max ? substr($str, 0, $max) : $str);
		}

		// Keep it 250 just so client never sees 255 for "security"
		public static function varchar250(&$str) {
			$str = self::ceil_str($str, 250);
		}

		// Email max length is 254 characters
		public static function varcharEmail(&$str) {
			$str = self::ceil_str($str, 254);
		}

		// Cleans email & uniformization
		public static function sanitizeEmail(&$email) {
			$email = filter_var($email, FILTER_SANITIZE_EMAIL);
			$email = strtolower($email);
		}

		// user.name@domain.com -> user.name
		public static function emailLocalPart($email) {
			return preg_replace('#^(.+)@(?:.+)#i', '$1', $email);
		}

		// Converts '1' to 'true' & else to 'false'
		// Made to use with TINYTEXT(1) to store bool
		public static function mysqlBool($boolean) {
			return intval($boolean) === 1;
		}

		/**
		 * Transforms texts with Markdown syntax into HTML.
		 *
		 * CSS Example for Markdown output styling :
		 *
		 * ```css
		 * .markdown-container .markdown-heading.h1 {
		 * 		font-family: var(--font-title);
		 * 		color: var(--color-text-dark);
		 * 		font-weight: bold;
		 * 		font-size: 1.17em;
		 * 		margin: 0 0 0.7em 0;
		 * 		padding: 0;
		 * }
		 *
		 * .markdown-container hr {
		 * 		border: none;
		 * 		border-top: 1px solid var(--color-separator);
		 * 		margin: 1.5em 0 1.7em 0;
		 * }
		 *
		 * .markdown-container ul,
		 * .markdown-container ol {
		 * 		list-style-position: inside;
		 * 		padding-left: var(--gutter-default);
		 * }
		 * ```
		 *
		 * @param string $text Text to transform
		 * @param bool $fakeHeadings Apply a 'markdown-heading' class instead instead of using the real HTML tags
		 * @return string Transformed text (HTML)
		 */
		public static function basicMarkdown($text, $fakeHeadings = false) {

			$lines = preg_split('#\R#', $text);
			$linesCount = count($lines);

			// For <ul> / <li>. If first we prepend a <li> tag
			// true as default, since when we encounter one, it's the first
			$firstLiTag = true;

			for ($i = 0; $i < $linesCount; $i++) {

				// TITLES
				$lines[$i] = preg_replace('#^\#{6}(.+)#i', '<h6>$1</h6>', $lines[$i]);
				$lines[$i] = preg_replace('#^\#{5}(.+)#i', '<h5>$1</h5>', $lines[$i]);
				$lines[$i] = preg_replace('#^\#{4}(.+)#i', '<h4>$1</h4>', $lines[$i]);
				$lines[$i] = preg_replace('#^\#{3}(.+)#i', '<h3>$1</h3>', $lines[$i]);
				$lines[$i] = preg_replace('#^\#{2}(.+)#i', '<h2>$1</h2>', $lines[$i]);
				$lines[$i] = preg_replace('#^\#{1}(.+)#i', '<h1>$1</h1>', $lines[$i]);

				// HR

				$lines[$i] = preg_replace('#^-{3,}$#i', '<hr>', $lines[$i]);

				// UL LIST
				if (preg_match('#^-(.+)#i', $lines[$i])) {

					// Put <li></li> around and remove the dash (-) and any following white space
					$lines[$i] = preg_replace('#^-\s*(.+)#i', '<li>$1</li>', $lines[$i]);

					// Check if first <li>
					if ($firstLiTag) {

						$lines[$i] = '<ul>' . $lines[$i]; // Prepend <ul>
						$firstLiTag = false; // Next one won't be first anymore
					}

					// Check if last <li>
					// IF current line is last line OR next line is not <li>
					if (($i == ($linesCount - 1))
						|| !(preg_match('#^-(.+)#i', $lines[$i + 1]))) {

						$lines[$i] .= '</ul>';
						$firstLiTag = true; // Next one will be first again
					}
				}

				// OL LIST
				if (preg_match('#^[0-9]{0,3}\.(.+)#i', $lines[$i])) { // {0,3} = 0-999

					// Put <li></li> around and remove the dash (-) and any following white space
					$lines[$i] = preg_replace('#^[0-9]{0,3}\.\s*(.+)#i', '<li>$1</li>', $lines[$i]);

					// Check if first <li>
					if ($firstLiTag) {

						$lines[$i] = '<ol>' . $lines[$i]; // Prepend <ul>
						$firstLiTag = false; // Next one won't be first anymore
					}

					// Check if last <li>
					// IF current line is last line OR next line is not <li>
					if (($i == ($linesCount - 1))
						|| !(preg_match('#^[0-9]{0,3}\.(.+)#i', $lines[$i + 1]))) {

						$lines[$i] .= '</ol>';
						$firstLiTag = true; // Next one will be first again
					}
				}
			}

			$text = implode("\n", $lines);

			// ITALIC / BOLD / UNDERLINE / LINE-THROUGH
			// This can be multi-line
			$text = preg_replace('#\*{2}(.+?)\*{2}#is', '<strong>$1</strong>', $text);
			$text = preg_replace('#\*{1}(.+?)\*{1}#is', '<em>$1</em>', $text);
			$text = preg_replace('#\*{1}(.+?)\*{1}#is', '<em>$1</em>', $text);
			$text = preg_replace('#__(.+?)__#is', '<span style="text-decoration: underline;">$1</span>', $text);
			$text = preg_replace('#~~(.+?)~~#is', '<span style="text-decoration: line-through;">$1</span>', $text);

			// CLEANING OUT MY CLOSET
			// We don't want any <br /> after <h[1-6]>, <ul>, <ol>, <li> because these are block elements
			// and would provoke a double line break.
			// But we only remove 1 to 2 line breaks, more than that is probably done on purpose by the user
			$text = preg_replace('#(</?(?:h[1-6]|hr|ul|ol|li)>)\R{1,2}#i', '$1', $text);

			// Means we don't want to user real titles not to mess up the document
			// This replaces all headings (<h[1-6]>) with a regular paragraph and a markdown-heading h[1-6] class
			if ($fakeHeadings) {
				$text = preg_replace('#<h([1-6])>(.+?)</h[1-6]>#i', '<p class="markdown-heading h$1">$2</p>', $text);
			}

			return $text;
		}

		public static function textLinksToHTML($text, $clean = false) {

			// Normal : https://domainname.com/page?v=123 (full link)
			// Cleaned : domainname.com (domain name)

			return preg_replace('#((https?://)?([\d\w\.-]+\.[\w\.]{2,6})([^\s\]\[\<\>]*/?))#i',
				'<a href="$1" ' . ($clean ? 'title="$1"' : '') . '>' . ($clean ? '$3' : '$1') . '</a>',
				$text);
		}

		public static function formatTextAndEscape($text) {
			// First, just escape regular HTML
			// We don't want any <br /> yet cause it would
			// mess with Markdown
			$text = htmlspecialchars($text);

			$text = self::basicMarkdown($text, true); // true = <h1> to <span>

			$text = self::textLinksToHTML($text, true); // true = leave domain, cut fluff

			// Now we can add in the <br>s
			$text = nl2br($text);

			return $text;
		}
	}

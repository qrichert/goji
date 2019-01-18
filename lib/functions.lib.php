<?php

	function print_array($array) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}

	function log_array($array) {
		error_log(print_r($array, true));
	}

	function log_var_dump($var) {
		ob_start();
		var_dump($var);
		error_log(ob_get_clean());
	}

	// Cuts string if longer than $max
	function ceil_str($str, $max) {
		return (strlen($str) > $max ? substr($str, 0, $max) : $str);
	}

	// Keep it 250 just so client never sees 255 for "security"
	function varchar250(&$str) {
		$str = ceil_str($str, 250);
	}

	// Email max length is 254 characters
	function varcharEmail(&$str) {
		$str = ceil_str($str, 254);
	}

	// Cleans email & uniformization
	function sanitizeEmail(&$email) {
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		$email = strtolower($email);
	}

	// user.name@domain.com -> user.name
	function emailLocalPart($email) {
		return preg_replace('#^(.+)@(?:.+)#i', '$1', $email);
	}

	// Converts '1' to 'true' & else to 'false'
	// Made to use with TINYTEXT(1) to store bool
	function mysqlBool($boolean) {
		return intval($boolean) === 1;
	}

	/*
		CSS Example for mardown output styling
		--------------------------------------

		.markdown-container .markdown-heading.h1 {
			font-family: var(--font-title);
			color: var(--color-text-dark);
			font-weight: bold;
			font-size: 1.17em;
			margin: 0 0 0.7em 0;
			padding: 0;
		}

		.markdown-container hr {
			border: none;
			border-top: 1px solid var(--color-separator);
			margin: 1.5em 0 1.7em 0;
		}

		.markdown-container ul,
		.markdown-container ol {
			list-style-position: inside;
			padding-left: var(--gutter-default);
		}
	*/
	function basicMarkdown($text, $fakeHeadings = false) {

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
//			if (preg_match('#^[0-9]{0,3}\.(.+)#i', $lines[$i])) { // {0,3} = 0-999
//
//				// Put <li></li> around and remove the dash (-) and any following white space
//				$lines[$i] = preg_replace('#^[0-9]{0,3}\.\s*(.+)#i', '<li>$1</li>', $lines[$i]);
//
//				// Check if first <li>
//				if ($firstLiTag) {
//
//					$lines[$i] = '<ol>' . $lines[$i]; // Prepend <ul>
//					$firstLiTag = false; // Next one won't be first anymore
//				}
//
//				// Check if last <li>
//				// IF current line is last line OR next line is not <li>
//				if (($i == ($linesCount - 1))
//					|| !(preg_match('#^[0-9]{0,3}\.(.+)#i', $lines[$i + 1]))) {
//
//					$lines[$i] .= '</ol>';
//					$firstLiTag = true; // Next one will be first again
//				}
//			}
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

	function textLinksToHTML($text, $clean = false) {

		// Normal : https://domainname.com/page?v=123 (full link)
		// Cleaned : domainname.com (domain name)

		return preg_replace('#((https?://)?([\d\w\.-]+\.[\w\.]{2,6})([^\s\]\[\<\>]*/?))#i',
							'<a href="$1" ' . ($clean ? 'title="$1"' : '') . '>' . ($clean ? '$3' : '$1') . '</a>',
							$text);
	}

	function formatTextAndEscape($text) {
		// First, just escape regular HTML
		// We don't want any <br /> yet cause it would
		// mess with Markdown
		$text = htmlspecialchars($text);

		$text = basicMarkdown($text, true); // true = <h1> to <span>

		$text = textLinksToHTML($text, true); // true = leave domain, cut fluff

		// Now we can add in the <br>s
		$text = nl2br($text);

		return $text;
	}

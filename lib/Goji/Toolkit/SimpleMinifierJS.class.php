<?php

	namespace Goji\Toolkit;

	/**
	 * Class SimpleMinifierJS
	 *
	 * @package Goji
	 */
	class SimpleMinifierJS extends SimpleMinifierAbstract {

		public static function minify($code) {

			// Remove comments
  			$code = preg_replace('#/\*[^*]*\*+([^/][^*]*\*+)*/#', '', $code); // Multi line : /* hello, world */
  			$code = preg_replace('#//.*$#m', '', $code); // Single line : // hello, world

			// Backup values within single or double quotes
			preg_match_all('#(\'[^\']*?\'|"[^"]*?")#ims', $code, $hit, PREG_PATTERN_ORDER);

			for ($i = 0; $i < count($hit[1]); $i++) {
				$code = str_replace($hit[1][$i], '##########' . $i . '##########', $code);
			}

			// Remove white-space around ';'
			$code = preg_replace('#[\s\r\n\t]*;[\s\r\n\t]*?([^\s\r\n\t])#ims', ';$1', $code);
			// Remove white-space around ':'
			$code = preg_replace('#[\s\r\n\t]*:[\s\r\n\t]*?([^\s\r\n\t])#ims', ':$1', $code);
			// Remove white-space around ','
			$code = preg_replace('#[\s\r\n\t]*,[\s\r\n\t]*?([^\s\r\n\t])#ims', ',$1', $code);
			// Remove white-space around '{'
			$code = preg_replace('#[\s\r\n\t]*\{[\s\r\n\t]*?([^\s\r\n\t])#ims', '{$1', $code);
			// Remove white-space around '}'
			$code = preg_replace('#[\s\r\n\t]*\}[\s\r\n\t]*?([^\s\r\n\t])#ims', '}$1', $code);
			// Remove white-space around '['
			$code = preg_replace('#[\s\r\n\t]*\[[\s\r\n\t]*?([^\s\r\n\t])#ims', '[$1', $code);
			// Remove white-space around ']'
			$code = preg_replace('#[\s\r\n\t]*\][\s\r\n\t]*?([^\s\r\n\t])#ims', ']$1', $code);
			// Remove white-space around '('
			$code = preg_replace('#[\s\r\n\t]*\([\s\r\n\t]*?([^\s\r\n\t])#ims', '($1', $code);
			// Remove white-space around ')'
			$code = preg_replace('#[\s\r\n\t]*\)[\s\r\n\t]*?([^\s\r\n\t])#ims', ')$1', $code);
			// Remove white-space around '='
			$code = preg_replace('#[\s\r\n\t]*(=+)[\s\r\n\t]*?([^\s\r\n\t])#ims', '$1$2', $code);
			// Remove white-space around '!'
			$code = preg_replace('#[\s\r\n\t]*![\s\r\n\t]*?([^\s\r\n\t])#ims', '!$1', $code);
			// Remove white-space around '?'
			$code = preg_replace('#[\s\r\n\t]*\?[\s\r\n\t]*?([^\s\r\n\t])#ims', '?$1', $code);
			// Remove white-space around '>'
			$code = preg_replace('#[\s\r\n\t]*>[\s\r\n\t]*?([^\s\r\n\t])#ims', '>$1', $code);
			// Remove white-space around '<'
			$code = preg_replace('#[\s\r\n\t]*<[\s\r\n\t]*?([^\s\r\n\t])#ims', '<$1', $code);
			// Remove white-space around '||'
			$code = preg_replace('#[\s\r\n\t]*\|\|[\s\r\n\t]*?([^\s\r\n\t])#ims', '||$1', $code);
			// Remove white-space around '&&'
			$code = preg_replace('#[\s\r\n\t]*&&[\s\r\n\t]*?([^\s\r\n\t])#ims', '&&$1', $code);
			// Remove white-space around '+'
			$code = preg_replace('#[\s\r\n\t]*\+[\s\r\n\t]*?([^\s\r\n\t])#ims', '+$1', $code);
			// Remove white-space around '-'
			$code = preg_replace('#[\s\r\n\t]*-[\s\r\n\t]*?([^\s\r\n\t])#ims', '-$1', $code);
			// Remove white-space around '*'
			$code = preg_replace('#[\s\r\n\t]*\*[\s\r\n\t]*?([^\s\r\n\t])#ims', '*$1', $code);
			// Remove white-space around '/'
			$code = preg_replace('#[\s\r\n\t]*/[\s\r\n\t]*?([^\s\r\n\t])#ims', '/$1', $code);
			// Remove white-space around '%'
			$code = preg_replace('#[\s\r\n\t]*%[\s\r\n\t]*?([^\s\r\n\t])#ims', '%$1', $code);
			// Remove redundant white-space
			$code = preg_replace('#\p{Zs}+#ims', ' ', $code);
			// Remove new lines
			$code = str_replace(array("\r\n", "\r", "\n", PHP_EOL), '', $code);

			// Restore backupped values within single or double quotes
			for ($i = 0; $i < count($hit[1]); $i++) {
				$code = str_replace('##########' . $i . '##########', $hit[1][$i], $code);
			}

			return $code;
		}
	}

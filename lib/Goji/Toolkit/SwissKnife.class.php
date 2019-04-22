<?php

	namespace Goji\Toolkit;

	/**
	 * Class SwissKnife
	 *
	 * @package Goji\Toolkit
	 */
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
	}
<?php

	namespace Goji\Toolkit;

	/**
	 * Class SwissKnife
	 *
	 * @package Goji\Toolkit
	 */
	class SwissKnife {

		// Cuts string if longer than $max
		public static function ceil_str($str, $max) {
			return (mb_strlen($str) > $max ? mb_substr($str, 0, $max) : $str);
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
			$email = mb_strtolower($email);
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

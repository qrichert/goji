<?php

	namespace Goji\Toolkit;

	/**
	 * Class SwissKnife
	 *
	 * @package Goji\Toolkit
	 */
	class SwissKnife {

		/**
		 * Cuts string if longer than $max
		 *
		 * @param string $str
		 * @param int $max
		 * @return string
		 */
		public static function ceil_str(string $str, int $max): string {
			return (mb_strlen($str) > $max ? mb_substr($str, 0, $max) : $str);
		}

		/**
		 * Keep it 250 just so client never sees 255 for "security"
		 *
		 * @param string $str
		 */
		public static function varchar250(string &$str): void {
			$str = self::ceil_str($str, 250);
		}

		/**
		 * Email max length is 254 characters
		 *
		 * @param string $email
		 */
		public static function varcharEmail(string &$email): void {
			$email = self::ceil_str($email, 254);
		}

		/**
		 * Cleans email & uniformization
		 *
		 * @param string $email
		 */
		public static function sanitizeEmail(string &$email): void {
			$email = filter_var($email, FILTER_SANITIZE_EMAIL);
			$email = mb_strtolower($email);
		}

		/**
		 * user.name@domain.com -> user.name
		 *
		 * @param string $email
		 * @return string
		 */
		public static function emailLocalPart(string $email): string {
			return preg_replace('#^(.+)@(?:.+)#i', '$1', $email);
		}

		/**
		 * Converts '1' to 'true' & else to 'false'
		 *
		 * Made to use with TINYTEXT(1) to store bool
		 *
		 * @param $boolean
		 * @return bool
		 */
		public static function mysqlBool($boolean): bool {
			return intval($boolean) === 1;
		}

		/**
		 * Shuffles a multi byte string (link UTF-8)
		 *
		 * In PHP strings are byte arrays, and str_shuffle() shuffles single bytes,
		 * leading to multi byte characters (basically all that is non English-default,
		 * or non ASCII) being cut, thus producing unknown characters.
		 *
		 * This function shuffles the string without separating the bytes of a single
		 * character (it separates the characters into an array, and shuffles the array).
		 *
		 * @param string $str
		 * @return string
		 */
		public static function mb_str_shuffle(string $str): string {

			$strlen = mb_strlen($str);
			$letters = array();

			while ($strlen-- > 0) {
				$letters[] = mb_substr($str, $strlen, 1);
			}

			shuffle($letters);

			return join('', $letters);
		}

		/**
		 * Removes accents on accented characters (é => e)
		 *
		 * @param string $str
		 * @return string
		 */
		public static function removeAccents(string $str): string {

			$transliterator = \Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
			                                                  \Transliterator::FORWARD);

			return $transliterator->transliterate($str);
		}
	}

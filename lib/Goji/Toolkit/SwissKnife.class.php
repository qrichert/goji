<?php

	namespace Goji\Toolkit;

	use DateTime;
	use Transliterator;

	/**
	 * Class SwissKnife
	 *
	 * @package Goji\Toolkit
	 */
	class SwissKnife {

		/* <CONSTANTS> */

		// Those are translated in Goji's demo language files
		const UNIT_BYTE = 'UNIT_BYTE';
		const UNIT_KILO_BYTE = 'UNIT_KILO_BYTE';
		const UNIT_MEGA_BYTE = 'UNIT_MEGA_BYTE';
		const UNIT_GIGA_BYTE = 'UNIT_GIGA_BYTE';
		const UNIT_TERA_BYTE = 'UNIT_TERA_BYTE';

		/**
		 * Cuts string if longer than $max
		 *
		 * @param string $str
		 * @param int $max
		 * @param string $pad
		 * @return string
		 */
		public static function ceil_str(string $str, int $max, string $pad = ''): string {

			if (mb_strlen($str) > $max) {
				$nbPadChars = mb_strlen($pad);
				$str = mb_substr($str, 0, $max - $nbPadChars) . $pad;
			}

			return $str;
		}

		/**
		 * Like ceil_str() but cut the beginning instead of the end
		 *
		 * @param string $str
		 * @param int $max
		 * @param string $pad
		 * @return string
		 */
		public static function ceil_str_inverse(string $str, int $max, string $pad = ''): string {

			if (mb_strlen($str) > $max) {

				$nbPadChars = mb_strlen($pad);
				$str = $pad . mb_substr($str, -($max - $nbPadChars));
			}

			return $str;
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
		public static function sqlBool($boolean): bool {
			return 1 === (int) $boolean;
		}

		/**
		 * Shuffles a multi byte string (like UTF-8)
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
			$letters = [];

			while ($strlen-- > 0) {
				$letters[] = mb_substr($str, $strlen, 1);
			}

			shuffle($letters);

			return join('', $letters);
		}

		/**
		 * Breaks a written date into its components.
		 *
		 * @param string $date Written date (ex: from database)
		 * @param string $format Format of written date
		 * @return array
		 */
		public static function dateToComponents(string $date, string $format = 'Y-m-d H:i:s'): array {

			$dateTime = DateTime::createFromFormat($format, $date);

			$date = [
				'full' => $date,
				'year' => $dateTime->format('Y'),
				'month' => $dateTime->format('m'),
				'day' => $dateTime->format('d'),
				'hour' => $dateTime->format('H'),
				'min' => $dateTime->format('i'),
				'sec' => $dateTime->format('s'),
			];

			return $date;
		}

		/**
		 * Removes accents on accented characters (é => e)
		 *
		 * @param string $str
		 * @return string
		 */
		public static function removeAccents(string $str): string {

			$transliterator = Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
			                                                  Transliterator::FORWARD);

			return $transliterator->transliterate($str);
		}

		/**
		 * Removes new lines in a string.
		 *
		 * @param string $str
		 * @param string $replaceWith Replace the new lines with the given string (e.g. a space)
		 */
		public static function removeNewLines(string &$str, string $replaceWith = ''): void {
			$str = str_replace(["\r\n", "\r", "\n", PHP_EOL], $replaceWith, $str);
		}

		/**
		 * Transforms any string to a list of words, without special chars, separated by dashes
		 *
		 * -#HÉllo, _world-! 123 :) -> hello-world-123
		 *
		 * @param string $str
		 * @return string
		 */
		public static function stringToID(string $str): string {

			// -#HÉllo, _world-! 123 :)
			$str = mb_strtolower($str); // -#héllo, _world-! 123 :)
			$str = self::removeAccents($str); // -#hello, _world-! 123 :)
			$str = preg_replace('#[^A-Z0-9]+#i', '-', $str); // -hello-world-123-
			$str = trim($str, '-'); // hello-world-123

			return $str;
		}

		/**
		 * Returns an array of ['value' => value, 'unit' => unit]
		 *
		 * For example, a 5.72 MB file will be returned as
		 * ['value' => 5.72, 'unit' => SwissKnife::UNIT_MEGA_BYTE]
		 *
		 * @param int $bytes
		 * @param int $precision
		 * @return array
		 */
		public static function bytesToFileSize(int $bytes, int $precision = 2): array {

			if ($bytes < 1000) {

				return [
					'value' => $bytes,
					'unit' => self::UNIT_BYTE
				];
			}
			elseif ($bytes < 1000000) {

				$bytes = round($bytes / 1000, $precision);

				return [
					'value' => $bytes,
					'unit' => self::UNIT_KILO_BYTE
				];
			}
			elseif ($bytes < 1000000000) {

				$bytes = round($bytes / 1000000, $precision);

				return [
					'value' => $bytes,
					'unit' => self::UNIT_MEGA_BYTE
				];
			}
			elseif ($bytes < 1000000000000) {

				$bytes = round($bytes / 1000000000, $precision);

				return [
					'value' => $bytes,
					'unit' => self::UNIT_GIGA_BYTE
				];
			}
			else { // $bytes < 1000000000000000

				$bytes = round($bytes / 1000000000000, $precision);

				return [
					'value' => $bytes,
					'unit' => self::UNIT_TERA_BYTE
				];
			}
		}
	}

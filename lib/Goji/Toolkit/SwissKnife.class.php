<?php

namespace Goji\Toolkit;

use DateTime;
use finfo;
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
		else if ($bytes < 1000000) {

			$bytes = round($bytes / 1000, $precision);

			return [
				'value' => $bytes,
				'unit' => self::UNIT_KILO_BYTE
			];
		}
		else if ($bytes < 1000000000) {

			$bytes = round($bytes / 1000000, $precision);

			return [
				'value' => $bytes,
				'unit' => self::UNIT_MEGA_BYTE
			];
		}
		else if ($bytes < 1000000000000) {

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

	/**
	 * Returns dirsize in bytes or -1 on failure
	 *
	 * This command can be quite slow, use cache if possible.
	 *
	 * @param string $dir
	 * @return int
	 * @throws \Exception
	 */
	public static function dirsize(string $dir): int {

		$success = null;

		/*
		 * du = disk usage
		 * -s = compiled
		 * -k = chunks of 1024 bytes
		 */
		$dirsize = Terminal::execute('du -sk ' . $dir, $success); // 112764	/var/www/html

		if ($success === false)
			return -1;

		$dirsize = (int) preg_replace('#^(\d+)\D.*#', '$1', $dirsize); // 112764
		$dirsize *= 1024; // 112764 (chunks of 1024 bytes) * 1024 = nb bytes

		return $dirsize;
	}

	/**
	 * Joins path segments into a full path, with nice directory separator awareness.
	 *
	 * Emulates Python's os.path.join()
	 *
	 * @param mixed ...$args
	 * @return string
	 */
	public static function osPathJoin(...$args): string {

		$paths = [];

		foreach ($args as $arg) {
			if (!empty($arg))
				$paths[] = (string) $arg;
		}

		return preg_replace('#/+#', '/', join('/', $paths));
	}

	/**
	 * Transforms Hex color to RGB
	 *
	 * #f00 -> 255, 0, 0
	 * #ff0000 -> 255, 0, 0
	 *
	 * @param string|null $hexColor
	 * @param string $defaultHexColor
	 * @return array
	 */
	public static function hexColorToRGB(?string $hexColor, string $defaultHexColor = '#000000'): array {

		if (empty($hexColor))
			$hexColor = $defaultHexColor;

		// Remove leading '#' if any
		if (substr($hexColor, 0, 1) == '#')
			$hexColor = substr($hexColor, 1);

		$red = 0;
		$green = 0;
		$blue = 0;

		$colorStringLength = strlen($hexColor);

		if ($colorStringLength !== 3 && $colorStringLength !== 6)
			return [0, 0, 0]; // Black, default

		// 1fb -> 11, ff, bb
		if ($colorStringLength === 3) {

			$red = substr($hexColor, 0, 1);
				$red .= $red;

			$green = substr($hexColor, 1, 1);
				$green .= $green;

			$blue = substr($hexColor, 2, 1);
				$blue .= $blue;

		// 1fbc32 -> 1f, bc, 32
		} else {

			$red = substr($hexColor, 0, 2);
			$green = substr($hexColor, 2, 2);
			$blue = substr($hexColor, 4, 2);
		}

		$red = hexdec($red);
		$green = hexdec($green);
		$blue = hexdec($blue);


		return [$red, $green, $blue];
	}

	/**
	 * Digs up values from deep arrays.
	 *
	 * Loops through a recursive array and extracts the first value that is not an array.
	 *
	 * @param $array
	 * @return !array|null
	 */
	public static function extractFirstNonArrayValueFromRecursiveArray(&$array) {

		if (!is_array($array))
			return $array;

		foreach ($array as $value) {

			if (!is_array($value))
				return $value;
			else
				return self::extractFirstNonArrayValueFromRecursiveArray($value);
		}

		return null;
	}

	public static function mime_content_type(string $file): string {

		$finfo = new finfo(FILEINFO_MIME_TYPE);

		$fileType = $finfo->file($file);

		if ($fileType === false)
			$fileType = 'text/plain';
		else if ($fileType == 'image/svg')
			$fileType .= '+xml'; // image/svg+xml

		return $fileType;
	}

	/**
	 * Zips two arrays together Python-like
	 *
	 * Array1: [1, 7, 9]
	 * Array2: ['Health', 'Travel', 'Beauty']
	 *
	 * => [
	 *     [1, 'Health'],
	 *     [7, 'Travel'],
	 *     [6, 'Beauty'],
	 * ]
	 *
	 * If you specify keys (ex: ['id', 'name']) it will output associative arrays with given keys :
	 *
	 * => [
	 *     ['id' => 1, 'name' => 'Health'],
	 *     ['id' => 7, 'name' => 'Travel'],
	 *     ['id' => 6, 'name' => 'Beauty'],
	 * ]
	 *
	 * @param array $array1
	 * @param array $array2
	 * @param array|null $keys List array of two values [$keyForArray1, $keyForArray2]
	 * @return array
	 */
	public static function zip(array $array1, array $array2, array $keys = null): array {

		$shortestArrayLength = min(count($array1), count($array2));
		$zippedArray = [];

		for ($i = 0; $i < $shortestArrayLength; $i++) {

			if ($keys !== null) {
				$zippedArray[] = [
					$keys[0] => $array1[$i],
					$keys[1] => $array2[$i],
				];
			} else {
				$zippedArray[] = [$array1[$i], $array2[$i]];
			}
		}

		return $zippedArray;
	}
}

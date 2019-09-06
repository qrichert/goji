<?php

	namespace Goji\Core;

	use Exception;

	/**
	 * Class Logger
	 *
	 * @package Goji\Core
	 */
	class Logger {

		/* <CONSTANTS> */

		const CONSOLE = 0;
		const BROWSER = 1;
		const BOTH = 3;

		/**
		 * Log element into console or browser.
		 *
		 * @param $el
		 * @param int $output
		 */
		public static function log($el, $output = self::BROWSER): void {

			$console = $output == self::CONSOLE || $output == self::BOTH;
			$browser = $output == self::BROWSER || $output == self::BOTH;

			if (is_string($el) || is_numeric($el)) {

				if ($console)
					error_log($el);

				if ($browser)
					echo '<pre>' . $el . '</pre>';

			} else if (is_array($el)) {

				if ($console)
					error_log(print_r($el, true));

				if ($browser)
					echo '<pre>' . print_r($el, true) . '</pre>';

			} else {

				self::dump($el, $output);
			}
		}

		/**
		 * Log var_dump() into console or browser.
		 *
		 * @param $el
		 * @param int $output
		 */
		public static function dump($el, $output = self::BROWSER): void {

			$console = $output == self::CONSOLE || $output == self::BOTH;
			$browser = $output == self::BROWSER || $output == self::BOTH;

			if ($console) {
				ob_start();
				var_dump($el);
				error_log(ob_get_clean());
			}

			if ($browser) {
				echo '<pre>';
				var_dump($el);
				echo '</pre>';
			}
		}

		/**
		 * Log backtrace
		 *
		 * Adapted from comment at https://www.php.net/manual/en/function.debug-backtrace.php#112238
		 *
		 * @param int $output
		 */
		public static function backtrace($output = self::BROWSER): void {

			$e = new Exception();
			$trace = explode("\n", $e->getTraceAsString());

			// Reverse array to make steps line up chronologically
			$trace = array_reverse($trace);
			array_shift($trace); // Remove {main}
			array_pop($trace); // Remove call to this method
			$length = count($trace);
			$result = [];

			for ($i = 0; $i < $length; $i++)
				$result[] = ($i + 1)  . '.' . mb_substr($trace[$i], mb_strpos($trace[$i], ' ')); // Replace '#someNum' with '$i.', set the right ordering

			Logger::log("\t" . implode("\n\t", $result), $output);
		}
	}

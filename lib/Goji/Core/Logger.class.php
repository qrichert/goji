<?php

	namespace Goji\Core;

	class Logger {

		/* <CONSTANTS> */

		const CONSOLE = 0;
		const BROWSER = 1;
		const BOTH = 3;

		/**
		 * @param $el
		 * @param int $output
		 */
		public static function log($el, $output = self::CONSOLE): void {

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
		 * @param $el
		 * @param int $output
		 */
		public static function dump($el, $output = self::CONSOLE): void {

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
	}

<?php

	namespace Goji\Toolkit;

	/**
	 * Class SimpleMetrics
	 *
	 * @package Goji\Toolkit
	 */
	class SimpleMetrics {

		const METRICS_PATH = '../var/log/metrics/';

		public static function addPageView($page, $folder = 'pageview') {

			$folder = self::METRICS_PATH . $folder;

			if (mb_substr($folder, -1) != '/')
				$folder .= '/'; // metrics/pageview/

			$folder .= date('Y/m/d'); // metrics/pageview/2018/03/11

			if (!is_dir($folder))
				mkdir($folder, 0777, true);

			$file = $folder . '/' . $page . '.txt';

			if (!is_file($file)) { // If no page view for this day, we create it

				$file = fopen($file, 'a');
				fputs($file, '1');
				fclose($file);

			} else { // We increment the counter

				$file = fopen($file, 'r+');
				$count = ((int) fgets($file)) + 1;
				fseek($file, 0);
				fputs($file, $count);
				fclose($file);
			}
		}

		/*
			Functions : get all pageviews in array
						same but from date to date
						or over 1y, 1m, 1d

						{
							"home": {
								"2018": {
									"03": 123,
									"04": 97
								}
							}
						}
		*/
	}

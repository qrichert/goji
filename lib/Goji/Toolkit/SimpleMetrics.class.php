<?php

namespace Goji\Toolkit;

/**
 * Class SimpleMetrics
 *
 * This class just saves metrics in the most basic way:
 * year folder, month folder, day folder, file named as ID, file containing value
 *
 * It is up to other classes to process this data and / or save it to DB.
 *
 * For example, \Admin\Model\AnalyticsModel processes page views and saves them neatly to
 * the database, and removes SimpleMetrics files afterwards.
 *
 * @package Goji\Toolkit
 */
class SimpleMetrics {

	const METRICS_PATH = ROOT_PATH . '/var/log/metrics/';
	const PAGE_VIEW = 'pageview';

	/**
	 * @param string $page
	 */
	public static function addPageView(string $page): void {

		$folder = self::getMetricsFolderPath(self::PAGE_VIEW);

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

	/**
	 * Returns metrics folder
	 *
	 * Ends with trailing slash
	 *
	 * @param string $folder
	 * @return string
	 */
	public static function getMetricsFolderPath(string $folder = ''): string {

		$folder = self::METRICS_PATH . $folder;

		if (mb_substr($folder, -1) != '/')
			$folder .= '/'; // metrics/pageview/

		return $folder;
	}

	/**
	 * Removes empty folders in path
	 *
	 * @param string $path
	 */
	public static function cleanup($path = self::METRICS_PATH) {

		if (mb_substr($path, -1) != '/')
			$path .= '/';

		$folders = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);

		foreach ($folders as $folder) {

			if (mb_substr($folder, -1) != '/')
				$folder .= '/';

			self::cleanup($folder);

			// Ignore .DS_Store
			if (file_exists($folder . '.DS_Store'))
				@unlink($folder . '.DS_Store');

			// Remove folder if nothing in it, folder or files
			if (empty(glob($folder . '*', GLOB_NOSORT)))
				@rmdir($folder);
		}
	}
}

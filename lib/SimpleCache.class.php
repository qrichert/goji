<?php

	/*
		Caching a simple fragment:
		--------------------------

			if (SimpleCache::isValid($fragmentId)) {

				SimpleCache::loadFragment($fragmentId, true); // == echo SimpleCache::loadFragment($fragmentId); w/o 'return'

			} else {

				SimpleCache::startBuffer();
				...
				SimpleCache::cacheBuffer($fragmentId);

				OR SIMPLY:

				SimpleCache::cacheFragment($string, $fragmentId);
			}

		Preprocessing a file and caching it:
		------------------------------------

			if (SimpleCache::isValidFilePreprocessed($cacheId, $file)) {

				SimpleCache::loadFilePreprocessed($cacheId, true);

			} else {

				$content = ... // Loading and preprocessing file (ex: minify CSS)

				SimpleCache::cacheFilePreprocessed($content, $file, $cacheId);

				echo $content;
			}

		Caching an Array:
		-----------------

			// Can be used in destructor to store object values

			if (SimpleCache::isValid($fragmentId)) {

				$array = SimpleCache::loadArray($fragmentId);

			} else {

				$array = ... // Generate values

				SimpleCache::cacheArray($array, $fragmentId);
			}
	*/

	// If SimpleCache is called from a __destruct(), relative path may vary
	// Here we make sure it stays the same
	if (!defined('SIMPLE_CACHE_PATH')) {

		$cachePath = '../var/cache/';

		// Folder must exist before 'realpath()'
		if (!is_dir($cachePath))
			mkdir($cachePath, 0777, true);

		$cachePath = realpath($cachePath);

		if (substr($cachePath, -1) != '/')
			$cachePath .= '/';

		define('SIMPLE_CACHE_PATH', $cachePath);
	}

	class SimpleCache {

		const CACHE_PATH = SIMPLE_CACHE_PATH;
		const CACHE_FILE_EXTENSION = '.txt';
		const DEFAULT_CACHE_MAX_AGE = -1; // Default maximum page age in seconds (-1 = no limit)

		const TIME_1MIN = 60;
		const TIME_15MIN = 15 * self::TIME_1MIN;
		const TIME_30MIN = 30 * self::TIME_1MIN;
		const TIME_1H = 60 * self::TIME_1MIN;
		const TIME_12H = 12 * self::TIME_1H;
		const TIME_1DAY = 24 * self::TIME_1H;
		const TIME_1WEEK = 7 * self::TIME_1DAY;
		const TIME_1MONTH = 30 * self::TIME_1DAY;
		const TIME_1YEAR = 365 * self::TIME_1DAY;

/* <GENERIC BUFFERING FUNCTIONS> */

		// Start buffering
		public static function startBuffer() {
			ob_start();
		}

		// Close buffer and load fragment into variable
		public static function readBuffer() {
			return ob_get_clean();
		}

		// Close buffer and discard content
		public static function closeBuffer() {
			ob_end_clean();
		}

/* <GENERIC CACHE HANDLING FUNCTIONS> */

		// Remove fragment from cache
		public static function invalidateFragment($id) {

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			if (is_file($cacheFile))
				unlink($cacheFile);
		}

		// Delete all cached files
		public static function purgeCache() {

			$fragments = glob(self::CACHE_PATH . '*'); // Get all file names

			foreach ($fragments as $fragment) { // Iterate fragments

				if (is_file($fragment)) {
					unlink($fragment); // Delete file
				}
			}
		}

/* <GENERIC FRAGMENT CACHING> */

/* --- Save --- */

		// Cache HTML or TEXT (string)
		public static function cacheFragment($frag, $id) { // (string) $frag

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			if (!is_dir(self::CACHE_PATH))
				mkdir(self::CACHE_PATH, 0777, true);

			file_put_contents($cacheFile, $frag);

			return true;
		}

		// Close buffer and cache content
		public static function cacheBuffer($id, $returnContent = false) {

			// Get content
			$content = self::readBuffer();

			// Cache content
			if ($content !== false) {

				self::cacheFragment($content, $id);

				if ($returnContent)
					return $content;
				else
					return true;

			} else {

				return null;
			}
		}

/* --- Check Validity --- */

		// If is fragment cache and if cache is valid (not too old)
		public static function isValid($id, $maxAge = self::DEFAULT_CACHE_MAX_AGE) {

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			return (
				   file_exists($cacheFile) // File with corresponsding ID must exist
				&& ( // And also:
					$maxAge == -1 // Max age must be infinite
					|| (time() - filemtime($cacheFile)) <= $maxAge // OR, file must be recent enough
				)
			);
		}

		// See if file is not too recent to be written
		public static function canCache($id, $minAge = 0) { // 0 = Can't write the file twice at the same time

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			return (
					!file_exists($cacheFile) // File does not exist
				|| (time() - filemtime($cacheFile)) > $minAge // OR, file old enough
			);
		}

/* --- Load --- */

		// Load HTML or TEXT Fragment
		public static function loadFragment($id, $outputContent = false) {

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			if (file_exists($cacheFile)) {

				if ($outputContent)
					readfile($cacheFile);
				else
					return file_get_contents($cacheFile);
			}

			return null;
		}

/* <FILE SPECIFIC CACHING> */

/* --- Save --- */

		// Cache file content (usually preprocessed) with last edit timestamp
		// Can be multiple files too (array)
		public static function cacheFilePreprocessed($content, $file, $id) { // (string), (string|array), (string)

			// If single file we handle it as array, so we can use the same code for all
			if (!is_array($file))
				$file = array($file);

			$filesLastEditTimes = array();

			foreach ($file as $f) {

				if (!is_file($f))
					return false;

				$filesLastEditTimes[] = filemtime($f);
			}

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			/*
				Prepend file last edit time()s
				We combine them with '|' delimiter in the order of call.
				This is used to check if the file has been edited since
				last caching. If not edited, no need to process & cache again.
			*/
			$content = implode('|', $filesLastEditTimes) . PHP_EOL . $content;

			return self::cacheFragment($content, $id);
		}

/* --- Check Validity --- */

		// Look if original file has been edited since last caching
		public static function isValidFilePreprocessed($id, $file) {

			// If single file we handle it as array, so we can use the same code for all
			if (!is_array($file))
				$file = array($file);

			$filesLastEditTimes = array();

			foreach ($file as $f) {

				if (!is_file($f))
					return false;

				$filesLastEditTimes[] = filemtime($f);
			}

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			if (!file_exists($cacheFile)) // Cached file must exist
				return false;

			// Gettings original files last edit timestamp (= first line)
			$f = fopen($cacheFile, 'r');
			$timestamps = rtrim(fgets($f)); // rtrim() == Remove white-space at the end (= PHP_EOL)
			fclose($f);

			// Extract timestamps from string
			$timestamps = explode('|', $timestamps);

			// If file count doesn't match -> invalid
			if (count($filesLastEditTimes) != count($timestamps))
				return false;

			// If timestamps don't match -> invalid
			for ($i = 0; $i < count($filesLastEditTimes); $i++) {

				if ($filesLastEditTimes[$i] != $timestamps[$i])
					return false;
			}

			// If file count matches && timestamps match
			// No file has been edited since last caching
			return true;
		}

/* --- Load --- */

		// Load preprocessed content from cache, w/o first line (timestamp);
		public static function loadFilePreprocessed($id, $outputContent = false) {

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			$content = null;

			if (file_exists($cacheFile)) {

				$f = fopen($cacheFile, 'r');
				fgets($f); // Ignore first line / timestamp

				if ($outputContent)
					fpassthru($f); // Output content from caret to the end
				else
					$content = stream_get_contents($f); // Read from caret to the end (= second line to end)

				fclose($f);
			}

			return $content;
		}

/* <ARRAY SPECIFIC CACHING> */

/* --- Save --- */

		// Shortcut for caching arrays
		public static function cacheArray($arr, $id) {

			if (is_array($arr)) {

				$arr = json_encode($arr);

				self::cacheFragment($arr, $id);

				return true;

			} else {

				return false;
			}
		}

/* --- Load --- */

		public static function loadArray($id) {

			$arr = self::loadFragment($id);

			if ($arr !== null)
				$arr = json_decode($arr, true);

			return $arr;
		}
	}

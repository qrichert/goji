<?php

	namespace Goji;

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

	/**
	 * A class to use caching in a very simple way.
	 *
	 * This class is made to be used statically. Following are some examples of use.
	 *
	 * Caching a simple fragment:
	 * --------------------------
	 *
	 * if (SimpleCache::isValid($fragmentId)) {
	 *
	 * 		SimpleCache::loadFragment($fragmentId, true); // == echo SimpleCache::loadFragment($fragmentId); w/o 'return'
	 *
	 * } else {
	 *
	 * 		SimpleCache::startBuffer();
	 * 		...
	 * 		SimpleCache::cacheBuffer($fragmentId);
	 *
	 * 		OR SIMPLY:
	 *
	 * 		SimpleCache::cacheFragment($string, $fragmentId);
	 * }
	 *
	 * Preprocessing a file and caching it:
	 * ------------------------------------
	 *
	 * if (SimpleCache::isValidFilePreprocessed($cacheId, $file)) {
	 *
	 * 		SimpleCache::loadFilePreprocessed($cacheId, true);
	 *
	 * } else {
	 *
	 * 		$content = ... // Loading and preprocessing file (ex: minify CSS)
	 *
	 * 		SimpleCache::cacheFilePreprocessed($content, $file, $cacheId);
	 *
	 * 		echo $content;
	 * }
	 *
	 * Caching an Array:
	 * -----------------
	 *
	 * // Can be used in destructor to store object values
	 *
	 * if (SimpleCache::isValid($fragmentId)) {
	 *
	 * 		$array = SimpleCache::loadArray($fragmentId);
	 *
	 * } else {
	 *
	 * 		$array = ... // Generate values
	 *
	 * 		SimpleCache::cacheArray($array, $fragmentId);
	 * }
	 *
	 */
	class SimpleCache {

		const CACHE_PATH = SIMPLE_CACHE_PATH;
		const CACHE_FILE_EXTENSION = '.cache.txt';
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

		/**
		 * Start buffering.
		 *
		 * Used to collect all output for later use.
		 * You could use that if you want to cache a whole page for instance.
		 */
		public static function startBuffer() {
			ob_start();
		}

		/**
		 * Stop buffering and save the collected output.
		 *
		 * Stops the buffer and loads all the output that has been collected into a variable.
		 * This variable can then be stored.
		 */
		public static function readBuffer() {
			return ob_get_clean();
		}

		/**
		 * Stop buffering and discard content.
		 *
		 * Stops the buffer and forgets all the output that has been collected.
		 * It's like a "cancel" button.
		 */
		public static function closeBuffer() {
			ob_end_clean();
		}

/* <GENERIC CACHE HANDLING FUNCTIONS> */

		/**
		 * Remove specific fragment from cache.
		 *
		 * The cache consists of files with unique file names (= ID).
		 * This functions deletes the file with the given ID.
		 *
		 * @param string $id The ID of the fragment
		 */
		public static function invalidateFragment($id) {

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			if (is_file($cacheFile))
				unlink($cacheFile);
		}

		/**
		 * Purge cache.
		 *
		 * Empties the cache by deleting ALL cache files.
		 */
		public static function purgeCache() {

			// Get all *.txt file names (unsorted = faster)
			$fragments = glob(self::CACHE_PATH . '*' . self::CACHE_FILE_EXTENSION, GLOB_NOSORT);

			foreach ($fragments as $fragment) { // Iterate fragments

				if (is_file($fragment)) {
					unlink($fragment); // Delete file
				}
			}
		}

/* <GENERIC FRAGMENT CACHING> */

/* --- Save --- */

		/**
		 * Cache a string.
		 *
		 * The string could be anything: text, HTML, etc.
		 *
		 * @param string $frag The text to cache
		 * @param string $id The ID of the cached fragment
		 * @return bool Returns always true
		 */
		public static function cacheFragment($frag, $id) { // (string) $frag

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			if (!is_dir(self::CACHE_PATH))
				mkdir(self::CACHE_PATH, 0777, true);

			file_put_contents($cacheFile, $frag);

			return true;
		}

		/**
		 * Close buffer and cache content.
		 *
		 * This is a shortcut function that stops the buffer and caches its content
		 * at the same time.
		 *
		 * @param string $id The ID of the cached fragment
		 * @param bool $returnContent (optional) If set to true, the cached content will be returned. false by default.
		 * @return bool|string|null true if successful, null if not, string if successful and $returnContent = true
		 */
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

		/**
		 * If is fragment cache and if cache is valid (not too old).
		 *
		 * @param string $id Fragment ID
		 * @param int $maxAge (optional) Infinite by default
		 * @return bool true if fragment is valid (exists and not too old), false if not (doesn't exist or too old)
		 */
		public static function isValid($id, $maxAge = self::DEFAULT_CACHE_MAX_AGE) {

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			return (
				file_exists($cacheFile) // File with corresponding ID must exist
				&& ( // And also:
					$maxAge == -1 // Max age must be infinite
					|| (time() - filemtime($cacheFile)) <= $maxAge // OR, file must be recent enough
				)
			);
		}

		/**
		 * See if file is not too recent to be written.
		 *
		 * This functions uses time() and filemtime().
		 * Precision of both may vary according to file system. So adapt $minAge param.
		 *
		 * @param string $id Fragment ID
		 * @param int $minAge (optional) Minimum age the cache file must have to be overwritten. 0 by default.
		 * @return bool true if file old enough, false if too recent
		 */
		public static function canCache($id, $minAge = 0) { // 0 = Can't write the file twice at the same time

			$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

			return (
				!file_exists($cacheFile) // File does not exist
				|| (time() - filemtime($cacheFile)) > $minAge // OR, file old enough
			);
		}

/* --- Load --- */

		/**
		 * Load HTML or TEXT Fragment
		 *
		 * @param string $id Fragment ID
		 * @param bool $outputContent (optional) echo fragment's content instead of returning it. false by default
		 * @return string|null string if $outputContent = false (default), null if echoed or fragment doesn't exist
		 */
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

		/**
		 * Caching method designed specifically for files (usually preprocessed).
		 *
		 * When you preprocess files before display, it can be useful to cache
		 * the preprocessed version instead of re-preprocessing it for each request.
		 *
		 * For example, if you minify and merge all CSS files into one you could
		 * use this function to cache the output.
		 *
		 * The advantage over simply caching the output as a string is that
		 * this method saves the last modified timestamp of the file.
		 *
		 * Thanks to that, you can update the cache or not according to whether the
		 * original file has been modified or not.
		 *
		 * If you cache a single file, give the preprocessed content as $content and
		 * the file full path as $file. The path will be used to determine the timestamp
		 * of the file's last edit. So that if you update the file, you can detect it
		 * and updated he cached version accordingly.
		 *
		 * If you cache merged files (so multiple files combined into one), give the
		 * preprocessed content as $content. That is, one string containing the result
		 * of the merge. The whole thing. Then give the paths for all concerned (original)
		 * files as an array with $file. This method will collect last edit timestamps for
		 * all original files, so that if one gets modified you can detect it and update
		 * the cached version.
		 *
		 * @param string $content Text to be cached
		 * @param string|array $file A single file or a list of files
		 * @param string $id Fragment ID
		 * @return bool true if success, false if error
		 */
		public static function cacheFilePreprocessed($content, $file, $id) {

			// If single file we handle it as array, so we can use the same code for all
			if (!is_array($file))
				$file = array($file);

			$filesLastEditTimes = array();

			foreach ($file as $f) {

				if (!is_file($f))
					return false;

				$filesLastEditTimes[] = filemtime($f);
			}

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

		/**
		 * Look if original file has been edited since last caching.
		 *
		 * This is the complement for cacheFilePreprocessed().
		 *
		 * It compares the last edit timestamp(s) of the original file(s) with those that
		 * have been cached. If the timestamps match, the cache is still valid. If one or
		 * more don't match, cache is outdated.
		 *
		 * @param string $id Fragment ID
		 * @param string|array $file A single file or a list of files
		 * @return bool true if cache is valid, false if not (one or more original file has been edited)
		 */
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

		/**
		 * Load preprocessed content from cache.
		 *
		 * Load a preprocessed fragment from the cache.
		 *
		 * loadFragment() just reads the cache file and returns it. But with preprocessed
		 * files, the timestamp(s) are saved within the cached file. So loadFragment()
		 * would return the content and the timestamp(s) which we don't want.
		 *
		 * This method however returns the content alone, as we want it. Meaning a clean
		 * version without the timestamp(s).
		 *
		 * @param string $id Fragment ID
		 * @param bool $outputContent (optional) echo fragment's content instead of returning it. false by default
		 * @return string|null string if $outputContent = false (default), null if echoed or fragment doesn't exist
		 */
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

		/**
		 * Caching method designed specifically for arrays.
		 *
		 * Caches the content of an array.
		 *
		 * @param array $arr Array to cache
		 * @param string $id Fragment ID
		 * @return bool true if successful, false if not
		 */
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

		/**
		 * Load a cached array.
		 *
		 * @param string $id Fragment ID
		 * @return array|null array if successful, null if not
		 */
		public static function loadArray($id) {

			$arr = self::loadFragment($id);

			if ($arr !== null)
				$arr = json_decode($arr, true);

			return $arr;
		}
	}

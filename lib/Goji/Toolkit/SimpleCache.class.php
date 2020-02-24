<?php

namespace Goji\Toolkit;

use Exception;
use Goji\Core\ConfigurationLoader;

/**
 * Class SimpleCache
 *
 * A class to use caching in a very simple way.
 *
 * This class is made to be used statically.
 *
 * For more information about HTTP caching, see:
 * https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching
 *
 * Following are some examples of use.
 *
 * Caching a simple fragment:
 * --------------------------
 *
 * ```php
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
 * ```
 *
 * Preprocessing a file and caching it:
 * ------------------------------------
 *
 * ```php
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
 * ```
 *
 * Caching an Array:
 * -----------------
 *
 * ```php
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
 * ```
 *
 * @package Goji\Toolkit
 */
class SimpleCache {

	/* <ATTRIBUTES> */

	private static $m_isInitialized;
	private static $m_useCache;

	/* <CONSTANTS> */

	const CONFIG_FILE = ROOT_PATH . '/config/caching.json5';

	const CACHE_PATH = ROOT_PATH . '/var/cache/';
	const CACHE_FILE_EXTENSION = '.cache';
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

	const HTTP_CACHE_CONTROL_NO_CACHE = 'no-cache';
	const HTTP_CACHE_CONTROL_NO_STORE = 'no-store';
	const HTTP_CACHE_CONTROL_PUBLIC = 'public';
	const HTTP_CACHE_CONTROL_PRIVATE = 'private';

	const E_WRONG_HTTP_CACHE_RESTRICTION_SETTING = 0;
	const E_WRONG_HTTP_CACHE_PRIVACY_SETTING = 1;
	const E_HTTP_CACHE_MAX_AGE_INVALID = 3;

	/**
	 * Read configuration and initialize attributes.
	 *
	 * This function is designed to load configuration only on the first use of
	 * a class method.
	 *
	 * @param string $configFile
	 */
	private static function initialize(string $configFile = self::CONFIG_FILE): void {

		if (self::$m_isInitialized)
			return;

		if (!file_exists(self::CACHE_PATH))
			mkdir(self::CACHE_PATH, 0777, true);

		try {

			// Can't use cache for this one, because it would call isValid*() and isValid*() calls this function
			$config = ConfigurationLoader::loadFileToArray($configFile, false);

			self::$m_useCache = isset($config['use_cache']) && $config['use_cache'] === true;

		} catch (Exception $e) {

			self::$m_useCache = true;
		}

		self::$m_isInitialized = true;
	}

/* <GENERIC BUFFERING FUNCTIONS> */

	/**
	 * Start buffering.
	 *
	 * Used to collect all output for later use.
	 * You could use that if you want to cache a whole page for instance.
	 */
	public static function startBuffer(): void {
		ob_start();
	}

	/**
	 * Stop buffering, save the collected output and delete current output buffer
	 *
	 * From the doc: ob_get_clean() essentially executes both ob_get_contents() and ob_end_clean().
	 *
	 * Stops the buffer and loads all the output that has been collected into a variable.
	 * This variable can then be stored.
	 *
	 * @return string
	 */
	public static function readBuffer(): string {
		return ob_get_clean();
	}

	/**
	 * Stop buffering and discard content.
	 *
	 * Stops the buffer and forgets all the output that has been collected.
	 * It's like a "cancel" button.
	 */
	public static function closeBuffer(): void {
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
	public static function invalidateFragment(string $id): void {

		$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

		if (is_file($cacheFile))
			unlink($cacheFile);
	}

	/**
	 * Purge cache.
	 *
	 * Empties the cache by deleting ALL cache files.
	 *
	 * @return array Fragments' name and size
	 */
	public static function purgeCache(): array {

		$fragmentsRemoved = [];

		// Get all *.txt file names (unsorted = faster)
		$fragments = glob(self::CACHE_PATH . '*' . self::CACHE_FILE_EXTENSION, GLOB_NOSORT);

		foreach ($fragments as $fragment) { // Iterate fragments

			if (is_file($fragment)) {

				$fragmentSize = filesize($fragment);

				if (unlink($fragment)) { // Delete fragment

					$fragmentsRemoved[] = [
						'fragment' => $fragment,
						'size' => $fragmentSize
					];
				}
			}
		}

		return $fragmentsRemoved;
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
	 * @return bool The function returns true on success, or false on failure.
	 */
	public static function cacheFragment(string $frag, string $id): bool {

		self::initialize();

		if (!self::$m_useCache)
			return false;

		$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

		return file_put_contents($cacheFile, $frag) !== false;
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
	public static function cacheBuffer(string $id, bool $returnContent = false) {

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
	public static function isValid(string $id, int $maxAge = self::DEFAULT_CACHE_MAX_AGE): bool {

		self::initialize();

		if (!self::$m_useCache)
			return false;

		$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

		return (
			is_file($cacheFile) // File with corresponding ID must exist
			&& ( // And also:
				$maxAge == -1 // Max age must be infinite
				|| (time() - filemtime($cacheFile)) <= $maxAge // OR, file must be recent enough
			)
		);
	}

/* --- Load --- */

	/**
	 * Load HTML or TEXT Fragment
	 *
	 * @param string $id Fragment ID
	 * @param bool $outputContent (optional) echo fragment's content instead of returning it. false by default
	 * @return string|null string if $outputContent = false (default), null if echoed or error
	 */
	public static function loadFragment(string $id, bool $outputContent = false): ?string {

		$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

		if (is_file($cacheFile)) {

			if ($outputContent) {
				readfile($cacheFile); // (int|false)
			} else {
				$fragment = file_get_contents($cacheFile); // (string|false)
				return $fragment !== false ? $fragment : null;
			}
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
	public static function cacheFilePreprocessed(string $content, $file, string $id): bool {

		// If single file we handle it as array, so we can use the same code for all
		$file = (array) $file;

		$filesLastEditTimes = [];

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
	public static function isValidFilePreprocessed(string $id, $file): bool {

		self::initialize();

		if (!self::$m_useCache)
			return false;

		// If single file we handle it as array, so we can use the same code for all
		$file = (array) $file;

		$filesLastEditTimes = [];

		foreach ($file as $f) {

			if (!is_file($f))
				return false;

			$filesLastEditTimes[] = filemtime($f);
		}

		$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

		if (!is_file($cacheFile)) // Cached file must exist
			return false;

		// Getting original files' last edit timestamp (= first line)
		$f = fopen($cacheFile, 'r');
		$timestamps = rtrim(fgets($f)); // rtrim() == Remove white-space at the end (= PHP_EOL)
		fclose($f);

		// Extract timestamps from string
		$timestamps = explode('|', $timestamps);

		// If file count doesn't match -> invalid
		if (count($filesLastEditTimes) != count($timestamps))
			return false;

		// If timestamps don't match -> invalid
		$count = count($filesLastEditTimes);
		for ($i = 0; $i < $count; $i++) {

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
	public static function loadFilePreprocessed(string $id, bool $outputContent = false): ?string {

		$cacheFile = self::CACHE_PATH . $id . self::CACHE_FILE_EXTENSION;

		$content = null;

		if (is_file($cacheFile)) {

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
	public static function cacheArray(array $arr, string $id): bool {

		$arr = json_encode($arr);

		return self::cacheFragment($arr, $id);
	}

/* --- Load --- */

	/**
	 * Load a cached array.
	 *
	 * @param string $id Fragment ID
	 * @return array|null array if successful, null if not
	 */
	public static function loadArray($id): ?array {

		$arr = self::loadFragment($id);

		if ($arr !== null)
			$arr = json_decode($arr, true);

		return $arr;
	}

/* <OTHER FUNCTIONS> */

	/**
	 * Converts file path to cache ID.
	 *
	 * js/main.js -> // js-js-main--js
	 *
	 * @param string|array $file
	 * @return string
	 */
	public static function cacheIDFromFileFullPath($file): string {

		$cacheId = is_array($file) ? implode('|', $file) : $file;
			$cacheId = mb_strtolower($cacheId); // js/main.js
			$cacheId = str_replace('.', '--', $cacheId); // js/main--js
			$cacheId = preg_replace('#\W#', '-', $cacheId); // js-main--js

			$file = is_array($file) ? end($file) : $file;
			if (!empty($file) && is_string($file))
				$cacheId = pathinfo($file, PATHINFO_EXTENSION) . '-' . $cacheId; // js-js-main--js
			else
				$cacheId = 'unknown-' . $cacheId; // unknown-js-main--js

		return $cacheId;
	}

	/**
	 * Transforms string into valid cache ID.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function cacheIDFromString(string $string): string {

		$cacheId = $string; // _String:-404-*
			$cacheId = mb_strtolower($cacheId); // _string:-404-*
			$cacheId = preg_replace('#\W#', '-', $cacheId); // _string--404--
			$cacheId = str_replace('_', '-', $cacheId); // -string--404--

		return $cacheId;
	}

/* <HTTP CACHING HEADERS> */

	/**
	 * HTTP Entity Tag (ETag) token
	 *
	 * ETag is sort of a state token. It's an unique ID of a file and its content (like a hash).
	 * It is used by to browser to check whether it should update the file in cache or not.
	 *
	 * For example a file hello.txt containing 'hello'. We could hash it and obtain a token
	 * like a5rX98j. So as long as the token remains the same, the browser know it doesn't
	 * need to update the file since it hasn't changed.
	 *
	 * But let's change the content of the file to 'hello, world!'. The new hash is klg83Ds.
	 * The browser notices it has changed and thus request the updated version.
	 *
	 * ETag is checked once (and only once) the cache has expired. Once the file in cache has expired
	 * (max-age) the browsers requests the ETag. If it's the same, the file is cached again for max-age
	 * (part of the HTTP response) without re-download. It it doesn't match, the new version is downloaded.
	 *
	 * No data is transferred (apart from the header) if ETag is valid.
	 *
	 * @param string $token
	 * @param bool $replace
	 */
	private static function setHttpETag(string $token, bool $replace = true): void {
		header("ETag: \"$token\"", $replace);
	}

	/**
	 * HTTP Cache Control Restriction (no-cache | no-store)
	 *
	 * no-cache: With the no-cache header, the browser CAN cache the file but MUST re-validate it for
	 * each request. It's like max-age was set to 0. If the file is still the same (see ETag) it uses
	 * the cached version, else it re-downloads it.
	 *
	 * no-store: With no-store, the cache is completely disabled for the given file. This is used for
	 * privacy or if the file always changes. It's best not to store banking information and so on
	 * inside the browser cache.
	 *
	 * @param string $directive (HTTP_CACHE_CONTROL_NO_STORE | HTTP_CACHE_CONTROL_NO_CACHE)
	 * @param bool $replace
	 */
	private static function setHttpCacheControlRestriction(string $directive, bool $replace = false): void {
		header("Cache-Control: $directive", $replace);
	}

	/**
	 * HTTP Cache Control Privacy (public | private)
	 *
	 * public: public indicates the file can be cache in any case, regardess of other settings
	 * like HTTP authentication or non-cachable status codes. You don't need it in most cases
	 * where you'd use max-age, because max-age implies public.
	 *
	 * private: private means the end user (browser) is allowed to cache the file, but not any intermediate
	 * cache system (like a CDN). This is meant for privacy also.
	 *
	 * @param string $directive (HTTP_CACHE_CONTROL_PUBLIC | HTTP_CACHE_CONTROL_PRIVATE)
	 * @param bool $replace
	 */
	private static function setHttpCacheControlPrivacy(string $directive, bool $replace = false): void {
		header("Cache-Control: $directive", $replace);
	}

	/**
	 * HTTP Cache Control Max Age (in seconds)
	 *
	 * How long (in seconds) the browser should consider the cached file valid until it must re-validate.
	 * Be careful, no re-validation will occur until the 'countdown' is over (or the user empties his cache).
	 *
	 * If you set a CSS file for 1 year for example, and the user doesn't empty his cache, he will keep
	 * the same design CSS file for 1 year, regardless of how many updates you make.
	 *
	 * A way around it is changing the name of the file.
	 *
	 * /!\ If you don't use any cache directive, it's up to the browser to decide what to do /!\
	 *
	 * @param int $maxAge In seconds
	 * @param bool $replace
	 */
	private static function setHttpCacheControlMaxAge(int $maxAge, bool $replace = false): void {
		header("Cache-Control: max-age=$maxAge", $replace);
	}

	/**
	 * Set HTTP Caching Policy.
	 *
	 * Use an array like:
	 *
	 * $policy = [
	 *      'etag' => 'token',
	 *      'restriction' => SimpleCache::HTTP_CACHE_CONTROL_NO_CACHE,
	 *      'privacy' => SimpleCache::HTTP_CACHE_CONTROL_PUBLIC,
	 *      'max-age' => SimpleCache::TIME_1DAY
	 * ]
	 *
	 * @param array $policy
	 * @throws \Exception
	 */
	public static function setHttpCachingPolicy(array $policy): void {

		$eTag = $policy['etag'] ?? null;
		$restriction = $policy['restriction'] ?? null;
		$privacy = $policy['privacy'] ?? null;
		$maxAge = $policy['max-age'] ?? null;

		if (isset($eTag)) {
			self::setHttpETag($eTag);
		}

		// Clear previously set caching headers
		header_remove('ETag');
		header_remove('Cache-Control');

		if (isset($restriction)) {

			if ($restriction === self::HTTP_CACHE_CONTROL_NO_CACHE
			    || $restriction === self::HTTP_CACHE_CONTROL_NO_STORE)
				self::setHttpCacheControlRestriction($restriction);
			else
				throw new Exception("HTTP Cache Control Restriction not valid: $restriction. Must be either no-cache or no-store.", self::E_WRONG_HTTP_CACHE_RESTRICTION_SETTING);
		}

		if (isset($privacy)) {

			if ($privacy === self::HTTP_CACHE_CONTROL_PUBLIC
			    || $privacy === self::HTTP_CACHE_CONTROL_PRIVATE)
				self::setHttpCacheControlPrivacy($privacy);
			else
				throw new Exception("HTTP Cache Control Privacy not valid: $privacy. Must be either public or private.", self::E_WRONG_HTTP_CACHE_PRIVACY_SETTING);
		}

		if (isset($maxAge)) {

			if (is_numeric($maxAge))
				// To positive int
				self::setHttpCacheControlMaxAge(abs((int) $maxAge));
			else
				throw new Exception('Http Cache Max Age invalid. Must be a numeric value in seconds.');
		}
	}

	/*private static function httpCacheMustRevalidate(){
		isset(If-None-Match) ?
		token = If-None-Match

		if (token === currentToken)
		 http header: 304 not modified
		 return false

		else
		 http set Etag: new token
		 http max-age = XXX
		 return true
	}*/

	/*private static function httpIfModifiedSince() {
		Similar to ETag but with timestamp
		If-Modified-Since: Fri, 13 Jul 2018 10:49:23 GMT
			http set last-modified: Fri, 10 Jul 2018 10:49:23 GMT /
	}*/
}

<?php

	namespace Goji\Core;

	use Goji\Parsing\JSON5;
	use Goji\Toolkit\SimpleCache;
	use Exception;

	/**
	 * Class ConfigurationLoader
	 *
	 * @package Goji\Core
	 */
	class ConfigurationLoader {

		/* <CONSTANTS> */

		const E_FILE_DOES_NOT_EXIST = 0;
		const E_FILE_CANNOT_BE_READ = 1;

		/**
		 * Return configuration file as array.
		 *
		 * @param string $file
		 * @return array
		 * @throws \Exception
		 */
		public static function loadFileToArray($file) {

			if (!is_string($file) || !is_file($file))
				throw new Exception("Configuration file doesn't exist. (" . strval($file) . ")", self::E_FILE_DOES_NOT_EXIST);

			$extension = pathinfo($file, PATHINFO_EXTENSION);

			switch ($extension) {
				case 'json':    return self::loadJSONFileToArray($file);    break;
				case 'json5':   return self::loadJSON5FileToArray($file);   break;
				default:
					throw new Exception("Configuration file cannot be read. (" . $file . ")", self::E_FILE_CANNOT_BE_READ);
					break;
			}

		}

		/**
		 * Load configuration from JSON file.
		 *
		 * @param string $file
		 * @return array
		 * @throws \Exception
		 */
		private static function loadJSONFileToArray($file): array {

			$config = file_get_contents($file);
			$config = json_decode($config, true);

			if (!is_array($config))
				throw new Exception("Configuration file cannot be read. (" . $file . ")", self::E_FILE_CANNOT_BE_READ);

			return $config;
		}

		/**
		 * Load configuration from JSON5 file.
		 *
		 * JSON5 is converted to JSON and cached automatically.
		 * Cache expires when config file is modified.
		 *
		 * @param string $file
		 * @return array
		 * @throws \Exception
		 */
		private static function loadJSON5FileToArray($file) {

			// Generating cache ID
			$cacheId = SimpleCache::cacheIDFromFileFullPath($file);
			$config = '';

			// We cache it as JSON, so we don't have to re-convert it each time.
			if (SimpleCache::isValidFilePreprocessed($cacheId, $file)) { // Get cached version (JSON)

				$config = SimpleCache::loadFilePreprocessed($cacheId);

			} else { // Convert JSON5 to JSON and cache it

				$config = file_get_contents($file);
				$config = JSON5::toJSON($config);

				SimpleCache::cacheFilePreprocessed($config, $file, $cacheId);
			}

			// JSON to Array
			$config = json_decode($config, true);

			if (!is_array($config))
				throw new Exception("Configuration file cannot be read. (" . $file . ")", self::E_FILE_CANNOT_BE_READ);

			return $config;
		}
	}

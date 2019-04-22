<?php

	namespace Goji\Core;

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
		private static function loadJSONFileToArray($file) {

			$config = file_get_contents($file);
			$config = json_decode($config, true);

			if (!is_array($config))
				throw new Exception("Configuration file cannot be read. (" . $file . ")", self::E_FILE_CANNOT_BE_READ);

			return $config;
		}
	}

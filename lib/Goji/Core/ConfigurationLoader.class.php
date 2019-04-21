<?php

	namespace Goji\Core;

	use Exception;

	/**
	 * Class ConfigurationLoader
	 *
	 * @package Goji\Core
	 */
	class ConfigurationLoader {

		/**
		 * Return configuration file as array.
		 *
		 * @param string $file
		 * @return array
		 * @throws \Exception
		 */
		public static function loadFileToArray($file) {

			if (!is_string($file) || !is_file($file))
				throw new Exception("Configuration file doesn't exist. (" . strval($file) . ")", 0);

			$extension = pathinfo($file, PATHINFO_EXTENSION);

			switch ($extension) {
				case 'json':    return self::loadJSONFileToArray($file);    break;
				default:
					throw new Exception("Configuration file cannot be read. (" . $file . ")", 1);
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
				throw new Exception("Configuration file cannot be read. (" . $file . ")", 1);

			return $config;
		}
	}

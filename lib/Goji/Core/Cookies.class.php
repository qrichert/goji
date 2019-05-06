<?php

	namespace Goji\Core;

	use Exception;

	/**
	 * Class Cookies
	 *
	 * @package Goji\Core
	 */
	class Cookies {

		/* <ATTRIBUTES> */

		private static $m_isInitialized;
		private static $m_useCookies;
		private static $m_secure;
		private static $m_cookiesPrefix;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/cookies.json5';

		/**
		 * Read configuration and initialize attributes.
		 *
		 * This function is designed to load configuration only on the first use of
		 * a class method.
		 *
		 * @param string $configFile
		 */
		private static function initialize($configFile = self::CONFIG_FILE): void {

			if (self::$m_isInitialized)
				return;

			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

				self::$m_useCookies = isset($config['use_cookies']) && $config['use_cookies'] === true;
				self::$m_secure = self::isSecure($config['secure']);
				self::$m_cookiesPrefix = $config['cookies_prefix'] ?? '';

			} catch (Exception $e) {

				self::$m_useCookies = true;
				self::$m_secure = self::isSecure();
				self::$m_cookiesPrefix = '';
			}

			self::$m_isInitialized = true;
		}

		/**
		 * @param null $secure
		 * @return bool
		 */
		private static function isSecure($secure = null): bool {

			if (!isset($secure)) {

				if (!empty($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS']) !== 'off') {
					return true;
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
				        || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && mb_strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on') {
					return true;
				} else {
					return (isset($_SERVER['SERVER_PORT']) && intval($_SERVER['SERVER_PORT']) == 443);
				}
			}

			if (isset($secure) && $secure === true)
				return true;

			// $secure != true
			return false;
		}

		/**
		 * Create or set the value of a cookie.
		 *
		 * @param string $name
		 * @param string $value
		 * @param int $expireIn
		 * @param string $path
		 * @param string $domain
		 * @param bool $secure default = null -> use config
		 * @param bool $httponly
		 * @return bool
		 */
		public static function set(string $name, string $value = '', int $expireIn = -1,
		                           string $path = '/', string $domain = '', bool $secure = null,
		                           bool $httponly = true): bool {

			self::initialize();

			if (!self::$m_useCookies)
				return false;

			$name = self::$m_cookiesPrefix . $name;

			if ($expireIn == -1)
				$expireIn = 10 * 12 * 30 * 24 * 3600; // 10 years

			$expireIn = time() + $expireIn;

			if (!isset($secure))
				$secure = self::$m_secure;

			return setcookie($name, $value, $expireIn, $path, $domain, $secure, $httponly);
		}

		/**
		 * Get a value from a cookie.
		 *
		 * @param string $name
		 * @return mixed|string
		 */
		public static function get(string $name) {

			self::initialize();

			$name = self::$m_cookiesPrefix . $name;

			if (isset($_COOKIE[$name]))
				return $_COOKIE[$name];
			else
				return null;
		}

		/**
		 * Delete a specific cookie.
		 *
		 * @param $name
		 * @param string $path
		 * @param string $domain
		 * @return bool
		 */
		public static function unset($name, $path = '/', $domain = ''): bool {

			self::initialize();

			$name = self::$m_cookiesPrefix . $name;

			unset($_COOKIE[$name]);

			return setcookie($name, '', time() - 24 * 3600, $path, $domain, false, true);
		}

		/**
		 * Delete all cookies.
		 *
		 * @return bool
		 */
		public static function purge(): bool {

			$purgeSuccessful = true;

			if (isset($_SERVER['HTTP_COOKIE'])) {

				$cookies = explode(';', $_SERVER['HTTP_COOKIE']);

				foreach($cookies as $cookie) {

					$parts = explode('=', $cookie);
					$name = trim($parts[0]);

					if (!setcookie($name, '', time() - 24 * 3600))
						$purgeSuccessful = false;

					if (!setcookie($name, '', time() - 24 * 3600, '/'))
						$purgeSuccessful = false;
				}

			} else {
				$purgeSuccessful = false;
			}

			return $purgeSuccessful;
		}
	}

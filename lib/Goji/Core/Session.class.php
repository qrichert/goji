<?php

	namespace Goji\Core;

	use Exception;

	/**
	 * Class Session
	 *
	 * @package Goji\Core
	 */
	class Session {

		/* <ATTRIBUTES> */

		private static $m_isInitialized;

		/**
		 * Read configuration and initialize attributes.
		 *
		 * This function is designed to load configuration only on the first use of
		 * a class method.
		 */
		private static function initialize(): void {

			if (self::$m_isInitialized)
				return;

			self::start();

			self::$m_isInitialized = true;
		}

		/**
		 * Whether a $_SESSION is currently active or not.
		 *
		 * @return bool
		 */
		public static function isActive(): bool {

			return session_status() === PHP_SESSION_ACTIVE;
		}

		/**
		 * Start $_SESSION if not started
		 *
		 * Returns true if a $_SESSION is active after call, false if not.
		 * (Regardless of whether it has activated it or if it was already active before).
		 *
		 * @return bool
		 */
		public static function start(): bool {

			$status = session_status();

			switch ($status) {
				case PHP_SESSION_DISABLED:  return false;           break;
				case PHP_SESSION_NONE:      return session_start(); break;
				case PHP_SESSION_ACTIVE:    return true;            break;
			}

			return false;
		}

		/**
		 * Destroy $_SESSION
		 *
		 * @param bool $purge
		 * @return bool
		 */
		public static function destroy(bool $purge = false): bool {

			// A $_SESSION must be active to be destroyed
			// So it is either already active, or we start it.
			if (self::active() || self::start()) {

				if ($purge)
					self::purge();

				return session_destroy();
			}

			return false;
		}

		/**
		 * Delete all $_SESSION variables.
		 * @return bool
		 */
		public static function purge(): bool {

			if (self::active() || self::start()) {

				$_SESSION = array();
			}

			return false;
		}

		/**
		 * Set a specific $_SESSION variable.
		 *
		 * @param string $name
		 * @param null $value
		 */
		public static function set(string $name, $value = null): void {

			self::initialize();

			$_SESSION[$name] = $value;
		}

		/**
		 * Get a specific $_SESSION variable.
		 *
		 * @param string $name
		 * @return mixed|null
		 */
		public static function get(string $name) {

			self::initialize();

			if (isset($_SESSION[$name]))
				return $_SESSION[$name];
			else
				return null;
		}

		/**
		 * Delete a specific $_SESSION variable.
		 *
		 * @param string $name
		 */
		public static function unset(string $name): void {

			self::initialize();

			if (isset($_SESSION[$name]))
				unset($_SESSION[$name]);
		}
	}

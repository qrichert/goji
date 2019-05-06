<?php

	namespace Goji\Security;

	use Goji\Core\ConfigurationLoader;
	use Exception;

	/**
	 * Class Passwords
	 *
	 * @package Goji\Security
	 */
	class Passwords {

		/* <ATTRIBUTES> */

		private static $m_isInitialized;
		private static $m_configuration;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/passwords.json5';

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

				self::$m_configuration = ConfigurationLoader::loadFileToArray($configFile);

			} catch (Exception $e) {

				self::$m_configuration = array();
			}

			self::$m_isInitialized = true;
		}

		/**
		 * Get a property from config file.
		 *
		 * Always returns a string.
		 * The string will be empty if the property isn't set.
		 *
		 * @param string $key
		 * @return string
		 */
		public static function getProperty(string $key): string {

			self::initialize();

			return (string) self::$m_configuration[$key] ?? '';
		}

		/**
		 * Returns pepper set in config file.
		 *
		 * @return string
		 */
		public static function getPepperBefore(): string {
			return self::getProperty('pepper_before');
		}

		/**
		 * Returns pepper set in config file.
		 *
		 * @return string
		 */
		public static function getPepperAfter(): string {
			return self::getProperty('pepper_after');
		}

		/**
		 * Strengthens weak user passwords against brute force.
		 *
		 * @param $password
		 * @return string
		 */
		public static function pepperPassword(string $password): string {

			return (self::getPepperBefore()
					. $password
					. self::getPepperAfter()
					. mb_strlen($password)); // Simple salt technique adding length of password
		}

		/**
		 * Hash password using PHP's password_hash(PASSWORD_DEFAULT).
		 *
		 * @param string $password
		 * @param bool $pepper (optional) default = false
		 * @return string
		 */
		public static function hashPassword(string $password, bool $pepper = false): string {

			if ($pepper)
				$password = self::pepperPassword($password);

			return password_hash($password, PASSWORD_DEFAULT);
		}

		/**
		 * Checks if a given password matches a hashed password.
		 *
		 * Use $peppered = true if you peppered the password in hashPassword()
		 *
		 * @param string $password
		 * @param string $hash
		 * @param bool $peppered (optional) default = false
		 * @return bool
		 */
		public static function verifyPassword(string $password, string $hash, bool $peppered = false): bool {

			if ($peppered)
				$password = self::pepperPassword($password);

			return password_verify($password, $hash);
		}

		/**
		 * Generate a random password.
		 *
		 * @param int $length (optional) default = 17 characters
		 * @return string
		 */
		public static function generatePassword(int $length = 17): string {

			// Generate random strings

				$lowers		= str_shuffle('abcdefghijklmnopqrstuvwxyz');
				$uppers		= str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
				$numbers	= str_shuffle('0123456789');
				$symbols	= str_shuffle('-_$@()!?#<>:/*;,.&=+%°'); // These are the most accessible on regular keyboards

			// Generate random proportions

				$propLowers		= rand(1, 100);
				$propUppers		= rand(1, 100);
				$propNumbers	= rand(1, 100);
				$propSymbols	= rand(1, 100);

					$propTotal = $propLowers + $propUppers + $propNumbers + $propSymbols;

				$propLowers		= ceil($length * ($propLowers / $propTotal));
				$propUppers		= ceil($length * ($propUppers / $propTotal));
				$propNumbers	= ceil($length * ($propNumbers / $propTotal));
				$propSymbols	= ceil($length * ($propSymbols / $propTotal));

			// Mixing them together

				$password = mb_substr($lowers, 0, $propLowers)
						  . mb_substr($uppers, 0, $propUppers)
						  . mb_substr($numbers, 0, $propNumbers)
						  . mb_substr($symbols, 0, $propSymbols);

			$password = str_shuffle($password);

			while (mb_strlen($password) < $length) { // If $length is greater than the available characters, $password will not be long enough
				$password .= self::generatePassword($length - mb_strlen($password));
			}

			if (mb_strlen($password) > $length) { // Because of ceil(), password may be longer than length
				$password = mb_substr($password, 0, $length);
			}

			return $password;
		}
	}

<?php

	namespace Goji\Security;

	use Goji\Core\ConfigurationLoader;
	use Goji\Toolkit\SwissKnife;
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

		const CONFIG_FILE = ROOT_PATH . '/config/passwords.json5';

		const E_PROPERTY_NOT_SET = 1;

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

			try {

				self::$m_configuration = ConfigurationLoader::loadFileToArray($configFile);

			} catch (Exception $e) {

				self::$m_configuration = [];
			}

			self::$m_isInitialized = true;
		}

		/**
		 * Get a property from config file.
		 *
		 * Throws an error if property not set for security reasons.
		 *
		 * (If we returned like an empty string, it could cause problems
		 * in certain cases).
		 *
		 * @param string $key
		 * @return string
		 * @throws \Exception
		 */
		public static function getProperty(string $key): string {

			self::initialize();

			if (isset(self::$m_configuration[$key]))
				return self::$m_configuration[$key];

			// else
			throw new Exception("Property not set in passwords config file: $key", self::E_PROPERTY_NOT_SET);
		}

		/**
		 * Returns pepper set in config file.
		 *
		 * @return string
		 * @throws \Exception
		 */
		public static function getPepperBefore(): string {
			return self::getProperty('pepper_before');
		}

		/**
		 * Returns pepper set in config file.
		 *
		 * @return string
		 * @throws \Exception
		 */
		public static function getPepperAfter(): string {
			return self::getProperty('pepper_after');
		}

		/**
		 * Strengthens weak user passwords against brute force.
		 *
		 * @param $password
		 * @return string
		 * @throws \Exception
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
		 * @throws \Exception
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
		 * @throws \Exception
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

			$lowers		= SwissKnife::mb_str_shuffle('abcdefghijklmnopqrstuvwxyz');
			$uppers		= SwissKnife::mb_str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
			$numbers	= SwissKnife::mb_str_shuffle('0123456789');
			$symbols	= SwissKnife::mb_str_shuffle('-_$@()!?#<>:/*;,.&=+%°'); // These are the most accessible on regular keyboards

		// Generate random proportions

			$propLowers  = 0;
			$propUppers  = 0;
			$propNumbers = 0;
			$propSymbols = 0;

				try {

					$propLowers  = mt_rand(1, 100);
					$propUppers  = mt_rand(1, 100);
					$propNumbers = mt_rand(1, 100);
					$propSymbols = mt_rand(1, 100);

				} catch (Exception $e) {

					$propLowers  = mt_rand(1, 100);
					$propUppers  = mt_rand(1, 100);
					$propNumbers = mt_rand(1, 100);
					$propSymbols = mt_rand(1, 100);
				}

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

			$password = SwissKnife::mb_str_shuffle($password);

			while (mb_strlen($password) < $length) { // If $length is greater than the available characters, $password will not be long enough
				$password .= self::generatePassword($length - mb_strlen($password));
			}

			if (mb_strlen($password) > $length) { // Because of ceil(), password may be longer than length
				$password = mb_substr($password, 0, $length);
			}

			return $password;
		}

		/**
		 * Generates random unique token that looks like '5ddbf249c0bb5iA69b'
		 *
		 * @return string
		 */
		public static function generateUniqueToken(): string {
			return uniqid() . Passwords::generatePassword(5);
		}
	}

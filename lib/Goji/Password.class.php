<?php

	namespace Goji;

	/**
	 * Class Password
	 *
	 * @package Goji
	 */
	class Password {

		// To strengthen weak user passwords (against brute force)
		public static function pepperPassword($password) {

			return (PASSWORD_PEPPER_BEFORE
					. $password
					. PASSWORD_PEPPER_AFTER
					. strlen($password)); // Simple salt technique adding length of password
		}

		public static function hashPassword($password) {

			$password = self::pepperPassword($password);

			return password_hash($password, PASSWORD_DEFAULT);
		}

		public static function verifyPassword($password, $hash) {

			$password = self::pepperPassword($password);

			return password_verify($password, $hash);
		}

		public static function generatePassword($length = 17) {

			// Generate random strings

				$lower		= str_shuffle('abcdefghijklmnopqrstuvwxyz');
				$upper		= str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
				$number		= str_shuffle('0123456789');
				$symbols	= str_shuffle('-_$@()!?#<>:/*;.&=+%');

			// Generate random proportions

				$propLower		= rand(1, 100);
				$propUpper		= rand(1, 100);
				$propNumber		= rand(1, 100);
				$propSymbols	= rand(1, 100);

					$propTotal = $propLower + $propUpper + $propNumber + $propSymbols;

				$propLower		= ceil($length * ($propLower / $propTotal));
				$propUpper		= ceil($length * ($propUpper / $propTotal));
				$propNumber		= ceil($length * ($propNumber / $propTotal));
				$propSymbols	= ceil($length * ($propSymbols / $propTotal));

			// Mixing them together

				$password = substr($lower, 0, $propLower)
						  . substr($upper, 0, $propUpper)
						  . substr($number, 0, $propNumber)
						  . substr($symbols, 0, $propSymbols);

			$password = str_shuffle($password);

			while (strlen($password) < $length) { // If $length is greater than the available characters, $password will not be long enough
				$password .= self::generatePassword($length - strlen($password));
			}

			if (strlen($password) > $length) { // Because of ceil(), password may be longer than length
				$password = substr($password, 0, $length);
			}

			return $password;
		}
	}

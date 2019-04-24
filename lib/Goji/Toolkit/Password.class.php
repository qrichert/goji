<?php

	namespace Goji\Toolkit;

	/**
	 * Class Password
	 *
	 * @package Goji\Toolkit
	 */
	class Password {

		// To strengthen weak user passwords (against brute force)
		public static function pepperPassword($password) {

			return (PASSWORD_PEPPER_BEFORE
					. $password
					. PASSWORD_PEPPER_AFTER
					. mb_strlen($password)); // Simple salt technique adding length of password
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

				$password = mb_substr($lower, 0, $propLower)
						  . mb_substr($upper, 0, $propUpper)
						  . mb_substr($number, 0, $propNumber)
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

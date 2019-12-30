<?php

	namespace Goji\Security;

	use Goji\Toolkit\SimpleRequest;

	/**
	 * Class reCaptcha
	 *
	 * @package Goji\Security
	 */
	class reCaptcha {

		public static function isValid($code, $ip = null) {

			if (empty($code))
				return false;

			$data = [
				'secret' => Passwords::getProperty('google_captcha_private_key'),
				'response' => $code
			];

			if (!empty($ip))
				$data['remoteip'] = $ip;

			$response = SimpleRequest::post('https://www.google.com/recaptcha/api/siteverify', $data);

			if (empty($response))
				return false;

			$response = json_decode($response);

			return $response->success;
		}
	}

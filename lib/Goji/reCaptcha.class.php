<?php

	namespace Goji;

	class reCaptcha {

		public static function isValid($code, $ip = null) {

			if (empty($code))
				return false;

			$data = array(
				'secret'   => PASSWORDS_GOOGLE_CAPTCHA_PRIVATE_KEY,
				'response' => $code
			);

			if ($ip) {
				$data['remoteip'] = $ip;
			}

			$url = "https://www.google.com/recaptcha/api/siteverify";

			$options = array(
				'http'=> array(
							'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data)
						)
			);

			$context = stream_context_create($options);

			$response = file_get_contents($url, false, $context);

			if ($response === false)
				return false;

			$json = json_decode($response);

			return $json->success;
		}
	}

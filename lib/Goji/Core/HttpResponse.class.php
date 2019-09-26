<?php

	namespace Goji\Core;

	use Exception;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Blueprints\HttpStatusInterface;

	/**
	 * Class HttpResponse
	 *
	 * @package Goji\Core
	 */
	class HttpResponse implements HttpStatusInterface, HttpMethodInterface {

		/* <CONSTANTS> */

		const E_HTTP_STATUS_UNKNOWN = 1;

		/**
		 * Is the status code valid ?
		 *
		 * @param int|null $statusCode
		 * @return bool
		 */
		public static function isValidStatusCode(?int $statusCode): bool {
			return !empty(self::HTTP_REASON_PHRASE[$statusCode]);
		}

		/**
		 * Sets the HTTP response header according to the given status code (w/ HTTP version & Reason phrase).
		 *
		 * @param int $statusCode
		 * @param bool $exit
		 * @throws \Exception
		 */
		public static function setStatusHeader(int $statusCode, bool $exit = false) {

			if (!self::isValidStatusCode($statusCode))
				throw new Exception("Unknown HTTP status code: '$statusCode'", self::E_HTTP_STATUS_UNKNOWN);

			$reasonPhrase = self::HTTP_REASON_PHRASE[$statusCode];

			// HTTP/(1.0|1.1) + Status Code + Reason Phrase -> ex: HTTP/1.1 404 Not Found
			header("{$_SERVER['SERVER_PROTOCOL']} $statusCode $reasonPhrase", true, $statusCode);

			if ($exit)
				exit;
		}

		/**
		 * Adds JSON header, status if given, json_encode()s array and exits by default.
		 *
		 * @param array|null $data (associative array)
		 * @param bool|null $success
		 * @param bool $exit
		 */
		public static function JSON(?array $data = null, ?bool $success = null, bool $exit = true): void {

			if ($data === null)
				$data = [];

			if ($success === true)
				$data['status'] = 'SUCCESS';
			else if ($success === false)
				$data['status'] = 'ERROR';

			header('Content-Type: application/json', true);

			echo json_encode($data);

			if ($exit)
				exit;
		}
	}

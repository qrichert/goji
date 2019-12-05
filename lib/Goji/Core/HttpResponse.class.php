<?php

	namespace Goji\Core;

	use Exception;
	use Goji\Blueprints\HttpContentTypeInterface;
	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Blueprints\HttpStatusInterface;
	use Goji\Blueprints\RobotsInterface;

	/**
	 * Class HttpResponse
	 *
	 * @package Goji\Core
	 */
	class HttpResponse implements HttpStatusInterface, HttpMethodInterface, RobotsInterface, HttpContentTypeInterface {

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
		 * @param int $behaviour
		 */
		public static function setRobotsHeader(int $behaviour): void {

			switch ($behaviour) {

				case self::ROBOTS_NOINDEX:          header('X-Robots-Tag: "noindex"', true);            break;
				case self::ROBOTS_NOFOLLOW:         header('X-Robots-Tag: "nofollow"', true);           break;
				case self::ROBOTS_NOINDEX_NOFOLLOW: header('X-Robots-Tag: "noindex, nofollow"', true);  break;
				default:                            header_remove('X-Robots-Tag');                      break;
			}
		}

		/**
		 * @param string $contentType
		 * @param string|null $charset
		 */
		public static function setContentType(string $contentType, ?string $charset = 'utf-8'): void {

			$contentType = "Content-Type: $contentType";

			if (!empty($charset))
				$contentType .= "; charset=$charset";

			header($contentType, true);
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

			self::setContentType(self::CONTENT_JSON);

			echo json_encode($data);

			if ($exit)
				exit;
		}
	}

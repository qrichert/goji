<?php

	namespace Goji\Blueprints;

	/**
	 * Class HttpErrorControllerAbstract
	 *
	 * @package Goji\Blueprints
	 */
	abstract class HttpErrorControllerAbstract extends ControllerAbstract {

		/* <ATTRIBUTES> */

		protected $m_httpErrorCode;

		/* <CONSTANTS> */

		const HTTP_ERROR_DEFAULT = self::HTTP_SERVER_INTERNAL_SERVER_ERROR; // If it's an error we don't handle, make it internal

		const SUPPORTED_HTTP_ERRORS = [
			self::HTTP_ERROR_FORBIDDEN => true,
			self::HTTP_ERROR_NOT_FOUND => true,
			self::HTTP_SERVER_INTERNAL_SERVER_ERROR => true
		];

		/**
		 * @param int|null $errorCode
		 * @return bool
		 */
		public static function isValidError(?int $errorCode): bool {

			if (!isset($errorCode))
				return false;

			return isset(self::SUPPORTED_HTTP_ERRORS[$errorCode]);
		}

		/**
		 * @param int|null $errorCode
		 */
		public function setHttpError(?int $errorCode): void {

			if (!self::isValidError($errorCode))
				$errorCode = self::HTTP_ERROR_DEFAULT;

			$this->m_httpErrorCode = (int) $errorCode;
		}
	}

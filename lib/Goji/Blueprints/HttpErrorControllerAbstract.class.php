<?php

	namespace Goji\Blueprints;

	abstract class HttpErrorControllerAbstract implements ControllerInterface {

		/* <ATTRIBUTES> */

		protected $m_httpErrorCode;

		/* <CONSTANTS> */

		const HTTP_ERROR_DEFAULT = self::HTTP_SERVER_INTERNAL_SERVER_ERROR; // If it's an error we don't handle, make it internal

		/**
		 * @param int|null $errorCode
		 * @return bool
		 */
		public function isValidError(?int $errorCode): bool {

			if (!isset($errorCode))
				return false;

			$supportedErrors = array(
				self::HTTP_ERROR_FORBIDDEN,
				self::HTTP_ERROR_NOT_FOUND,
				self::HTTP_SERVER_INTERNAL_SERVER_ERROR
			);

			return in_array($errorCode, $supportedErrors);
		}

		/**
		 * @param int|null $errorCode
		 */
		public function setHttpError(?int $errorCode): void {

			if (!$this->isValidError($errorCode))
				$errorCode = self::HTTP_ERROR_DEFAULT;

			$this->m_httpErrorCode = $errorCode;
		}
	}

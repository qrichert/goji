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

			// Hash tables are O(1)
			$supportedErrors = [
				strval(self::HTTP_ERROR_FORBIDDEN) => true,
				strval(self::HTTP_ERROR_NOT_FOUND) => true,
				strval(self::HTTP_SERVER_INTERNAL_SERVER_ERROR) => true
			];

			return isset($supportedErrors[strval($errorCode)]);
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

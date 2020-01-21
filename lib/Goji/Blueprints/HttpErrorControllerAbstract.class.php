<?php

namespace Goji\Blueprints;

use Goji\Core\HttpResponse;

/**
 * Class HttpErrorControllerAbstract
 *
 * @package Goji\Blueprints
 */
abstract class HttpErrorControllerAbstract extends ControllerAbstract {

	/* <ATTRIBUTES> */

	protected $m_httpErrorCode;

	/* <CONSTANTS> */

	// If it's an error we don't handle, make it internal
	const HTTP_ERROR_DEFAULT = self::HTTP_SERVER_INTERNAL_SERVER_ERROR;

	/**
	 * @param int|null $errorCode
	 * @return bool
	 */
	public static function isValidError(?int $errorCode): bool {

		if (!isset($errorCode))
			return false;

		if (!HttpResponse::isValidStatusCode($errorCode))
			return false;

		// You could add more conditions here, like be >= 400

		return true;
	}

	/**
	 * @param int|null $errorCode
	 */
	public function setHttpError(?int $errorCode): void {

		if (!self::isValidError($errorCode))
			$errorCode = self::HTTP_ERROR_DEFAULT;

		$this->m_httpErrorCode = $errorCode;
	}
}

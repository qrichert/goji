<?php

namespace Goji\Core;

use Goji\Blueprints\HttpMethodInterface;
use Goji\Blueprints\HttpStatusInterface;
use Goji\Toolkit\UrlManager;

/**
 * Class RequestHandler
 *
 * Isolates elements from current request:
 *  - Request URI
 *  - Request Page URI
 *  - Raw Query String
 *  - Query String
 *  - Script Name
 *  - Root Folder
 *  - Request Page
 *
 * @package Goji\Core
 */
class RequestHandler implements HttpStatusInterface, HttpMethodInterface {

	private $m_requestURI;
	private $m_requestPageURI;
	private $m_rawQueryString;
	private $m_queryString;
	private $m_scriptName;
	private $m_rootFolder;
	private $m_requestPage;
	private $m_requestParameters;
	private $m_redirectStatus;
	private $m_requestMethod;
	private $m_errorDetected;
	private $m_forcedLocaleDetected;
	private $m_isAjaxRequest;

	/**
	 * RequestHandler constructor.
	 */
	public function __construct() {

		// /home?q=query
		// /goji/public/home?q=query
		$this->m_requestURI = $_SERVER['REQUEST_URI'];
			// If the URL was '/hello%20world/home', it would mess up the count by two chars
			// because root folder would be 'hello world/', so if we wanted to extract he page,
			// we would get 'c/home' because in root there is a space ' ' and in request URI
			// there is '%20' which is 2 chars longer.
			$this->m_requestURI = rawurldecode($this->m_requestURI);

		// /home
		// /goji/public/home
		$this->m_requestPageURI = $this->m_requestURI;

			// We only want the page, not the query string
			// /home?q=query -> /home
			$pos = mb_strpos($this->m_requestPageURI, '?');

			if ($pos !== false)
				$this->m_requestPageURI = mb_substr($this->m_requestPageURI, 0, $pos);

		// q=query
		$this->m_rawQueryString = $_SERVER['QUERY_STRING'] ?? '';

		// ['q' => 'query']
		$this->m_queryString = UrlManager::queryStringToArray($this->m_rawQueryString);

		// /index.php
		// /goji/public/index.php
		$this->m_scriptName = $_SERVER['SCRIPT_NAME'];

		// /
		// /goji/public/
		// The root folder is the path without 'index.php'
		// It includes public/ when server isn't configured to use public as docroot
		$this->m_rootFolder = WEBROOT . '/';

		// home
		$this->m_requestPage = '';

			// if ('/goji/public/' == '/goji/public/') -> Empty Page (nothing specified, probably home)
			if ($this->m_requestPageURI != $this->m_rootFolder) {

				//   /goji/public/home
				// - /goji/public/
				//   -----------------
				// =              home
				$len = mb_strlen($this->m_requestPageURI) - mb_strlen($this->m_rootFolder);

				// Make sure $len is negative
				if ($len > 0)
					$len *= -1;

				$this->m_requestPage = mb_substr($this->m_requestPageURI, $len);
			}

		// page-([0-9]) -> { '0' => [ 'page-1', '1' ] }
		// Filled in by \Goji\Core\Router by default
		$this->m_requestParameters = [];

		// 200, 403, 404, 500, etc.
		$this->m_redirectStatus = $_SERVER['REDIRECT_STATUS'] ?? self::HTTP_SUCCESS_OK;
			$this->m_redirectStatus = (int) $this->m_redirectStatus;

		// GET, POST, PUT, DELETE, etc...
		$this->m_requestMethod = $_SERVER['REQUEST_METHOD'] ?? self::HTTP_GET;
			$this->m_requestMethod = strtoupper((string) $this->m_requestMethod);

		$this->m_errorDetected = $this->m_redirectStatus >= 400;

		$this->m_forcedLocaleDetected = UrlManager::getFirstParamOccurrence('forceLocale', $this->m_queryString);

		$this->m_isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	public function __debugInfo() {

		echo 'Request URI: ' . $this->m_requestURI, PHP_EOL;
		echo 'Request Page URI: ' . $this->m_requestPageURI, PHP_EOL;
		echo 'Raw Query String: ' . $this->m_rawQueryString, PHP_EOL;
		echo 'Query String: ' . print_r($this->m_queryString, true), PHP_EOL;
		echo 'Script Name: ' . $this->m_scriptName, PHP_EOL;
		echo 'Root Folder: ' . $this->m_rootFolder, PHP_EOL;
		echo 'Request Page: ' . $this->m_requestPage, PHP_EOL;
		echo 'Request Parameters: ' . print_r($this->m_requestParameters, true), PHP_EOL;
		echo 'Redirect Status: ' . $this->m_redirectStatus, PHP_EOL;
		echo 'Request Method: ' . $this->m_requestMethod, PHP_EOL;
	}

	/**
	 * Request URI.
	 *
	 * Page + Query String:
	 * /goji/public/home?q=query
	 *
	 * @return string
	 */
	public function getRequestURI(): string {
		return $this->m_requestURI;
	}

	/**
	 * Request Page URI.
	 *
	 * Page only:
	 * /goji/public/home
	 *
	 * @return string
	 */
	public function getRequestPageURI(): string {
		return $this->m_requestPageURI;
	}

	/**
	 * Raw Query String.
	 *
	 * Query String only:
	 * q=query
	 *
	 * @return string
	 */
	public function getRawQueryString(): string {
		return $this->m_rawQueryString;
	}

	/**
	 * Query String.
	 *
	 * Query String as array:
	 * [
	 *      'foo' => ['value1', value3'],
	 *      'bar' => 'value2'
	 * ]
	 *
	 * @return array
	 */
	public function getQueryString(): array {
		return $this->m_queryString;
	}

	/**
	 * Script Name.
	 *
	 * Path to index.php:
	 * /goji/public/index.php
	 *
	 * @return string
	 */
	public function getScriptName(): string {
		return $this->m_scriptName;
	}

	/**
	 * Root Folder.
	 *
	 * /public folder path:
	 * /goji/public/
	 *
	 * @return string
	 */
	public function getRootFolder(): string {
		return $this->m_rootFolder;
	}

	/**
	 * Request Page.
	 *
	 * Page requested:
	 * home
	 *
	 * This is equal to (Request Page URI - Root Folder):
	 *   /goji/public/home
	 * - /goji/public/
	 *   -----------------
	 * =              home
	 *
	 * @return string
	 */
	public function getRequestPage(): string {
		return $this->m_requestPage;
	}

	/**
	 * Returns request parameters as array.
	 *
	 * /page-([0-9]+)(?:-([0-9]+))?
	 * -> /page-937-28
	 *
	 * Note that (?: ) is a non-capturing group!
	 *
	 * => Array(
	 *      [0] => [/page-937-28] // Full match (= $0)
	 *      [1] => [937] // First capturing group (= $1)
	 *      [2] => [29] // Second capturing group (= $2)
	 * )
	 *
	 * @return array
	 */
	public function getRequestParameters(): array {
		return $this->m_requestParameters;
	}

	/**
	 * Get a particular capturing group.
	 *
	 * 0 = $0 (full match), 1 = $1 (first capturing group), etc.
	 *
	 * See getRequestParameters() for explanation.
	 *
	 * @param int $capturingGroup
	 * @return string|null
	 */
	public function getRequestParameter(int $capturingGroup): ?string {
		return $this->m_requestParameters[$capturingGroup] ?? null;
	}

	/**
	 * @param array $parameters
	 */
	public function setRequestParameters(array $parameters) {
		$this->m_requestParameters = $parameters;
	}

	/**
	 * Returns HttpStatusInterface::STATUS_CODE
	 *
	 * @return int
	 */
	public function getRedirectStatus(): int {
		return $this->m_redirectStatus;
	}

	/**
	 * Returns HttpMethodInterface::METHOD_NAME
	 *
	 * @return string
	 */
	public function getRequestMethod(): string {
		return $this->m_requestMethod;
	}

	/**
	 * Checks whether there is a 'X-Requested-With' header
	 *
	 * This doesn't work if you don't send this header with your AJAX requests.
	 *
	 * This is sent automatically by Goji's SimpleRequest module (which Form module is based on).
	 *
	 * @return bool
	 */
	public function isAjaxRequest(): bool {
		return $this->m_isAjaxRequest;
	}

	/**
	 * True if HttpStatusInterface::STATUS_CODE >= 400
	 *
	 * @return bool
	 */
	public function getErrorDetected(): bool {
		return $this->m_errorDetected;
	}

	/**
	 * Returns forced locale or null if none
	 *
	 * @return string|null
	 */
	public function getForcedLocaleDetected(): ?string {
		return $this->m_forcedLocaleDetected;
	}
}

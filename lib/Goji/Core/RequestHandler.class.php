<?php

	namespace Goji\Core;

	use Goji\Blueprints\HttpMethodInterface;
	use Goji\Blueprints\HttpStatusInterface;

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

		/**
		 * RequestHandler constructor.
		 */
		public function __construct() {

			// /home?q=query
			// /goji/public/home?q=query
			$this->m_requestURI = $_SERVER['REQUEST_URI'];

			// /home
			// /goji/public/home
			$this->m_requestPageURI = $this->m_requestURI;

				// We only want the page, not the query string
				// /home?q=query -> /home
				$pos = mb_strpos($this->m_requestPageURI, '?');

				if ($pos !== false) {
					$this->m_requestPageURI = mb_substr($this->m_requestPageURI, 0, $pos);
				}

			// q=query
			$this->m_rawQueryString = $_SERVER['QUERY_STRING'] ?? '';

			// array('q' => 'query')
			$this->m_queryString = self::queryStringToArray($this->m_rawQueryString);

			// /index.php
			// /goji/public/index.php
			$this->m_scriptName = $_SERVER['SCRIPT_NAME'];

			// /
			// /goji/public/
			$this->m_rootFolder = $this->m_scriptName;

				// The root folder is the path without 'index.php'
				// It includes public/ when server isn't configured to use public as docroot

				// /goji/public/index.php -> /goji/public/
				$pagesPossible = array('index.php', 'static.php');

				foreach ($pagesPossible as $page) {

					$pos = mb_strpos($this->m_rootFolder, $page);

					if ($pos !== false) {
						$this->m_rootFolder = mb_substr($this->m_rootFolder, 0, $pos);
						break;
					}
				}

				// Make sure there's a trailing slash
				if (empty($this->m_rootFolder))
					$this->m_rootFolder = '/';
				else if (mb_substr($this->m_rootFolder, -1) !== '/')
					$this->m_rootFolder .= '/';

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
			$this->m_requestParameters = array();

			// 200, 403, 404, 500, etc.
			$this->m_redirectStatus = $_SERVER['REDIRECT_STATUS'] ?? self::HTTP_SUCCESS_OK;
				$this->m_redirectStatus = intval($this->m_redirectStatus);

			// GET, POST, PUT, DELETE, etc...
			$this->m_requestMethod = $_SERVER['REQUEST_METHOD'] ?? self::HTTP_GET;
				$this->m_requestMethod = strtoupper((string) $this->m_requestMethod);

			$this->m_errorDetected = $this->m_redirectStatus >= 400;

			$this->m_forcedLocaleDetected = self::getFirstParamOccurrence('forceLocale', $this->m_queryString);
		}

		public function __debugInfo() {

			echo 'Request URI: ' . $this->m_requestURI . PHP_EOL;
			echo 'Request Page URI: ' . $this->m_requestPageURI . PHP_EOL;
			echo 'Raw Query String: ' . $this->m_rawQueryString . PHP_EOL;
			echo 'Query String: ' . print_r($this->m_queryString, true) . PHP_EOL;
			echo 'Script Name: ' . $this->m_scriptName . PHP_EOL;
			echo 'Root Folder: ' . $this->m_rootFolder . PHP_EOL;
			echo 'Request Page: ' . $this->m_requestPage . PHP_EOL;
			echo 'Request Parameters: ' . print_r($this->m_requestParameters, true) . PHP_EOL;
			echo 'Redirect Status: ' . $this->m_redirectStatus . PHP_EOL;
			echo 'Request Method: ' . $this->m_requestMethod . PHP_EOL;
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
		 * array(
		 *      'foo' => ['value1', value3'],
		 *      'bar' => 'value2'
		 * )
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
		 * Checks whether there is a 'ajax-http-request' key in the POST data
		 *
		 * This doesn't work if you don't send this key with your AJAX requests.
		 *
		 * @return bool
		 */
		public function isAjaxRequest(): bool {
			return !empty($_POST['ajax-http-request']);
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

		/**
		 * Parses Query String and returns an array.
		 *
		 * This is similar to PHP's parse_str(), but instead of overwriting duplicate
		 * parameters, it saves them under the same key, in order of appearance.
		 *
		 * foo=value1&bar=value2&foo=value3
		 *
		 * parse_str():
		 * array(
		 *      'foo' => 'value3',
		 *      'bar' => 'value2'
		 * )
		 *
		 * queryStringToArray():
		 * array(
		 *      'foo' => ['value1', value3'],
		 *      'bar' => 'value2'
		 * )
		 *
		 * @param string $queryString
		 * @return array
		 */
		public static function queryStringToArray(string $queryString): array {

			if (empty($queryString))
				return array();

			// foo=value1&bar=value2&foo=value3

			// array('foo=value1', 'bar=value2', 'foo=value3');
			$tmpQueryString = explode('&', $queryString);

			foreach ($tmpQueryString as &$paramValue) {

				// First portion = 'foo'=value1
				// Second portion = foo='value1'
				// We use the 3rd parameter (limit) to have maximum 2 chunks (in case there is an '=' in the value)
				list($param, $value) = explode('=', $paramValue, 2);

				// We passed $paramValue by reference, so we can modify it
				$paramValue = array(
					'param' => $param,
					'value' => $value
				);
			}
			// Break the reference
			unset($paramValue);

			$queryString = array();

			// array(['param' => 'foo', 'value' => 'value1'], etc.);
			foreach ($tmpQueryString as $paramValue) {

				// If parameter already in list (duplicate)
				if (isset($queryString[$paramValue['param']])) {

					// If the index is already an array (so 3rd+ occurrence of param)
					if (is_array($queryString[$paramValue['param']])) {

						// Append to array
						$queryString[$paramValue['param']][] = $paramValue['value'];

					} else {

						// Replace value by array containing it
						$queryString[$paramValue['param']] = array($queryString[$paramValue['param']]);
						$queryString[$paramValue['param']][] = $paramValue['value'];
					}

				} else {

					$queryString[$paramValue['param']] = $paramValue['value'];
				}
			}

			return $queryString;
		}

		/**
		 * Returns the value of the first occurrence of a query string parameter.
		 *
		 * In PHP, $_GET['param'] always returns the value of the last occurrence of 'param'.
		 *
		 * For example :
		 *
		 * ```php
		 * ?param=foo&param=bar
		 * $_GET['param'] == 'bar'
		 * ```
		 *
		 * Sometimes it causes security issues because the user could override the system value.
		 *
		 * This function returns the value of the first time the parameter appears,
		 * thus ignoring any user addition.
		 *
		 * $queryString should be an array produced by RequestHandler::queryStringToArray() or a
		 * raw query string (could be $_SERVER['QUERY_STRING']).
		 *
		 * @param string $param The parameter you want the value of
		 * @param array|string $queryString The query string in which to look for the value
		 * @return string|null The value of the first occurrence of $param, null if not found or empty
		 */
		public static function getFirstParamOccurrence(string $param, $queryString): ?string {

			if (!is_array($queryString))
				$queryString = self::queryStringToArray($queryString);

			$value = null;

			if (!empty($queryString[$param]))
				$value = $queryString[$param];

			if (is_array($value))
				$value = $value[0];

			return $value;
		}

		/**
		 * Builds a query string from an array, inverse of RequestHandler::queryStringToArray()
		 *
		 * @param array $queryStringArray
		 * @return string
		 */
		public static function buildQueryStringFromArray(array $queryStringArray): string {

			$queryString = array();

			foreach ($queryStringArray as $key => $value) {

				$param = array();

				if (!is_array($value))
					$value = array($value);

				foreach ($value as $item) // $param = [ key=value1, key=value2, ... ]
					$param[] = $key . '=' . $item;

				$param = implode('&', $param);
				$queryString[] = $param;
			}

			$queryString = implode('&', $queryString);

			return $queryString;
		}

		/**
		 * RequestHandler::queryStringToArray() leaves values raw (url encoded), this function decodes the values
		 *
		 *
		 * @param array $queryStringArray
		 * @return array
		 */
		public static function decodeQueryStringArray(array $queryStringArray): array {

			foreach ($queryStringArray as $key => &$value) {

				if (is_array($value)) {

					foreach ($value as &$item) {
						$item = urldecode($item);
					}
					unset($item);

				} else {
					$value = urldecode($value);
				}
			}
			unset($value);

			return $queryStringArray;
		}
	}

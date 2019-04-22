<?php

	namespace Goji\Core;

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
	class RequestHandler {

		private $m_requestURI;
		private $m_requestPageURI;
		private $m_rawQueryString;
		private $m_queryString;
		private $m_scriptName;
		private $m_rootFolder;
		private $m_requestPage;

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
				$pos = strpos($this->m_requestPageURI, '?');

				if ($pos !== false) {
					$this->m_requestPageURI = substr($this->m_requestPageURI, 0, $pos);
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
				$pos = strpos($this->m_rootFolder, 'index.php');

				if ($pos !== false) {

					$this->m_rootFolder = substr($this->m_rootFolder, 0, $pos);

				} else { // Try with static.php

					// /goji/public/static.php -> /goji/public/
					$pos = strpos($this->m_rootFolder, 'static.php');

					if ($pos !== false) {
						$this->m_rootFolder = substr($this->m_rootFolder, 0, $pos);
					}
				}

			// home
			$this->m_requestPage = '';

				//   /goji/public/home
				// - /goji/public/
				//   -----------------
				// =              home
				$len = strlen($this->m_requestPageURI) - strlen($this->m_rootFolder);

				// Make sure $len is negative
				if ($len > 0)
					$len *= -1;

				$this->m_requestPage = substr($this->m_requestPageURI, $len);
		}

		public function __debugInfo() {

			echo 'Request URI: ' . $this->m_requestURI . PHP_EOL;
			echo 'Request Page URI: ' . $this->m_requestPageURI . PHP_EOL;
			echo 'Raw Query String: ' . $this->m_rawQueryString . PHP_EOL;
			echo 'Query String: ' . print_r($this->m_queryString, true) . PHP_EOL;
			echo 'Script Name: ' . $this->m_scriptName . PHP_EOL;
			echo 'Root Folder: ' . $this->m_rootFolder . PHP_EOL;
			echo 'Request Page: ' . $this->m_requestPage . PHP_EOL;
		}

		/**
		 * Request URI.
		 *
		 * Page + Query String:
		 * /goji/public/home?q=query
		 *
		 * @return string
		 */
		public function getRequestURI() {
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
		public function getRequestPageURI() {
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
		public function getRawQueryString() {
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
		 * @return string
		 */
		public function getQueryString() {
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
		public function getScriptName() {
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
		public function getRootFolder() {
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
		public function getRequestPage() {
			return $this->m_requestPage;
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
		public static function queryStringToArray($queryString) {

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

					// If the index is already an array (so 3rd+ occurence of param)
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
		 * @param string $param The parameter you want the value of
		 * @param string $queryString The query string in which to look for the value (could be $_SERVER['QUERY_STRING'])
		 * @return string|null The value of the first occurrence of $param, null if not found
		 */
		public static function getFirstParamOccurrence($param, $queryString) {

			// TODO: Is this still relevant? Maybe keep it anyways, but don't use queryStringToArray(), this one is more efficient

			// Ex :
			// $param = 'param'
			// $queryString = 'param=foo&param=bar'

			$param = $param . '='; // $param = 'param='
			$paramLength = strlen($param); // $paramLength = 6 (param + =)

			$query = explode('&', $queryString); // [0] => param=foo, [1] => param=bar

			foreach ($query as $p) {

				// if (substr('param=foo', 0, 6) == 'param='))
				if (substr($p, 0, $paramLength) == $param) { // substr('param=foo', 0, 6) == 'param='

					// substr('param=foo', 6) -> Remove first 6 chars
					return urldecode(substr($p, $paramLength)); // |param=|foo -> foo
				}
			}

			return null; // If not found
		}
	}

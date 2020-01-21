<?php

namespace Goji\Toolkit;

/**
 * Class UrlManager
 *
 * @package Goji\Toolkit
 */
class UrlManager {

	/**
	 * Parses Query String and returns an array.
	 *
	 * This is similar to PHP's parse_str(), but instead of overwriting duplicate
	 * parameters, it saves them under the same key, in order of appearance.
	 *
	 * foo=value1&bar=value2&foo=value3
	 *
	 * parse_str():
	 * [
	 *      'foo' => 'value3',
	 *      'bar' => 'value2'
	 * ]
	 *
	 * queryStringToArray():
	 * [
	 *      'foo' => ['value1', value3'],
	 *      'bar' => 'value2'
	 * ]
	 *
	 * @param string $queryString
	 * @return array
	 */
	public static function queryStringToArray(string $queryString): array {

		if (empty($queryString))
			return [];

		// foo=value1&bar=value2&foo=value3

		// ['foo=value1', 'bar=value2', 'foo=value3']
		$tmpQueryString = explode('&', $queryString);

		foreach ($tmpQueryString as &$paramValue) {

			// First portion = 'foo'=value1
			// Second portion = foo='value1'
			// We use the 3rd parameter (limit) to have maximum 2 chunks (in case there is an '=' in the value)
			[$param, $value] = explode('=', $paramValue, 2);

			// We passed $paramValue by reference, so we can modify it
			$paramValue = [
				'param' => $param,
				'value' => $value
			];
		}
		// Break the reference
		unset($paramValue);

		$queryString = [];

		// [['param' => 'foo', 'value' => 'value1'], etc.];
		foreach ($tmpQueryString as $paramValue) {

			// If parameter already in list (duplicate)
			if (isset($queryString[$paramValue['param']])) {

				// If the index is already an array (so 3rd+ occurrence of param)
				if (is_array($queryString[$paramValue['param']])) {

					// Append to array
					$queryString[$paramValue['param']][] = $paramValue['value'];

				} else {

					// Replace value by array containing it
					$queryString[$paramValue['param']] = [$queryString[$paramValue['param']]];
					$queryString[$paramValue['param']][] = $paramValue['value'];
				}

			} else {

				$queryString[$paramValue['param']] = $paramValue['value'];
			}
		}

		return $queryString;
	}

	/**
	 * Builds a query string from an array, inverse of UrlManager::queryStringToArray()
	 *
	 * @param array $queryStringArray
	 * @return string
	 */
	public static function buildQueryStringFromArray(array $queryStringArray): string {

		$queryString = [];

		foreach ($queryStringArray as $key => $value) {

			$param = [];
			$value = (array) $value;

			foreach ($value as $item) // $param = [ key=value1, key=value2, ... ]
				$param[] = $key . '=' . $item;

			$param = implode('&', $param);
			$queryString[] = $param;
		}

		$queryString = implode('&', $queryString);

		return $queryString;
	}

	/**
	 * UrlManager::queryStringToArray() leaves values raw (url encoded), this function decodes the values
	 *
	 * From PHP doc:
	 * rawurldecode() does not decode plus symbols ('+') into spaces. urldecode() does.
	 *
	 *
	 * @param array $queryStringArray
	 * @return array
	 */
	public static function decodeQueryStringArray(array $queryStringArray): array {

		foreach ($queryStringArray as $key => &$value) {

			if (is_array($value)) {

				foreach ($value as &$item) {
					$item = rawurldecode($item);
				}
				unset($item);

			} else {
				$value = rawurldecode($value);
			}
		}
		unset($value);

		return $queryStringArray;
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
	 * $queryString should be an array produced by UrlManager::queryStringToArray() or a
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
}

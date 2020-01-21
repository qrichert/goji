<?php

namespace Goji\Toolkit;

/**
 * Class SimpleRequest
 *
 * @package Goji\Toolkit
 */
class SimpleRequest {

	/**
	 * @param string $uri
	 * @param array $options
	 * @return string|null
	 */
	private static function request(string $uri, array $options): ?string {

		$context = stream_context_create(['http' => $options]);

		$response = file_get_contents($uri, false, $context);

		if ($response === false)
			return null;

		return $response;
	}

	/**
	 * HTTP GET request
	 *
	 * @param string $uri
	 * @return string|null
	 */
	public static function get(string $uri): ?string {

		$options = [
			'method' => 'GET'
		];

		return self::request($uri, $options);
	}

	/**
	 * HTTP POST request
	 *
	 * @param string $uri
	 * @param array $data
	 * @return string|null
	 */
	public static function post(string $uri, array $data): ?string {

		$options = [
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n",
			'content' => http_build_query($data)
		];

		return self::request($uri, $options);
	}

	/**
	 * HTTP PUT request
	 *
	 * @param string $uri
	 * @param array $data
	 * @return string|null
	 */
	public static function put(string $uri, array $data): ?string {

		$options = [
			'method' => 'PUT',
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'content' => http_build_query($data)
		];

		return self::request($uri, $options);
	}

	/**
	 * HTTP DELETE request
	 *
	 * @param string $uri
	 * @return string|null
	 */
	public static function delete(string $uri): ?string {

		$options = [
			'method' => 'GET'
		];

		return self::request($uri, $options);
	}
}

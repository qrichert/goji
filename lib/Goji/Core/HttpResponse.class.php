<?php

namespace Goji\Core;

use Exception;
use Goji\Blueprints\HttpContentTypeInterface;
use Goji\Blueprints\HttpMethodInterface;
use Goji\Blueprints\HttpStatusInterface;
use Goji\Blueprints\RobotsInterface;
use Goji\Blueprints\TimeInterface;

/**
 * Class HttpResponse
 *
 * For more information about HTTP caching, see:
 * https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching
 *
 * @package Goji\Core
 */
class HttpResponse implements HttpStatusInterface, HttpMethodInterface, RobotsInterface, HttpContentTypeInterface, TimeInterface {

	/* <CONSTANTS> */

	const HTTP_CACHE_CONTROL_NO_CACHE = 'no-cache';
	const HTTP_CACHE_CONTROL_NO_STORE = 'no-store';
	const HTTP_CACHE_CONTROL_PUBLIC = 'public';
	const HTTP_CACHE_CONTROL_PRIVATE = 'private';

	const E_HTTP_STATUS_UNKNOWN = 1;
	const E_WRONG_HTTP_CACHE_RESTRICTION_SETTING = 1;
	const E_WRONG_HTTP_CACHE_PRIVACY_SETTING = 2;
	const E_HTTP_CACHE_MAX_AGE_INVALID = 3;

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
	 * @param string $headerName
	 * @param string $headerValue
	 * @param bool $replace
	 */
	public static function setHeader(string $headerName, string $headerValue, bool $replace = true): void {
		header("$headerName: $headerValue", $replace);
	}

/* <HTTP CACHING HEADERS> */

	/**
	 * HTTP Entity Tag (ETag) token
	 *
	 * ETag is sort of a state token. It's an unique ID of a file and its content (like a hash).
	 * It is used by to browser to check whether it should update the file in cache or not.
	 *
	 * For example a file hello.txt containing 'hello'. We could hash it and obtain a token
	 * like a5rX98j. So as long as the token remains the same, the browser know it doesn't
	 * need to update the file since it hasn't changed.
	 *
	 * But let's change the content of the file to 'hello, world!'. The new hash is klg83Ds.
	 * The browser notices it has changed and thus request the updated version.
	 *
	 * ETag is checked once (and only once) the cache has expired. Once the file in cache has expired
	 * (max-age) the browsers requests the ETag. If it's the same, the file is cached again for max-age
	 * (part of the HTTP response) without re-download. It it doesn't match, the new version is downloaded.
	 *
	 * No data is transferred (apart from the header) if ETag is valid.
	 *
	 * @param string $token
	 * @param bool $replace
	 */
	private static function setHttpETag(string $token, bool $replace = true): void {
		header("ETag: \"$token\"", $replace);
	}

	/**
	 * HTTP Cache Control Restriction (no-cache | no-store)
	 *
	 * no-cache: With the no-cache header, the browser CAN cache the file but MUST re-validate it for
	 * each request. It's like max-age was set to 0. If the file is still the same (see ETag) it uses
	 * the cached version, else it re-downloads it.
	 *
	 * no-store: With no-store, the cache is completely disabled for the given file. This is used for
	 * privacy or if the file always changes. It's best not to store banking information and so on
	 * inside the browser cache.
	 *
	 * @param string $directive (HTTP_CACHE_CONTROL_NO_STORE | HTTP_CACHE_CONTROL_NO_CACHE)
	 * @param bool $replace
	 */
	private static function setHttpCacheControlRestriction(string $directive, bool $replace = false): void {
		header("Cache-Control: $directive", $replace);
	}

	/**
	 * HTTP Cache Control Privacy (public | private)
	 *
	 * public: public indicates the file can be cached in any case, regardless of other settings
	 * like HTTP authentication or non-cachable status codes. You don't need it in most cases
	 * where you'd use max-age, because max-age implies public.
	 *
	 * private: private means the end user (browser) is allowed to cache the file, but not any intermediate
	 * cache system (like a CDN). This is meant for privacy also.
	 *
	 * @param string $directive (HTTP_CACHE_CONTROL_PUBLIC | HTTP_CACHE_CONTROL_PRIVATE)
	 * @param bool $replace
	 */
	private static function setHttpCacheControlPrivacy(string $directive, bool $replace = false): void {
		header("Cache-Control: $directive", $replace);
	}

	/**
	 * HTTP Cache Control Max Age (in seconds)
	 *
	 * How long (in seconds) the browser should consider the cached file valid until it must re-validate.
	 * Be careful, no re-validation will occur until the 'countdown' is over (or the user empties his cache).
	 *
	 * If you set a CSS file for 1 year for example, and the user doesn't empty his cache, he will keep
	 * the same design CSS file for 1 year, regardless of how many updates you make.
	 *
	 * A way around it is changing the name of the file.
	 *
	 * /!\ If you don't use any cache directive, it's up to the browser to decide what to do /!\
	 *
	 * @param int $maxAge In seconds
	 * @param bool $replace
	 */
	private static function setHttpCacheControlMaxAge(int $maxAge, bool $replace = false): void {
		header("Cache-Control: max-age=$maxAge", $replace);
	}

	/**
	 * Set HTTP Caching Policy.
	 *
	 * Use an array like:
	 *
	 * $policy = [
	 *      'etag' => 'token',
	 *      'restriction' => HttpResponse::HTTP_CACHE_CONTROL_NO_CACHE,
	 *      'privacy' => HttpResponse::HTTP_CACHE_CONTROL_PUBLIC,
	 *      'max-age' => HttpResponse::TIME_1DAY
	 * ]
	 *
	 * @param array $policy
	 * @throws \Exception
	 */
	public static function setHttpCachingPolicy(array $policy): void {

		$eTag = $policy['etag'] ?? null;
		$restriction = $policy['restriction'] ?? null;
		$privacy = $policy['privacy'] ?? null;
		$maxAge = $policy['max-age'] ?? null;

		if (isset($eTag)) {
			self::setHttpETag($eTag);
		}

		// Clear previously set caching headers
		header_remove('ETag');
		header_remove('Cache-Control');

		if (isset($restriction)) {

			if ($restriction === self::HTTP_CACHE_CONTROL_NO_CACHE
			    || $restriction === self::HTTP_CACHE_CONTROL_NO_STORE)
				self::setHttpCacheControlRestriction($restriction);
			else
				throw new Exception("HTTP Cache Control Restriction not valid: $restriction. Must be either no-cache or no-store.", self::E_WRONG_HTTP_CACHE_RESTRICTION_SETTING);
		}

		if (isset($privacy)) {

			if ($privacy === self::HTTP_CACHE_CONTROL_PUBLIC
			    || $privacy === self::HTTP_CACHE_CONTROL_PRIVATE)
				self::setHttpCacheControlPrivacy($privacy);
			else
				throw new Exception("HTTP Cache Control Privacy not valid: $privacy. Must be either public or private.", self::E_WRONG_HTTP_CACHE_PRIVACY_SETTING);
		}

		if (isset($maxAge)) {

			if (is_numeric($maxAge))
				// To positive int
				self::setHttpCacheControlMaxAge(abs((int) $maxAge));
			else
				throw new Exception('Http Cache Max Age invalid. Must be a numeric value in seconds.', self::E_HTTP_CACHE_MAX_AGE_INVALID);
		}
	}

	/*private static function httpCacheMustRevalidate(){
		isset(If-None-Match) ?
		token = If-None-Match

		if (token === currentToken)
		 http header: 304 not modified
		 return false

		else
		 http set Etag: new token
		 http max-age = XXX
		 return true
	}*/

	/*private static function httpIfModifiedSince() {
		Similar to ETag but with timestamp
		If-Modified-Since: Fri, 13 Jul 2018 10:49:23 GMT
			http set last-modified: Fri, 10 Jul 2018 10:49:23 GMT /
	}*/

/* <CONTENT> */

	/**
	 * Adds JSON header, status if given, json_encode()s array and exits by default.
	 *
	 * If success === true -> HTTP 200 OK (default)
	 * If success === false -> HTTP 400 Bad Request
	 * If success is int -> HTTP (int)
	 *
	 * @param array|null $data (associative array)
	 * @param int|bool $success
	 * @param bool $exit
	 * @throws \Exception
	 */
	public static function JSON(?array $data = null, $success = true, bool $exit = true): void {

		if ($data === null)
			$data = [];

		// Set status according to given value
		if (is_int($success)) {
			self::setStatusHeader($success);

		// Set status according to default rules
		} else {

			if ($success === false)
				self::setStatusHeader(self::HTTP_ERROR_BAD_REQUEST);
			else // true or not int/bool
				self::setStatusHeader(self::HTTP_SUCCESS_OK);
		}

		self::setContentType(self::CONTENT_JSON);

		echo json_encode($data);

		if ($exit)
			exit;
	}
}

<?php

namespace Test\Goji\Unit;

use Exception;
use Goji\Core\HttpResponse;

final class HttpResponseTest {

	public static function test_JSON() {

		$data = [
			'hello' => 'world!'
		];

		$testResponse = function($success) use($data) {
			ob_start();
			HttpResponse::JSON($data, $success, false);
			$response = ob_get_clean();
			$responseCode = http_response_code();

			return [$response, $responseCode];
		};

		// success = true
		[$response, $responseCode] = $testResponse(true);

		if ($response !== '{"hello":"world!"}')
			throw new Exception("'$response' !== '{\"hello\":\"world!\"}'");

		if ($responseCode !== 200)
			throw new Exception("'$responseCode' !== 200");

		// success = false
		[$_, $responseCode] = $testResponse(false);

		if ($responseCode !== 400)
			throw new Exception("'$responseCode' !== 400");

		// success = no value provided (null)
		[$_, $responseCode] = $testResponse(null);

		if ($responseCode !== 200)
			throw new Exception("'$responseCode' !== 200");

		// success = valid HTTP response code (418 I'm a Teapot)
		[$_, $responseCode] = $testResponse(HttpResponse::HTTP_ERROR_I_M_A_TEAPOT);

		if ($responseCode !== 418)
			throw new Exception("'$responseCode' !== 418");

		// success = invalid HTTP response code (1337)
		$exceptionHappened = false;
		try {
			[$_, $responseCode] = $testResponse(1337);
		} catch (Exception $e) {
			$exceptionHappened = true;
		}

		if (!$exceptionHappened)
			throw new Exception("Should have thrown an exception");
	}
}

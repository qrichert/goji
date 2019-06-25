<?php

	namespace Goji\Blueprints;

	/**
	 * Interface HttpMethodInterface
	 *
	 * @package Goji\Blueprints
	 */
	interface HttpMethodInterface {

		/*
		 * Descriptions from https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
		 */

		const HTTP_CONNECT = 'CONNECT'; // The CONNECT method establishes a tunnel to the server identified by the target resource.
		const HTTP_DELETE = 'DELETE'; // The DELETE method deletes the specified resource.
		const HTTP_GET = 'GET'; // The GET method requests a representation of the specified resource. Requests using GET should only retrieve data.
		const HTTP_HEAD = 'HEAD'; // The HEAD method asks for a response identical to that of a GET request, but without the response body.
		const HTTP_OPTIONS = 'OPTIONS'; // The OPTIONS method is used to describe the communication options for the target resource.
		const HTTP_PATCH = 'PATCH'; // The PATCH method is used to apply partial modifications to a resource.
		const HTTP_POST = 'POST'; // The POST method is used to submit an entity to the specified resource, often causing a change in state or side effects on the server.
		const HTTP_PUT = 'PUT'; // The PUT method replaces all current representations of the target resource with the request payload.
		const HTTP_TRACE = 'TRACE'; // The TRACE method performs a message loop-back test along the path to the target resource.
	}

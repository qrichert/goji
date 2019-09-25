<?php

	namespace Goji\Blueprints;

	/**
	 * Interface HttpStatusInterface
	 *
	 * @package Goji\Blueprints
	 */
	interface HttpStatusInterface {

		const HTTP_INFORMATION_CONTINUE = 100;
		const HTTP_INFORMATION_SWITCHING_PROTOCOLS = 101;
		const HTTP_INFORMATION_PROCESSING = 102;
		const HTTP_INFORMATION_EARLY_HINTS = 103;

		const HTTP_SUCCESS_OK = 200;
		const HTTP_SUCCESS_CREATED = 201;
		const HTTP_SUCCESS_ACCEPTED = 202;
		const HTTP_SUCCESS_NON_AUTHORITATIVE_INFORMATION = 203;
		const HTTP_SUCCESS_NO_CONTENT = 204;
		const HTTP_SUCCESS_RESET_CONTENT = 205;
		const HTTP_SUCCESS_PARTIAL_CONTENT = 206;
		const HTTP_SUCCESS_MULTI_STATUS = 207;
		const HTTP_SUCCESS_ALREADY_REPORTED = 208;
		const HTTP_SUCCESS_CONTENT_DIFFERENT = 210;
		const HTTP_SUCCESS_IM_USED = 226;

		const HTTP_REDIRECT_MULTIPLE_CHOICES = 300;
		const HTTP_REDIRECT_MOVED_PERMANENTLY = 301;
		const HTTP_REDIRECT_FOUND = 302;
		const HTTP_REDIRECT_SEE_OTHER = 303;
		const HTTP_REDIRECT_NOT_MODIFIED = 304;
		const HTTP_REDIRECT_USE_PROXY = 305;
		const HTTP_REDIRECT_SWITCH_PROXY = 306;
		const HTTP_REDIRECT_TEMPORARY_REDIRECT = 307;
		const HTTP_REDIRECT_PERMANENT_REDIRECT = 308;
		const HTTP_REDIRECT_TOO_MANY_REDIRECTS = 310;

		const HTTP_ERROR_BAD_REQUEST = 400;
		const HTTP_ERROR_UNAUTHORIZED = 401;
		const HTTP_ERROR_PAYMENT_REQUIRED = 402;
		const HTTP_ERROR_FORBIDDEN = 403;
		const HTTP_ERROR_NOT_FOUND = 404;
		const HTTP_ERROR_METHOD_NOT_ALLOWED = 405;
		const HTTP_ERROR_NOT_ACCEPTABLE = 406;
		const HTTP_ERROR_PROXY_AUTHENTICATION_REQUIRED = 407;
		const HTTP_ERROR_REQUEST_TIMEOUT = 408;
		const HTTP_ERROR_CONFLICT = 409;
		const HTTP_ERROR_GONE = 410;
		const HTTP_ERROR_LENGTH_REQUIRED = 411;
		const HTTP_ERROR_PRECONDITION_FAILED = 412;
		const HTTP_ERROR_REQUEST_ENTITY_TOO_LARGE = 413;
		const HTTP_ERROR_REQUEST_URI_TOO_LONG = 414;
		const HTTP_ERROR_UNSUPPORTED_MEDIA_TYPE = 415;
		const HTTP_ERROR_REQUESTED_RANGE_UNSATISFIABLE = 416;
		const HTTP_ERROR_EXPECTATION_FAILED = 417;
		const HTTP_ERROR_I_M_A_TEAPOT = 418;
		const HTTP_ERROR_BAD_MAPPING = 421;
		const HTTP_ERROR_UNPROCESSABLE_ENTITY = 422;
		const HTTP_ERROR_LOCKED = 423;
		const HTTP_ERROR_METHOD_FAILURE = 424;
		const HTTP_ERROR_UNORDERED_COLLECTION = 425;
		const HTTP_ERROR_UPGRADE_REQUIRED = 426;
		const HTTP_ERROR_PRECONDITION_REQUIRED = 428;
		const HTTP_ERROR_TOO_MANY_REQUESTS = 429;
		const HTTP_ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
		const HTTP_ERROR_RETRY_WITH = 449;
		const HTTP_ERROR_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
		const HTTP_ERROR_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
		const HTTP_ERROR_UNRECOVERABLE_ERROR = 456;

		const HTTP_ERROR_NO_RESPONSE = 444;
		const HTTP_ERROR_SSL_CERTIFICATE_ERROR = 495;
		const HTTP_ERROR_SSL_CERTIFICATE_REQUIRED = 496;
		const HTTP_ERROR_HTTP_REQUEST_SENT_TO_HTTPS_PORT = 497;
		const HTTP_ERROR_TOKEN_INVALID = 498;
		const HTTP_ERROR_CLIENT_CLOSED_REQUEST = 499;

		const HTTP_SERVER_INTERNAL_SERVER_ERROR = 500;
		const HTTP_SERVER_NOT_IMPLEMENTED = 501;
		const HTTP_SERVER_BAD_GATEWAY = 502;
		const HTTP_SERVER_SERVICE_UNAVAILABLE = 503;
		const HTTP_SERVER_GATEWAY_TIMEOUT = 504;
		const HTTP_SERVER_HTTP_VERSION_NOT_SUPPORTED = 505;
		const HTTP_SERVER_VARIANT_ALSO_NEGOTIATES = 506;
		const HTTP_SERVER_INSUFFICIENT_STORAGE = 507;
		const HTTP_SERVER_LOOP_DETECTED = 508;
		const HTTP_SERVER_BANDWIDTH_LIMIT_EXCEEDED = 509;
		const HTTP_SERVER_NOT_EXTENDED = 510;
		const HTTP_SERVER_NETWORK_AUTHENTICATION_REQUIRED = 511;

		const HTTP_SERVER_UNKNOWN_ERROR = 520;
		const HTTP_SERVER_WEB_SERVER_IS_DOWN = 521;
		const HTTP_SERVER_CONNECTION_TIMED_OUT = 522;
		const HTTP_SERVER_ORIGIN_IS_UNREACHABLE = 523;
		const HTTP_SERVER_A_TIMEOUT_OCCURRED = 524;
		const HTTP_SERVER_SSL_HANDSHAKE_FAILED = 525;
		const HTTP_SERVER_INVALID_SSL_CERTIFICATE = 526;
		const HTTP_SERVER_RAILGUN_ERROR = 527;

		const HTTP_REASON_PHRASE = [

			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			103 => 'Early Hints',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			208 => 'Already Reported',
			210 => 'Content Different',
			226 => 'IM Used',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',
			310 => 'Too Many Redirects',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Unsatisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			421 => 'Bad Mapping',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Method Failure',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			449 => 'Retry With',
			450 => 'Blocked by Windows Parental Controls',
			451 => 'Unavailable For Legal Reasons',
			456 => 'Unrecoverable Error',

			444 => 'No Response',
			495 => 'SSL Certificate Error',
			496 => 'SSL Certificate Required',
			497 => 'HTTP Request Sent to HTTPS Port',
			498 => 'Token Invalid',
			499 => 'Client Closed Request',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			508 => 'Loop Detected',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended',
			511 => 'Network Authentication Required',

			520 => 'Unknown Error',
			521 => 'Web Server Is Down',
			522 => 'Connection Timed Out',
			523 => 'Origin Is Unreachable',
			524 => 'A Timeout Occurred',
			525 => 'SSL Handshake Failed',
			526 => 'Invalid SSL Certificate',
			527 => 'Railgun Error',
		];
	}

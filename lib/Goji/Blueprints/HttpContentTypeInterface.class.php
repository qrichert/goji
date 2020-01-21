<?php

namespace Goji\Blueprints;

/**
 * Interface HttpContentTypeInterface
 *
 * @package Goji\Blueprints
 */
interface HttpContentTypeInterface {

	const CONTENT_TEXT = 'text/plain';
	const CONTENT_HTML = 'text/html';
	const CONTENT_CSS = 'text/css';
	const CONTENT_JS = 'application/javascript';
	const CONTENT_JSON = 'application/json';

	const CONTENT_XML = 'application/xml';
	const CONTENT_CSV = 'text/csv';

	const CONTENT_GIF = 'image/gif';
	const CONTENT_JPG = 'image/jpg';
	const CONTENT_PNG = 'image/png';
	const CONTENT_SVG = 'image/svg+xml';
	const CONTENT_WEBP = 'image/webp';

	const CONTENT_AAC = 'audio/aac';
	const CONTENT_MP3 = 'audio/mpeg';
	const CONTENT_WEBA = 'audio/webm';

	const CONTENT_MP4 = 'video/mp4';
	const CONTENT_WEBM = 'video/webm';

	const CONTENT_PDF = 'application/pdf';
	const CONTENT_ZIP = 'application/zip';

	const CONTENT_OCTET_STREAM = 'application/octet-stream';
	const CONTENT_BINARY = self::CONTENT_OCTET_STREAM; // Alias

	const CONTENT_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
	const CONTENT_MULTIPART_FORM_DATA = 'multipart/form-data';
}

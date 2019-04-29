<?php

	// $_SERVER['REDIRECT_STATUS'] contains error code
	if (isset($_SERVER['REDIRECT_STATUS'])
	   && $_SERVER['REDIRECT_STATUS'] != 200) {

		$ERROR = $_SERVER['REDIRECT_STATUS'];

	} else { // If not set or 200, user probably requests the page directly.
			 // We deny that by making it look like the page does not exist (404)
		$ERROR = 404;
	}

	/*********************/

	if ($this->m_app->getAppMode() === \Goji\Core\App::DEBUG
		&& isset($_GET['error'])
		&& !empty($_GET['error'])) { // Giving direct access on localhost for testing

		$ERROR = intval($_GET['error']);
	}

	/*********************/

	$errors = array(403, 404, 500);

	if (!in_array($ERROR, $errors)) // If it's an error we don't handle, make it internal
		$ERROR = 500;

	switch ($ERROR) {
		// header('(HTTP/1.0|HTTP/1.1) ERROR DESCRIPTION', true (replace = default), RESPONSE CODE);
		case 403:	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);				break;
		case 404:	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);				break;
		case 500:	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);	break;
	}

	require_once '../src/view/error_v.php';

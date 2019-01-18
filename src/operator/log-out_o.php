<?php

	if (session_status() == PHP_SESSION_NONE)
		session_start();

	$_SESSION['connected'] = false;
	$_SESSION['user_id'] = null;
	$_SESSION['company_id'] = null;

	session_destroy();
	session_start();

	setcookie(COOKIES_PREFIX . 'remember-me', 'false',	time(), '/', null, false, true); // Delete now
	setcookie(COOKIES_PREFIX . 'id', '',				time(), '/', null, false, true); // Delete now
	setcookie(COOKIES_PREFIX . 'rm-hash', '',			time(), '/', null, false, true); // Delete now

	header('Location: ' . PAGES[CURRENT_LANGUAGE]['join']);
	exit;

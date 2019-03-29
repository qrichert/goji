<?php

	namespace App\Lang;

	function getLanguage($default) {

		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2); // fr-FR (français - France)

		return (!empty($lang)) ? $lang : $default;
	}

	// Make sure the user isn't trying to cheat
	$_GET['lang'] = \App::getFirstParamOccurrence('lang', $_SERVER['QUERY_STRING']);

	if (isset($_GET['lang'])
		&& !empty($_GET['lang'])
		&& $_GET['lang'] != 'no-lang') { // If $_GET['lang'] is set (force language from page)

		$_SESSION['lang'] = strtolower($_GET['lang']);

	} else { // No forced language (no_page or other languageless page)

		if (!isset($_SESSION['lang']) || empty($_SESSION['lang'])) { // If no default or last language set

			if (isset($_COOKIE[COOKIES_PREFIX . 'lang'])) { // We look if there's a cookie
				$_SESSION['lang'] = $_COOKIE[COOKIES_PREFIX . 'lang'];
			} else { // If no info about a previous preference, we use the one from the user's browser
				$_SESSION['lang'] = getLanguage(DEFAULT_LANGUAGE);
			}
		}
	}

	if (!in_array($_SESSION['lang'], ACCEPTED_LANGUAGES)) // If calculated lang is not valid
		$_SESSION['lang'] = DEFAULT_LANGUAGE;

	require_once '../translation/' . $_SESSION['lang'] . '.tr.php';

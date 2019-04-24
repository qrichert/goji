<?php

	if (isset($_GET['lang'])
		&& !empty($_GET['lang'])
		&& $_GET['lang'] != 'no-lang') {

		if (mb_strlen($_GET['lang']) > 2)
			$_GET['lang'] = mb_substr($_GET['lang'], 0, 2);

		$_SESSION['lang'] = mb_strtolower($_GET['lang']);

	} else {

		$_SESSION['lang'] = DEFAULT_LANGUAGE;
	}

	setcookie(COOKIES_PREFIX . 'lang', $_SESSION['lang'], time() + 10 * 12 * 30 * 24 * 3600, '/', null, false, true); // 10 years

	if (isset($_GET['ajax'])) {

		echo json_encode(array(
			'status' => 'SUCCESS',
			'lang' => $_SESSION['lang']
		));
		exit;

	} else {

		header('Location: ' . PAGES[$_SESSION['lang']]['home']);
		exit;
	}

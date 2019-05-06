<?php

	if (!isset($_SESSION['connected']) || $_SESSION['connected'] === false) {

		if (isset($_COOKIE[COOKIES_PREFIX . 'remember-me']) && $_COOKIE[COOKIES_PREFIX . 'remember-me'] === 'true'
			&& isset($_COOKIE[COOKIES_PREFIX . 'id']) && $_COOKIE[COOKIES_PREFIX . 'id'] != ''
		    && isset($_COOKIE[COOKIES_PREFIX . 'rm-hash']) && $_COOKIE[COOKIES_PREFIX . 'rm-hash'] != '') {

				require_once '../src/model/MemberManager.class.php';

				try {
					MemberManager::autoLogIn($_COOKIE[COOKIES_PREFIX . 'id'], $_COOKIE[COOKIES_PREFIX . 'rm-hash']);
				} catch (Exception $e) { // If not, cookies are probably broken, so we delete them
					setcookie(COOKIES_PREFIX . 'remember-me', 'false',	time(), '/', null, false, true); // Delete now
					setcookie(COOKIES_PREFIX . 'id', '',				time(), '/', null, false, true); // Delete now
					setcookie(COOKIES_PREFIX . 'rm-hash', '',			time(), '/', null, false, true); // Delete now
				}
			}
	}

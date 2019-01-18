<?php

	$_CONNECTED = false;
	$_MEMBER = null;

	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {

		try {
			$_MEMBER = new Member($_SESSION['user_id']);
		} catch (Exception $e) {

			require_once '../src/model/HR.class.php';

			HR::logOut();

			header('Location: ' . PAGES[CURRENT_LANGUAGE]['join']);
			exit;
		}

		$_CONNECTED = true;

		require_once '../src/model/Notification.class.php';
	}

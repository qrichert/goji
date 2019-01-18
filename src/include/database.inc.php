<?php

	$_LOCAL_TESTING = false;

	$db = null;

	try { // Online
		$db = new PDO('mysql:host=' . PASSWORD_DB_HOST . ';dbname=' . PASSWORD_DB_NAME . ';charset=utf8mb4', PASSWORD_DB_LOGIN, PASSWORD_DB_PASSWORD);
	}

	catch (Exception $e) {

		$_LOCAL_TESTING = true;

		try { // Local
			$db = new PDO('mysql:host=localhost;dbname=DB_NAME;charset=utf8mb4', 'root', 'root');
		}

		catch (Exception $e) {
			//die('Error: ' . $e->getMessage());
		}
	}

<?php

	use Goji\Core\App;

	session_start();

//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

/* <GENERAL> */

	require_once '../lib/AutoLoad.php';

	$app = new App();
		$app->createDataBase();
		$app->exec();

exit;
/* <INCLUDES> */

	require_once '../src/include/passwords.inc.php';

//	require_once '../src/model/Member.class.php';
//	require_once '../src/include/keep-me-logged-in.inc.php';
//	require_once '../src/include/connected.inc.php';

/* <LINKED FILES MERGING> */

	// TODO: Integrate it to SimpleTemplate
	$_LINKED_FILES_MODE = 'normal'; // Separate

	if (CURRENT_MODE != 'debug')
		$_LINKED_FILES_MODE = 'merged';

	define('LINKED_FILES_MODE', $_LINKED_FILES_MODE);

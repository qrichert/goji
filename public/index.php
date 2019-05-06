<?php

	use Goji\Core\App;

//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

/* <GENERAL> */

	require_once '../lib/AutoLoad.php';

	$app = new App();
		$app->createDataBase();
		$app->exec();

<?php

	use Goji\StaticFiles\StaticServer;

//	ini_set('display_errors', 1);
//	ini_set('display_startup_errors', 1);
//	error_reporting(E_ALL);

/* <GENERAL> */

	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	$staticServer = new StaticServer();
		$staticServer->exec();

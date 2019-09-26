<?php

	use Goji\StaticFiles\StaticServer;

/* <GENERAL> */

	require_once '../lib/Settings.php';
	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	$staticServer = new StaticServer();
		$staticServer->exec();

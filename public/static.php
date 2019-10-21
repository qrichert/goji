<?php

	require_once '../lib/Settings.php';
	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	use Goji\StaticFiles\StaticServer;

	$staticServer = new StaticServer();
		$staticServer->exec();

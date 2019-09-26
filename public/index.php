<?php

	use Goji\Core\App;

/* <GENERAL> */

	require_once '../lib/Settings.php';
	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	$app = new App();
		$app->exec();

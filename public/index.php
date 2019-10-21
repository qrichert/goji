<?php

	require_once '../lib/Settings.php';
	require_once '../lib/RootPath.php';
	require_once '../lib/AutoLoad.php';

	use Goji\Core\App;

	$app = new App();
		$app->exec();

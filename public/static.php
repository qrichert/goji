<?php

require_once '../lib/Goji/Goji.php';

use Goji\StaticFiles\StaticServer;

$staticServer = new StaticServer();
	$staticServer->exec();

<?php

	namespace AutoLoad;

	require_once 'Goji/Core/AutoLoader.class.php';

	spl_autoload_register('\Goji\Core\AutoLoader::autoLoadLibrary');
	spl_autoload_register('\Goji\Core\AutoLoader::autoLoadSource');

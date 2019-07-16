<?php

	// If methods are called from a __destruct(), relative path may vary
	// Here we make sure it stays the same
	// (used in \Goji\Toolkit\SimpleCache for example)
	if (!defined('ROOT_PATH')) {

		$rootPath = realpath('../');

		if (mb_substr($rootPath, -1) == '/')
			$rootPath = mb_substr($rootPath, 0, -1);

		/**
		 * Absolute path of project root folder without trailing /.
		 *
		 * /!\ This is different from web root /!\
		 * -> ex: /Users/Goji/Sites/project/
		 *
		 * It's needed when relative paths don't work properly (like in a destructor)
		 */
		define('ROOT_PATH', $rootPath);
	}

	if (!defined('WEBROOT')) {

		$rootPath = dirname($_SERVER['SCRIPT_NAME']);

		// Prepend / if none
		if (mb_substr($rootPath, 0, 1) != '/')
			$rootPath = '/' . $rootPath;

		// Remove trailing / if any
		if (mb_strlen($rootPath) > 1 && mb_substr($rootPath, -1) == '/')
			$rootPath = mb_substr($rootPath, 0, -1);


		/**
		 * Webroot path starting with / and without trailing /.
		 *
		 * /!\ This is the web root /!\
		 */
		define('WEBROOT', $rootPath);
	}

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

	// Remove trailing / if any (if $rootPath == '/' then $rootPath = '' = empty = none)
	if (mb_substr($rootPath, -1) == '/')
		$rootPath = mb_substr($rootPath, 0, -1);

	/**
	 * WebRoot path starting with / and without trailing /.
	 *
	 * /!\ This is the web root /!\
	 *
	 * If WebRoot is the same folder as index.php/static.php, then WebRoot = '' (empty)
	 * because we always do WEBROOT . '/sub/folder'. So if there is one, it becomes
	 * '/public/sub/folder', and if there is none, it remains '/sub/folder'.
	 */
	define('WEBROOT', $rootPath);
}

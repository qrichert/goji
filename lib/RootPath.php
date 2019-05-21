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
		 */
		define('ROOT_PATH', $rootPath);
	}

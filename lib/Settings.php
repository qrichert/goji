<?php

header_remove('X-Powered-By');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if (!defined('UMASK_FILE')) {

	/**
	 * Default mask for files.
	 *
	 * 0022 mask -> chmod 0644
	 *
	 * (file are not executable by default, Linux doesn't allow +x on creation, so -> 666)
	 *
	 * default:   0666  rw- rw- rw-
	 * umask:   - 0022  --- -w- -w-
	 *          ------
	 *            0644  rw- r-- r--
	 *
	 * -----------------------------------------------------------
	 * Use this only if you know what you are doing!
	 * If you're on a system that doesn't let you manage users, it
	 * probably isn't a good idea (like on cheap shared hosting).
	 * -----------------------------------------------------------
	 */
	define('UMASK_FILE', 0022); // chmod 644 rw- r-- r--
}

// Uncomment if you know what you're doing
//umask(UMASK_FILE);

if (!defined('CHMOD_FOLDER')) {

	/**
	 * Default chmod for folders.
	 *
	 * The +x bit is needed to use folders fully:
	 *
	 * +r -> list the files inside the folder (just names, no metadata, 'ls -l' won't work)
	 * +w -> create, rename or delete files within the directory
	 * +x -> enter the directory (cd) and accesses files and directories inside ('ls -l' will work with +x)
	 *
	 * -----------------------------------------------------------
	 * Use this only if you know what you are doing!
	 * If you're on a system that doesn't let you manage users, it
	 * probably isn't a good idea (like on cheap shared hosting).
	 * -----------------------------------------------------------
	 */
	define('CHMOD_FOLDER', 0755); // chmod 755 rwx r-x r-x
}

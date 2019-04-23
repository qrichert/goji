<?php

	namespace AutoLoad;

	/**
	 * Auto-load function for libraries.
	 *
	 * Libraries must be in their own project folder inside /lib, and their
	 * namespace must reflect that architecture.
	 *
	 * For example, SimpleCache class from Goji Toolkit library is in
	 * Goji\Toolkit\SimpleCache namespace. So, auto-loading it will look for
	 * SimpleCache.class.php inside a Goji/Toolkit folder, inside /lib.
	 *
	 * \Goji\Toolkit\SimpleCache -> '/lib/' + Goji + '/' + 'Toolkit' + '/' + SimpleCache + '.class.php'
	 *
	 * @param string $className The class which needs to be loaded (no initial backslash '\')
	 */
	function autoLoadLibrary($className) {

		// There shouldn't be a leading backslash, but you never know
		$className = ltrim($className, '\\');

		// Goji\SimpleTemplate -> ../lib/Goji/SimpleTemplate.class.php
		$classFile = '../lib/' . str_replace('\\', '/', $className) . '.class.php';

		if (is_file($classFile))
			require_once $classFile;
	}

	/**
	 * Auto-load function for controllers.
	 *
	 * Controllers must be in /src/controller/ folder.
	 *
	 * \App\Controller\HomeController -> '/src/controler/' + HomeController + '.class.php'
	 *
	 * @param string $className The class which needs to be loaded
	 */
	function autoLoadController($className) {

		// There shouldn't be a leading backslash, but you never know
		$className = ltrim($className, '\\');

		// App\Controller\HomeController -> HomeController
		// We want only the class name, not the rest of the namespace
		// $len = strlen('App\Controller\\');
		// $len = 15;
		$className = substr($className, 15);

		// HomeController -> ../src/controller/HomeController.class.php
		$classFile = '../src/controller/' . $className . '.class.php';

		if (is_file($classFile))
			require_once $classFile;
	}

	spl_autoload_register('AutoLoad\autoLoadLibrary');
	spl_autoload_register('AutoLoad\autoLoadController');

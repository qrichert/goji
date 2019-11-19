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
	 * \Goji\Toolkit\SimpleCache -> '/lib/' + 'Goji/Toolkit/SimpleCache' + '.class.php'
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
	 * Auto-load function for source files.
	 *
	 * Source files must be in /src folder.
	 *
	 * \App\Model\HomeModel -> '/src/' + 'Model/HomeModel' + '.class.php'
	 *
	 * @param string $className The class which needs to be loaded (no initial backslash '\')
	 */
	function autoLoadSource($className) {

		// There shouldn't be a leading backslash, but you never know
		$className = ltrim($className, '\\');

		// App\Controller\HomeController -> Controller\HomeController
		// We want only to remove the 'App\' part
		// $len = mb_strlen('App\\');
		// $len = 4;
		$className = mb_substr($className, 4);

		// Model\Admin\BlogPost -> Model/Admin/BlogPost
		$className = str_replace('\\', '/', $className);

		// Model/HomeController -> ../src/Model/HomeModel.class.php
		$classFile = '../src/' . $className . '.class.php';

		if (is_file($classFile))
			require_once $classFile;
	}

	spl_autoload_register('AutoLoad\autoLoadLibrary');
	spl_autoload_register('AutoLoad\autoLoadSource');

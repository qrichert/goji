<?php

namespace Goji\Core;

class AutoLoader {

	/* <CONSTANTS> */

	const CONTROLLER = 'Controller';
	const VIEW = 'View';

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
	public static function autoLoadLibrary($className) {

		// There shouldn't be a leading backslash, but you never know
		$className = ltrim($className, '\\');

		// Goji\SimpleTemplate -> ../lib/Goji/SimpleTemplate.class.php
		$classFile = ROOT_PATH . '/lib/' . str_replace('\\', '/', $className) . '.class.php';

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
	public static function autoLoadSource($className) {

		// There shouldn't be a leading backslash, but you never know
		$className = ltrim($className, '\\');

		// App/Model/HomeController -> ../src/App/Model/HomeModel.class.php
		$classFile = ROOT_PATH . '/src/' . str_replace('\\', '/', $className) . '.class.php';

		if (is_file($classFile))
			require_once $classFile;
	}

	/**
	 * Make an application component name a valid one.
	 *
	 * For example, the App/HomeController shortcut will be changed to App/Controller/HomeController.
	 * or, App/HomeView -> App/View/HomeView
	 *
	 * @param string $fullClassName
	 * @param string $component
	 * @param bool $namespacify Transform App/View/HomeView to \App\View\HomeView
	 * @return string
	 */
	private static function sanitizeApplicationComponent(string $fullClassName, string $component, bool $namespacify = true): string {

		// Step 1: \App/HomeController\ -> App/HomeController

		$fullClassName = str_replace('\\', '/', $fullClassName);
		$fullClassName = trim($fullClassName, '/');

		// Step 2: HomeController                -> Controller/HomeController
		//         App\HomeController            -> App/Controller/HomeController
		//         Controller\HomeController     -> Controller/HomeController
		//         App\Controller\HomeController -> App/Controller/HomeController

		$nbLevels = substr_count($fullClassName, '/');

		if (!preg_match('#(^|/)' . $component . '#i', $fullClassName)) {

			// HomeController -> Controller/HomeController
			if ($nbLevels === 0) {
				$fullClassName = $component . '/' . $fullClassName;
				// App/HomeController -> App/Controller/HomeController
			} else {
				$fullClassName = preg_replace('#^(.+?/)#i', '$1' . $component . '/', $fullClassName);
			}
		}

		if ($namespacify)
			return '\\' . str_replace('/', '\\', $fullClassName);
		else
			return $fullClassName;
	}

	/**
	 * Make the controller name a valid one.
	 *
	 * For example, the App/HomeController shortcut will be changed to \App\Controller\HomeController.
	 *
	 * @param string $controller
	 * @param bool $namespacify
	 * @return string
	 */
	public static function sanitizeController(string $controller, bool $namespacify = true): string {
		return self::sanitizeApplicationComponent($controller, self::CONTROLLER, $namespacify);
	}

	/**
	 * Make the view name a valid one.
	 *
	 * For example, the App/HomeView shortcut will be changed to \App\View\HomeView.
	 *
	 * @param string $view
	 * @param bool $namespacify
	 * @return string
	 */
	public static function sanitizeView(string $view, bool $namespacify = true): string {
		return self::sanitizeApplicationComponent($view, self::VIEW, $namespacify);
	}
}

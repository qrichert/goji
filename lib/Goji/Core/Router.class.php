<?php

	namespace Goji\Core;

	use \Exception;

	/**
	 * Class Router
	 *
	 * @package Goji\Core
	 */
	class Router {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_languages;
		private $m_requestHandler;
		private $m_routes;
		private $m_mappedRoutes;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/routes.json5';

		const E_ROUTES_ARE_MISCONFIGURED = 0;
		const E_ROUTE_LACKING_CONTROLLER = 1;

		const HTTP_ERROR_404 = 404;

		/**
		 * Router constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $configFile (optional) default = Router::CONFIG_FILE
		 * @throws \Exception
		 */
		public function __construct(App $app, $configFile = self::CONFIG_FILE) {

			$this->m_app = $app;
			$this->m_languages = $app->getLanguages();
			$this->m_requestHandler = $app->getRequestHandler();
			// TODO: Cache routes & mapped routes
			$this->m_routes = ConfigurationLoader::loadFileToArray($configFile);
			$this->m_mappedRoutes = $this->mapRoutes($this->m_routes);
		}

		/**
		 * Maps config file routes to usable array.
		 *
		 * The structure of the config file is easy on the human, but hard
		 * to run through for a computer.
		 *
		 * This functions changes the structure of the infos so that it's easier
		 * to use in code.
		 *
		 * Basically, it puts routes as keys, and lists their properties underneath.
		 * So it's easy to look for the request page in the array, and get the details.
		 *
		 * Initial:
		 * [page] => (
		 *      [routes] => Array(
		 *          [en] => /english-page,
		 *          [fr] => /page-francais
		 *      ),
		 *      [controller] => PageController
		 * )
		 *
		 * Mapped:
		 * [/english-page] => Array(
		 *      [lang] => en
		 *      [controller] => PageController
		 * ),
		 * [/page-francais] => Array(
		 *      [lang] => fr
		 *      [controller] => PageController
		 * )
		 *
		 * @param array $routes
		 * @return array
		 * @throws \Exception
		 */
		private function mapRoutes(array $routes): array {

			$mappedRoutes = array();

			foreach ($routes as $page => $config) {

				// Controller is mandatory
				if (!isset($config['controller']) || !is_string($config['controller']))
					throw new Exception('Route is lacking controller. (' . $page .')', self::E_ROUTE_LACKING_CONTROLLER);


				$controller = $config['controller'];

				if (isset($config['routes']) && is_array($config['routes'])) {

					foreach ($config['routes'] as $lang => $route) {

						$mappedRoutes[$route] = array(
							'lang' => $lang,
							'controller' => $controller
						);
					}

				} else if (isset($config['route']) && is_string($config['route'])) {

					$mappedRoutes[$config['route']] = array(
						'lang' => 'all', // No specific language
						'controller' => $controller
					);

				} else {

					throw new Exception('Routes are misconfigured. Configuration syntax is invalid.', self::E_ROUTES_ARE_MISCONFIGURED);
				}
			}

			return $mappedRoutes;
		}

		/**
		 * Loads the appropriate controller.
		 */
		public function route(): void {

			/*
			 * TODO: Lang
			 * Get current lang (last used) or default (browser).
			 * Also allow setting a default lang in config to overwrite browser one
			 */
			$defaultLang = 'en';

			$page = '/' . $this->m_requestHandler->getRequestPage();
			$controller = null;

			// If request page exists in config (note that controller is set, otherwise mapRoutes() would have failed).
			if (isset($this->m_mappedRoutes[$page])) {

				// $controller = \App\Controller\HomeController
				$controller = '\App\Controller\\' . $this->m_mappedRoutes[$page]['controller'];
			}

			if ($controller !== null) {

				// $controller = new \App\Controller\HomeController()
				$controller = new $controller($this->m_app);
				$controller->render();

			} else {
				$this->redirect(self::HTTP_ERROR_404);
			}
		}

		/**
		 * @param \Goji\Core\Router::HTTP_ERROR_CODE|string $where
		 */
		private function redirect($where): void {
			// TODO: Maybe put this in separate Redirection class that could be a member & passed along to controllers
			$where = null;
			echo PHP_EOL . '<br>' . 'REDIRECT';
			exit; // Always after redirection
		}
	}

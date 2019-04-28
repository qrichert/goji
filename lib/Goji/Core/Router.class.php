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

					foreach ($config['routes'] as $locale => $route) {

						$mappedRoutes[$route] = array(
							'locale' => $locale,
							'controller' => $controller
						);
					}

				} else if (isset($config['route']) && is_string($config['route'])) {

					$mappedRoutes[$config['route']] = array(
						'locale' => 'all', // No specific language
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

			$page = '/' . $this->m_requestHandler->getRequestPage();
			$locale = null;
			$controller = null;

			// Loop through the routes in configuration, and match them again the requested route
			// We use a regex so we can have variable parameters
			// (Note that controller is given, otherwise mapRoutes() would have failed).
			foreach ($this->m_mappedRoutes as $pagePattern => $route) { // contains controller & lang

				// If the pattern is config matches a request
				// We extract controller, locale and parameters
				if (preg_match('#^' . $pagePattern . '$#', $page, $matches)) {

					if (!isset($route['locale']) || empty($route['locale']) || $route['locale'] == 'all')
						$this->m_languages->getCurrentLocale();
					else
						$this->m_languages->setCurrentLocale($route['locale']);

					// $controller = \App\Controller\HomeController
					$controller = '\App\Controller\\' . $route['controller'];

					$this->m_requestHandler->setRequestParameters($matches);

					// Stop searching after first match.
					break;
				}
			}

			// If we found a match, a controller will be set
			if ($controller !== null) {

				// $controller = new \App\Controller\HomeController()
				$controller = new $controller($this->m_app);
				$controller->render();

			// If not, it's a 404
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

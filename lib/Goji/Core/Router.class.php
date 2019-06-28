<?php

	namespace Goji\Core;

	use Goji\Blueprints\HttpStatusInterface;
	use Goji\HumanResources\Authentication;
	use Goji\Parsing\RegexPatterns;
	use App\Controller\HttpErrorController;
	use Exception;

	/**
	 * Class Router
	 *
	 * @package Goji\Core
	 */
	class Router implements HttpStatusInterface {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_routes;
		private $m_mappedRoutes;
		private $m_currentPage;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/routes.json5';

		const E_ROUTES_ARE_MISCONFIGURED = 0;
		const E_ROUTE_LACKING_CONTROLLER = 1;
		const E_PAGE_DOES_NOT_EXIST = 2;
		const E_LOCALE_DOES_NOT_EXIST = 3;
		const E_ROUTING_MUST_BE_DONE_FIRST = 4;

		/**
		 * Router constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $configFile (optional) default = Router::CONFIG_FILE
		 * @throws \Exception
		 */
		public function __construct(App $app, $configFile = self::CONFIG_FILE) {

			$this->m_app = $app;

			$this->m_routes = ConfigurationLoader::loadFileToArray($configFile);
				$this->m_routes = $this->formatRoutes($this->m_routes);

			$this->m_mappedRoutes = $this->mapRoutes($this->m_routes);

			$this->m_currentPage = null;
		}

		/**
		 * We format the routes from config so that they are all standardized.
		 *
		 * They are easier to manipulate later if we know in which form they come in.
		 *
		 * Mainly we convert all routes to arrays for each language
		 *
		 * [en] => '/home' becomes [en] => ['/home']
		 * [fr] => ['/accueil', '/bienvenue'] stays the same
		 *
		 * Also prepend / if not there (it tells the system, THIS is the beginning of the path)
		 * home -> /home
		 *
		 * @param $routes
		 * @return array
		 * @throws \Exception
		 */
		private function formatRoutes($routes): array {

			foreach ($routes as $page => &$config) {

				if (isset($config['routes']) && is_array($config['routes'])) {

					foreach ($config['routes'] as $locale => &$route) {

						// There can be multiple paths for the same page
						// You could have /home and /home-([0-9+]) with a parameter
						// Here we standardize them all
						// It's maybe less efficient but otherwise we would have too much
						// $mappedRoutes (with if/else and 'routes'/'route) = 4x, here only 2x
						if (!is_array($route))
							$route = array($route);

						foreach ($route as &$alternativePath) {

							if (mb_substr($alternativePath, 0, 1) !== '/')
								$alternativePath = '/' . $alternativePath;
						}
						unset($alternativePath);
					}
					unset($route);

				} else if (isset($config['route'])
				           && (is_string($config['route']) || is_array($config['route']))) {

					if (!is_array($config['route']))
						$config['route'] = array($config['route']);

					foreach ($config['route'] as &$alternativePath) {

						if (mb_substr($alternativePath, 0, 1) !== '/')
							$alternativePath = '/' . $alternativePath;
					}
					unset($alternativePath);

				} else {

					throw new Exception('Routes are misconfigured. Configuration syntax is invalid.', self::E_ROUTES_ARE_MISCONFIGURED);
				}
			}

			return $routes;
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
		 *          [en] => [/english-page],
		 *          [fr] => [/page-francais]
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

						// We know it's an array because it has been formatted by Router::formatRoutes()
						foreach ($route as $alternativePath) {

							$mappedRoutes[$alternativePath] = array(
								'locale' => $locale,
								'controller' => $controller,
								'page' => $page
							);
						}
					}

				} else if (isset($config['route']) && is_array($config['route'])) {

					foreach ($config['route'] as $alternativePath) {

						$mappedRoutes[$alternativePath] = array(
							'locale' => 'all', // No specific language
							'controller' => $controller,
							'page' => $page
						);
					}

				} else {

					throw new Exception('Routes are misconfigured. Configuration syntax is invalid.', self::E_ROUTES_ARE_MISCONFIGURED);
				}
			}

			return $mappedRoutes;
		}

		/**
		 * Returns current page ID (key in routes.json5)
		 * @return string
		 * @throws \Exception
		 */
		public function getCurrentPage(): string {

			if (isset($this->m_currentPage))
				return $this->m_currentPage;
			else
				throw new Exception('Router::getCurrentPage() cannot be called before routing.', self::E_ROUTING_MUST_BE_DONE_FIRST);
		}

		/**
		 * Change the current page ID.
		 *
		 * This should be used sparingly. Only if hasCurrentPage() returned false.
		 *
		 * It is notably used by HttpErrorController to make sure it contains
		 * the same error as the one displayed (since HttpErrorController has the
		 * power to change the error code according to what can be displayed).
		 *
		 * @param string $page
		 */
		public function setCurrentPage(string $page): void {
			$this->m_currentPage = $page;
		}

		public function hasCurrentPage(): bool {
			return isset($this->m_currentPage);
		}

		/**
		 * Returns the link associated to a given page.
		 *
		 * Returns the absolute path, without the domain.
		 * So https://www.domain.com/my/page would return /my/page
		 *
		 * If you want the full path, with the domain, set the $includeSiteURL to true
		 *
		 * Router::getLinkForPage(null, 'en_US'); -> Current page, en_US version
		 * Router::getLinkForPage() -> Current page, current locale
		 * Router::getLinkForPage('home') -> 'home' page, current locale
		 * Router::getLinkForPage('home', 'fr') -> 'home' page, fr version
		 * Router::getLinkForPage(null, null, true) -> Current page, current locale, full URL https://www.domain.com/page
		 *
		 * @param string|null $page (optional) Page ID (see routes.json5) (default = current page)
		 * @param string|null $locale (optional) The locale you want the link for (default = current locale)
		 * @param bool $includeSiteURL (optional) default = false
		 * @param int|null $index (optional) if you have multiple paths/link for one page, which one you want (default = 0 = first one)
		 * @param array|null $parameters
		 * @return string
		 * @throws \Exception
		 */
		public function getLinkForPage(string $page = null,
		                               string $locale = null,
		                               bool $includeSiteURL = false,
		                               ?int $index = 0,
		                               array $parameters = null): string {

			if (!isset($page)) {

				if (isset($this->m_currentPage))
					$page = $this->m_currentPage;
				else
					throw new Exception('Router::getLinkForPage() cannot be called without $page parameter before routing.', self::E_ROUTING_MUST_BE_DONE_FIRST);
			}

			// Make sure page exists
			if (!isset($this->m_routes[$page]))
				throw new Exception('Page does not exist: ' . $page, self::E_PAGE_DOES_NOT_EXIST);

			if (!isset($locale))
				$locale = $this->m_app->getLanguages()->getCurrentLocale(); // Current one if none given

			// Make sure locale exists
			if (!isset($this->m_app->getLanguages()->getConfigurationLocales()[$locale]))
				throw new Exception('Locale does not exist: ' . $locale, self::E_LOCALE_DOES_NOT_EXIST);

			// Make sure index is set
			if ($index === null)
				$index = 0;

			$link = null;

			$locales = array($locale, $this->m_app->getLanguages()->getFallbackLocale());
			foreach ($locales as $locale) {

				// If we have [page][routes][locale]
				if (isset($this->m_routes[$page]['routes'][$locale][$index])) {

					$link = $this->m_routes[$page]['routes'][$locale][$index];

				// If we have [page][routes][all]
				} else if (isset($this->m_routes[$page]['routes']['all'][$index])) {

					$link = $this->m_routes[$page]['routes']['all'][$index];

				// If we have [page][route]
				} else if (isset($this->m_routes[$page]['route'][$index])) {

					$link = $this->m_routes[$page]['route'][$index];
				}

				if ($link !== null)
					break;
			}

			// If link was not found, there must be a misconfiguration somewhere,
			// because we already know that the page exists at this point.
			if (!isset($link))
				throw new Exception('Page does not exist: ' . $page, self::E_PAGE_DOES_NOT_EXIST);

			// Remove leading / (/home -> home)
			$link = mb_substr($link, 1);
			$link = $this->m_app->getRequestHandler()->getRootFolder() . $link;

			// some-other-page-([0-9]+)(?:-([0-9]+))? -> some-other-page-128-226
			if (!empty($parameters)) {

				preg_match_all(RegexPatterns::unescapedParenthesisGroups(), $link, $hit, PREG_PATTERN_ORDER);

				$hitCount = count($hit[1]);
				for ($i = 0; $i < $hitCount; $i++) {

					// We only want to replace the first occurrence, like page-([0-9])-([0-9]) -> page-####-([0-9])
					// str_replace doesn't have this option, so we convert it to regex
					$re = '#' . preg_quote($hit[1][$i], '#') . '#';
					$link = preg_replace($re, '##########' . $i . '##########', $link, 1);
				}

				// Now we can clean the path
				$link = preg_replace(RegexPatterns::unescapedMetacharacters(), '', $link);

				for ($i = 0; $i < $hitCount; $i++) {

					// Replace ##### by corresponding parameter value (###1### -> param1, ###2### -> param2, etc.)
					$link = str_replace('##########' . $i . '##########', $parameters[$i], $link);
				}
			}

			// home -> http://www.domain.com/home
			if ($includeSiteURL)
				$link = $this->m_app->getSiteUrl() . $link; // App::getSiteUrl() has no trailing /

			return $link;
		}

		/**
		 * Loads the appropriate controller.
		 */
		public function route(): void {

			$page = '/' . $this->m_app->getRequestHandler()->getRequestPage();
			$locale = null;
			$controller = null;

			// Loop through the routes in configuration, and match them again the requested route
			// We use a regex so we can have variable parameters
			// (Note that controller is given, otherwise mapRoutes() would have failed).
			foreach ($this->m_mappedRoutes as $pagePattern => $route) { // contains controller & lang

				// If the pattern is config matches a request
				// We extract controller, locale and parameters
				if (preg_match('#^' . $pagePattern . '$#', $page, $matches)) {

					if (empty($route['locale']) || $route['locale'] == 'all')
						$this->m_app->getLanguages()->getCurrentLocale();
					else
						$this->m_app->getLanguages()->setCurrentLocale($route['locale']);

					// $controller = \App\Controller\HomeController
					$controller = '\App\Controller\\' . $route['controller'];

					$this->m_app->getRequestHandler()->setRequestParameters($matches);

					$this->m_currentPage = $route['page'];

					// Stop searching after first match.
					break;
				}
			}

			// If we found a match, a controller will be set
			if ($controller !== null) {

				if ($this->m_app->getFirewall()->authenticationRequiredFor($this->m_currentPage)
					&& !$this->m_app->getUser()->isLoggedIn()) {

					$this->redirectToLoginPage();
				}

				if ($this->m_app->getFirewall()->authenticatedDisallowedFor($this->m_currentPage)
					&& $this->m_app->getUser()->isLoggedIn()) {

					$this->redirectToAuthenticatedDisallowed();
				}

				// $controller = new \App\Controller\HomeController()
				$controller = new $controller($this->m_app);
					$controller->render();

			// If not, it's a 404
			} else {
				$this->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);
			}

			exit;
		}

		/**
		 * @param string $location
		 */
		public function redirectTo(string $location): void {

			header("Location: $location");
			exit;
		}

		/**
		 * @param int|null $errorCode
		 * @throws \Exception
		 */
		public function requestErrorDocument(?int $errorCode): void {
			$this->redirectToErrorDocument($errorCode);
		}

		/**
		 * @param int|null $errorCode
		 * @throws \Exception
		 */
		private function redirectToErrorDocument(?int $errorCode): void {

			$controller = new HttpErrorController($this->m_app);
			$controller->setHttpError($errorCode);
			$controller->render();

			exit;
		}

		/**
		 * When you try to access a page where you must be connected and your are not
		 *
		 * @throws \Exception
		 */
		private function redirectToLoginPage(): void {

			$loginPage = $this->m_app->getAuthentication()->getLoginPage(); // Page ID
				$loginPage = $this->getLinkForPage($loginPage);

			Session::set(Authentication::AFTER_LOGIN_REDIRECT_TO,
			             $this->m_app->getRequestHandler()->getRequestURI()); // In case _last is configured

			$this->redirectTo($loginPage);
		}

		/**
		 * When you try to access a page you can't if you're connected (e.g. login)
		 *
		 * Redirects to 403 if no page set.
		 *
		 * In the rare cases where you don't have a Router set at the time of the redirection,
		 * it will default to RequestHandler::getRootFolder()
		 */
		private function redirectToAuthenticatedDisallowed(): void {
			$this->m_app->getFirewall()->redirectToAuthenticatedDisallowed();
		}
	}

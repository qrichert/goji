<?php

	namespace Goji\Core;

	use App\Controller\System\PasswordWallController;
	use Goji\Blueprints\HttpStatusInterface;
	use Goji\HumanResources\Authentication;
	use Goji\Parsing\RegexPatterns;
	use Goji\Toolkit\SimpleCache;
	use Goji\Toolkit\SimpleMetrics;
	use Goji\Toolkit\UrlManager;
	use Goji\Translation\Languages;
	use App\Controller\System\HttpErrorController;
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
		private $m_currentPageIsErrorPage;
		private $m_currentPageIsPasswordWallPage;

		/* <CONSTANTS> */

		const CONFIG_FILE = ROOT_PATH . '/config/routes.json5';

		const ACCEPT_ALL = 'all';
		const ACCEPT_MULTIPLE = 'multiple';

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
		public function __construct(App $app, string $configFile = self::CONFIG_FILE) {

			$this->m_app = $app;

		// Routes

			$baseCacheId = SimpleCache::cacheIDFromFileFullPath($configFile);

			$this->m_routes = null;

			// Formatted routes caching
			$cacheId =  $baseCacheId . '--formatted';

			if (SimpleCache::isValidFilePreprocessed($cacheId, $configFile)) {
				$this->m_routes = SimpleCache::loadFilePreprocessed($cacheId);
					$this->m_routes = json_decode($this->m_routes, true);
			} else {
				$this->m_routes = ConfigurationLoader::loadFileToArray($configFile, false); // Useless to cache it 2 times, config never gets used raw
					$this->m_routes = $this->formatRoutes($this->m_routes);
				SimpleCache::cacheFilePreprocessed(json_encode($this->m_routes), $configFile, $cacheId);
			}


		// Mapped routes

			$this->m_mappedRoutes = null;

			// Mapped Routes caching
			$cacheId = $baseCacheId . '--mapped';

			if (SimpleCache::isValidFilePreprocessed($cacheId, $configFile)) {
				$this->m_mappedRoutes = SimpleCache::loadFilePreprocessed($cacheId);
					$this->m_mappedRoutes = json_decode($this->m_mappedRoutes, true);
			} else {
				$this->m_mappedRoutes = $this->mapRoutes($this->m_routes);
				SimpleCache::cacheFilePreprocessed(json_encode($this->m_mappedRoutes), $configFile, $cacheId);
			}

			$this->m_currentPage = null;
			$this->m_currentPageIsErrorPage = false;
			$this->m_currentPageIsPasswordWallPage = false;
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

						/*
						 * Maybe in languages config there is 'en_US' and 'en_GB', but in routes config there is only 'en'
						 * In this case it would lead to the page not being found (current locale = 'en_US', searched locale = 'en')
						 *
						 * What we do here is if we find a locale that is not set in this languages config, and is 2 chars
						 * long (country code), we compare it to the supported locales.
						 *
						 * Then for each supported locale starting with the same country code, we add the corresponding route.
						 *
						 * Languages config:
						 * - en_US
						 * - en_GB
						 * - fr
						 *
						 * Routes config
						 * - en => /login
						 * - fr => /connexion
						 *
						 * After this process:
						 *
						 * Routes:
						 * - en => /login
						 * - en_US => /login (new)
						 * - en_GB => /login (new)
						 * - fr => /connexion
						 */
						if ($locale != self::ACCEPT_ALL
						    && mb_strlen($locale) == 2
						    && !in_array($locale, $this->m_app->getLanguages()->getSupportedLocales())) {

							foreach ($this->m_app->getLanguages()->getSupportedLocales() as $supportedLocale) {

								if ($locale != mb_substr($supportedLocale, 0, 2))
									continue;

								$config['routes'][$supportedLocale] = $route;
							}
						}

						// There can be multiple paths for the same page
						// You could have /home and /home-([0-9+]) with a parameter
						// Here we standardize them all
						// It's maybe less efficient but otherwise we would have too much
						// $mappedRoutes (with if/else and 'routes'/'route) = 4x, here only 2x
						$route = (array) $route;

						foreach ($route as &$alternativePath) {

							if (mb_substr($alternativePath, 0, 1) !== '/')
								$alternativePath = '/' . $alternativePath;
						}
						unset($alternativePath);
					}
					unset($route);

				} else if (isset($config['route'])
				           && (is_string($config['route']) || is_array($config['route']))) {

					$config['route'] = (array) $config['route'];

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

			$mappedRoutes = [];

			foreach ($routes as $page => $config) {

				// Controller is mandatory
				if (!isset($config['controller']) || !is_string($config['controller']))
					throw new Exception('Route is lacking controller. (' . $page .')', self::E_ROUTE_LACKING_CONTROLLER);

				$controller = AutoLoader::sanitizeController($config['controller']);

				if (isset($config['routes']) && is_array($config['routes'])) {

					foreach ($config['routes'] as $locale => $route) {

						// We know it's an array because it has been formatted by Router::formatRoutes()
						foreach ($route as $alternativePath) {

							/*
							 * Formatted routes can have multiple locales pointing to the same page
							 *
							 * Routes:
							 * - en => /login
							 * - en_US => /login
							 * - en_GB => /login
							 *
							 * Here, if we find a route that already has a locale set, we set it to self::ACCEPT_MULTIPLE and add a
							 * 'accept' parameter containing the accepted country codes.
							 *
							 * Locale set for the path, and not already set as multiple (multiple locales for same path,
							 * but first time we encounter another one) => Set the locale to 'multiple', move the set
							 * locale to 'accept' along with the new one
							 */
							if (!empty($mappedRoutes[$alternativePath]['locale'])
								&& $mappedRoutes[$alternativePath]['locale'] != self::ACCEPT_MULTIPLE) {

								// Extract country code from already set locale
								$forcedCountryCode = mb_substr($mappedRoutes[$alternativePath]['locale'], 0, 2);

								$mappedRoutes[$alternativePath]['locale'] = self::ACCEPT_MULTIPLE;
								$mappedRoutes[$alternativePath]['accept'] = [$forcedCountryCode, $locale];

								continue;

							} else if (!empty($mappedRoutes[$alternativePath]['locale'])
							           && $mappedRoutes[$alternativePath]['locale'] == self::ACCEPT_MULTIPLE) {

								// If already set to 'multiple', we just add the new locale to the 'accept'
								$mappedRoutes[$alternativePath]['accept'][] = $locale;

								continue;
							}

							$mappedRoutes[$alternativePath] = [
								'locale' => $locale,
								'controller' => $controller,
								'page' => $page
							];
						}
					}

				} else if (isset($config['route']) && is_array($config['route'])) {

					foreach ($config['route'] as $alternativePath) {

						$mappedRoutes[$alternativePath] = [
							'locale' => self::ACCEPT_ALL, // No specific language
							'controller' => $controller,
							'page' => $page
						];
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

		/**
		 * Returns true if current page is set, else false.
		 *
		 * @return bool
		 */
		public function hasCurrentPage(): bool {
			return isset($this->m_currentPage);
		}

		/**
		 * Returns true if current page is a http-error-* page, false if not.
		 *
		 * @return bool
		 */
		public function getCurrentPageIsErrorPage(): bool {
			return $this->m_currentPageIsErrorPage;
		}

		/**
		 * Returns true if current page is password wall page, false if not.
		 * @return bool
		 */
		public function getCurrentPageIsPasswordWallPage(): bool {
			return $this->m_currentPageIsPasswordWallPage;
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
		 * @param array|null $parameters (optional) Parameters to replace regex (e.g. '(.+)?') with
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

			$isErrorPage = mb_substr($page, 0, 11) == 'http-error-';
			$isPasswordWallPage = $page == 'password-wall';

			// Make sure page exists
			if (!isset($this->m_routes[$page]) && !$isErrorPage && !$isPasswordWallPage)
				throw new Exception('Page does not exist: ' . $page, self::E_PAGE_DOES_NOT_EXIST);

			if (!isset($locale))
				$locale = $this->m_app->getLanguages()->getCurrentLocale(); // Current one if none given

			// Make sure locale exists
			if (!isset($this->m_app->getLanguages()->getConfigurationLocales()[$locale]))
				throw new Exception('Locale does not exist: ' . $locale, self::E_LOCALE_DOES_NOT_EXIST);

			// Do we need to force the locale ?
			// If a different locale than the current one is requested, we force it
			$forceLocale = ($locale != $this->m_app->getLanguages()->getCurrentLocale());

			// Make sure index is set
			if ($index === null)
				$index = 0;

			$link = null;

			$locales = [$locale, $this->m_app->getLanguages()->getFallbackLocale()];
			foreach ($locales as $loc) {

				// If we have [page][routes][locale]
				if (isset($this->m_routes[$page]['routes'][$loc][$index])) {

					$link = $this->m_routes[$page]['routes'][$loc][$index];

				// If we have [page][routes][all]
				} else if (isset($this->m_routes[$page]['routes'][self::ACCEPT_ALL][$index])) {

					$link = $this->m_routes[$page]['routes'][self::ACCEPT_ALL][$index];

				// If we have [page][route]
				} else if (isset($this->m_routes[$page]['route'][$index])) {

					$link = $this->m_routes[$page]['route'][$index];
				}

				if ($link !== null)
					break;
			}

			// 404 pages can't have routes for example, neither can password wall
			// So we give it the 'current link'(i.e. the requested page)
			if (!isset($link) && ($isErrorPage || $isPasswordWallPage)) {
				$link = '/' . $this->m_app->getRequestHandler()->getRequestPage();
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

				// /goji/public/blog/(.+)? + blog-post-permalink

				preg_match_all(RegexPatterns::unescapedParenthesisGroups(), $link, $hit, PREG_PATTERN_ORDER);

				$hitCount = count($hit[1]);
				for ($i = 0; $i < $hitCount; $i++) {

					// We only want to replace the first occurrence, like page-([0-9])-([0-9]) -> page-####-([0-9])
					// str_replace doesn't have this option, so we convert it to regex
					$re = '#' . preg_quote($hit[1][$i], '#') . '#';
					$link = preg_replace($re, '##########' . $i . '##########', $link, 1);

				}

				// /goji/public/blog/##########0##########?

				// Now we can clean the path
				$link = preg_replace(RegexPatterns::unescapedMetacharacters(), '', $link);

				// /goji/public/blog/##########0##########

				for ($i = 0; $i < $hitCount; $i++) {

					// Replace ##### by corresponding parameter value (###1### -> param1, ###2### -> param2, etc.)
					$link = str_replace('##########' . $i . '##########', $parameters[$i], $link);
				}

				// /goji/public/blog/blog-post-permalink
			}

			// home -> http://www.domain.com/home
			if ($includeSiteURL)
				$link = $this->m_app->getSiteUrl() . $link; // App::getSiteUrl() has no trailing /

			// /page?forceLocale=en_US
			if ($forceLocale) {

				// If there isn't already a query string
				if (!parse_url($link, PHP_URL_QUERY))
					$link .= '?';
				else
					$link .= '&';

				$link .= 'forceLocale=' . $locale;
			}

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

					// If no locale specified, or accepts any locale
					if (empty($route['locale']) || $route['locale'] == self::ACCEPT_ALL) {

						// Just keep the current one (calling the getCurrentLocale() is necessary because it initializes the locale)
						$this->m_app->getLanguages()->getCurrentLocale();

					// If accepts multiple locales (but not just any!)
					} else if ($route['locale'] == self::ACCEPT_MULTIPLE) {

						// Get the old/default one
						$currentLocale = $this->m_app->getLanguages()->getCurrentLocale();

						// If 'multiple' country codes are set
						if (!empty($route['accept'])) {

							$acceptedLocales = $route['accept'];

							// Exact match
							$match = Languages::atLeastOneLocaleMatches($currentLocale, $acceptedLocales, true);

							// If there is an exact match, the current locale is already accepted, we good

							// If no exact match
							if ($match === false) {

								// Try a country code match
								$match = Languages::atLeastOneLocaleMatches($currentLocale, $acceptedLocales, false);

								// If we had a country match
								if (is_array($match)) {

									// Take the country match
									[$_, $match] = $match;

								// No country match
								} else {
									// Nothing found at all -> take the first accepted locale in the list
									$match = $acceptedLocales[0];
								}

								$forcedLocale = $this->m_app->getLanguages()->getBestLocaleMatchForCountryCode($match);

								if ($forcedLocale !== null)
									$this->m_app->getLanguages()->setCurrentLocale($forcedLocale);
							}
						}

					// Specific locale given (force locale)
					} else {

						$this->m_app->getLanguages()->setCurrentLocale($route['locale']);
					}

					// $controller = \App\Controller\HomeController
					$controller = $route['controller'];

					$this->m_app->getRequestHandler()->setRequestParameters($matches);

					$this->m_currentPage = $route['page'];

					// Stop searching after first match.
					break;
				}
			}

			// If we found a match, a controller will be set
			if ($controller !== null) {

				// Requires authentication
				if ($this->m_app->getFirewall()->authenticationRequiredFor($this->m_currentPage)) {

					// But user not logged in... -> redirect to login page
					if (!$this->m_app->getUser()->isLoggedIn())
						$this->redirectToLoginPage();

					// Logged in. But role not sufficient... -> 404
					if (!$this->m_app->getMemberManager()->memberIs($this->m_app->getFirewall()->roleRequiredFor($this->m_currentPage)))
						$this->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);
				}

				// Can't visit if authenticated but user is logged in
				if ($this->m_app->getFirewall()->authenticatedDisallowedFor($this->m_currentPage)
					&& $this->m_app->getUser()->isLoggedIn()) {

					$this->redirectToAuthenticatedDisallowed();
				}

				// Add page view
				SimpleMetrics::addPageView($this->m_app->getRouter()->getCurrentPage());

				// $controller = new \App\Controller\HomeController()
				$controller = new $controller($this->m_app);

					if ($controller->useCache()) {

						if (!$controller->renderCachedVersion()) { // Cache invalid

							$controller->startCacheBuffer();
							$controller->render();
							$controller->saveCacheBuffer(true);
						}

					} else {

						$controller->render();
					}

			// If not, it's a 404
			} else {
				$this->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);
			}

			exit;
		}

		/**
		 * In case there is a forced locale (forceLocale in query string) we make the change, remove the forceLocale and redirect.
		 *
		 * @param string $newLocale
		 * @throws \Exception
		 */
		public function requestLocaleSwitch(string $newLocale): void {

			// If locale doesn't exist -> 404
			if (!in_array($newLocale, $this->m_app->getLanguages()->getSupportedLocales()))
				$this->redirectToErrorDocument(self::HTTP_ERROR_NOT_FOUND);

			// Change lang
			$this->m_app->getLanguages()->setCurrentLocale($newLocale);

			// Get current query string & remove the forceLocale part
			$queryString = $this->m_app->getRequestHandler()->getQueryString();
			unset($queryString['forceLocale']);

			// Rebuild the query string without forceLocale
			$queryString = UrlManager::buildQueryStringFromArray($queryString);

			$redirectTo = $this->m_app->getRequestHandler()->getRequestPageURI();

				if (!empty($queryString))
					$redirectTo .= '?' . $queryString;

			$this->redirectTo($redirectTo);
			exit;
		}

		/**
		 * @param string $location
		 */
		public function redirectTo(string $location): void {

			$location = trim($location);

			header("Location: $location");
			exit;
		}

		/**
		 * @param int|null $errorCode
		 * @throws \Exception
		 */
		public function redirectToErrorDocument(?int $errorCode): void {

			$this->m_currentPageIsErrorPage = true;

			if (!HttpErrorController::isValidError($errorCode))
				$errorCode = HttpErrorController::HTTP_ERROR_DEFAULT;

			// Override 'page' (ex: admin -> 403 -> override to 'http-error-403')
			$this->m_currentPage = 'http-error-' . (string) $errorCode;

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
		public function redirectToLoginPage(): void {

			$loginPage = $this->m_app->getAuthentication()->getLoginPage(); // Page ID
				$loginPage = $this->getLinkForPage($loginPage);

			Session::set(Authentication::AFTER_LOGIN_REDIRECT_TO,
			             $this->m_app->getRequestHandler()->getRequestURI()); // In case _last is configured

			$this->redirectTo($loginPage);
			exit;
		}

		/**
		 * When you try to access a page you can't if you're connected (e.g. login)
		 *
		 * Redirects to 403 if no page set.
		 *
		 * In the rare cases where you don't have a Router set at the time of the redirection,
		 * it will default to RequestHandler::getRootFolder()
		 */
		public function redirectToAuthenticatedDisallowed(): void {
			$this->m_app->getFirewall()->redirectToAuthenticatedDisallowed();
			exit;
		}

		public function redirectToPasswordWall(): void {

			$this->m_currentPageIsPasswordWallPage = true;

			$this->m_currentPage = 'password-wall';

			$controller = new PasswordWallController($this->m_app);
				$controller->render();

			exit;
		}
	}

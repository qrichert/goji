<?php

	namespace Goji\Core;

	/**
	 * Class Firewall
	 *
	 * @package Goji\Core
	 */
	class Firewall {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_routesRequiringAuthentication;
		private $m_routesDisallowingAuthenticated;
		private $m_onForbiddenRedirectTo;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/authentication.json5';

		public function __construct(App $app, $configFile = self::CONFIG_FILE) {

			$this->m_app = $app;

			$config = ConfigurationLoader::loadFileToArray($configFile);

			$this->m_routesRequiringAuthentication = (array) $config['firewall']['require_authentication'] ?? array();

			$this->m_routesDisallowingAuthenticated = (array) $config['firewall']['disallow_authenticated'] ?? array();

			$this->m_onForbiddenRedirectTo = $config['forbidden']['redirect_to'] ?? null;
		}

		/**
		 * @param string $page Route ID of the page to be matched
		 * @return bool
		 */
		public function authenticationRequiredFor(string $page): bool {
			return in_array($page, $this->m_routesRequiringAuthentication);
		}

		/**
		 * @param string $page
		 * @return bool
		 */
		public function authenticatedDisallowedFor(string $page): bool {
			return in_array($page, $this->m_routesDisallowingAuthenticated);
		}

		public function redirectToAuthenticatedDisallowed(): void {

			$redirectTo = null; // Default, use root folder

			if ($this->m_onForbiddenRedirectTo !== null && $this->m_app->hasRouter()) // Unless custom folder is set
				$redirectTo = $this->m_app->getRouter()->getLinkForPage($this->m_onForbiddenRedirectTo);

			// If no page ID given (redirect_to: null)
			// Redirect to 403
			if ($redirectTo === null && $this->m_app->hasRouter())
				$this->m_app->getRouter()->requestErrorDocument(Router::HTTP_ERROR_FORBIDDEN);

			// If no page ID given, and/or App doesn't have a router, in which
			// case we can't redirect to 403, we default to redirecting to the root
			if ($redirectTo === null)
				$redirectTo = $this->m_app->getRequestHandler()->getRootFolder();

			header("Location: $redirectTo");
			exit;
		}
	}

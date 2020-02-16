<?php

namespace Goji\HumanResources;

use Goji\Core\App;
use Goji\Core\ConfigurationLoader;
use Goji\Core\Session;

/**
 * Class Authentication
 *
 * @package Goji\HumanResources
 */
class Authentication {

	/* <ATTRIBUTES> */

	private $m_app;
	private $m_loginPage;
	private $m_onLoginSuccessRedirectTo;

	/* <CONSTANTS> */

	const CONFIG_FILE = ROOT_PATH . '/config/authentication.json5';

	const AFTER_LOGIN_REDIRECT_TO = 'after-login-redirect-to';

	public function __construct(App $app, string $configFile = self::CONFIG_FILE) {

		$this->m_app = $app;

		$config = ConfigurationLoader::loadFileToArray($configFile);

		if (!array_key_exists('login', $config))
			$config['login'] = null;

		$this->m_loginPage = $config['login']['route'] ?? null;

		$this->m_onLoginSuccessRedirectTo = $config['login']['redirect_to'] ?? '_last';
			$this->m_onLoginSuccessRedirectTo = (array) $this->m_onLoginSuccessRedirectTo;
	}

	/**
	 * @return string|null Route ID of the login page
	 */
	public function getLoginPage(): ?string {
		return $this->m_loginPage;
	}

	/**
	 * Get the page to redirect to if any
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getRedirectToOnLogInSuccess(): string {

		foreach ($this->m_onLoginSuccessRedirectTo as $pageID) {

			if ($pageID == '_last' && !empty(Session::get(self::AFTER_LOGIN_REDIRECT_TO))) { // Require _last && last page is set

				$redirectTo = Session::get(self::AFTER_LOGIN_REDIRECT_TO);
				Session::unset(self::AFTER_LOGIN_REDIRECT_TO); // Single use only

				return $redirectTo;

			} else if ($pageID != '_last') { // Regular page ID

				// W/o Router we can't determine page path from ID
				// And without page ID we can't redirect to specific page
				// So exit the loop to use default
				if (!$this->m_app->hasRouter() || empty($pageID))
					break;

				$redirectTo = $this->m_app->getRouter()->getLinkForPage($pageID);

				return $redirectTo;
			}
		}

		// If nothing found, just redirect to root
		$redirectTo = $this->m_app->getRequestHandler()->getRootFolder();

		return $redirectTo;
	}

	/**
	 * After login, redirect to other page (given in config).
	 */
	public function redirectToLogInSuccess(): void {

		$redirectTo = $this->getRedirectToOnLogInSuccess();

		$this->m_app->getRouter()->redirectTo($redirectTo);
	}
}

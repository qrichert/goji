<?php

	namespace Goji\Core;

	use Goji\HumanResources\Authentication;
	use Goji\HumanResources\HrFactory;
	use Goji\HumanResources\MemberManager;
	use Goji\HumanResources\User;
	use Goji\Translation\Languages;
	use Goji\Translation\Translator;
	use PDO;
	use Exception;

	/**
	 * Class App
	 *
	 * @package Goji\Core
	 */
	class App {

		/* <ATTRIBUTES> */

		private $m_siteUrl;
		private $m_siteName;
		private $m_siteDomainName;
		private $m_siteFullDomain;
		private $m_companyEmail;

		private $m_appMode;

		private $m_languages;
		private $m_requestHandler;
		private $m_databases;
		private $m_memberManager;
		private $m_user;
		private $m_authentication;
		private $m_firewall;
		private $m_router;
		private $m_translator;

		private $m_passwordWallPassword;
		private $m_showPasswordWall;

		/* <CONSTANTS> */

		const CONFIG_FILE = ROOT_PATH . '/config/app.json5';

		const DEBUG = 'debug';
		const RELEASE = 'release';

		const PASSWORD_WALL_COOKIE = 'password-wall-authenticated';

		const E_NO_LANGUAGES = 0;
		const E_NO_ROUTER = 1;
		const E_USER_LOGGED_IN = 2;
		const E_NO_TRANSLATOR = 3;

		/**
		 * App constructor.
		 *
		 * Loads data from config file.
		 * Missing info will be ignored.
		 * Setting new values using set*() won't change config file,
		 * it will only affect current runtime.
		 *
		 * @param string $configFile (optional) default = App::CONFIG_FILE
		 * @throws \Exception
		 */
		public function __construct(string $configFile = self::CONFIG_FILE) {

			$config = ConfigurationLoader::loadFileToArray($configFile);

			$this->setSiteUrl($config['site_url'] ?? '');
			$this->setSiteName($config['site_name'] ?? '');
			$this->setSiteDomainName($config['site_domain_name'] ?? '');
			$this->setSiteFullDomain($config['site_full_domain'] ?? '');
			$this->setCompanyEmail($config['company_email'] ?? '');

			if (isset($config['debug']) && $config['debug'] === true)
				$this->setAppMode(self::DEBUG);
			else
				$this->setAppMode(self::RELEASE); // Default

			$this->m_languages = null;
			$this->m_requestHandler = new RequestHandler();
			$this->m_databases = [];
			$this->m_memberManager = null;
			$this->m_user = HrFactory::getUser($this);
				$this->m_user->updateMemberManager();
			$this->m_authentication = new Authentication($this);
			$this->m_firewall = new Firewall($this);
			$this->m_router = null;
			$this->m_translator = null;

			// If set, use password wall. If not set or empty, don't use it
			$this->m_passwordWallPassword = !empty($config['password_wall']) ? (string) $config['password_wall'] : null;
			// Use it IF password IS set AND cookie IS NOT set
			$this->m_showPasswordWall = !empty($this->m_passwordWallPassword)
			                                && empty(Cookies::get(self::PASSWORD_WALL_COOKIE));
		}

		/**
		 * @return string
		 */
		public function getSiteUrl(): string {
			return $this->m_siteUrl;
		}

		/**
		 * Set site URL attribute.
		 *
		 * Removes trailing '/'.
		 *
		 * https://www.SITE_URL.com/ -> https://www.SITE_URL.com
		 *
		 * @param string $siteUrl
		 */
		public function setSiteUrl(string $siteUrl): void {

			if (mb_substr($siteUrl, -1) == '/')
				$siteUrl = mb_substr($siteUrl, 0, -1);

			$this->m_siteUrl = $siteUrl;
		}

		/**
		 * @return string
		 */
		public function getSiteName(): string {
			return $this->m_siteName;
		}

		/**
		 * @param string $siteName
		 */
		public function setSiteName(string $siteName): void {
			$this->m_siteName = $siteName;
		}

		/**
		 * domain.com
		 *
		 * @return string
		 */
		public function getSiteDomainName(): string {
			return $this->m_siteDomainName;
		}

		/**
		 * domain.com
		 *
		 * @param string $siteDomainName
		 */
		public function setSiteDomainName(string $siteDomainName): void {

			if (mb_substr($siteDomainName, -1) == '/')
				$siteDomainName = mb_substr($siteDomainName, 0, -1);

			$this->m_siteDomainName = $siteDomainName;
		}

		/**
		 * www.domain.com
		 *
		 * @return string
		 */
		public function getSiteFullDomain(): string {
			return $this->m_siteFullDomain;
		}

		/**
		 * www.domain.com
		 *
		 * @param string $siteFullDomain
		 */
		public function setSiteFullDomain(string $siteFullDomain): void {

			if (mb_substr($siteFullDomain, -1) == '/')
				$siteFullDomain = mb_substr($siteFullDomain, 0, -1);

			$this->m_siteFullDomain = $siteFullDomain;
		}

		/**
		 * user@domain.com
		 *
		 * @return string
		 */
		public function getCompanyEmail(): string {
			return $this->m_companyEmail;
		}

		/**
		 * user@domain.com
		 *
		 * @param string $companyEmail
		 */
		public function setCompanyEmail(string $companyEmail): void {
			$this->m_companyEmail = $companyEmail;
		}

		/**
		 * @return string
		 */
		public function getAppMode(): string {
			return $this->m_appMode;
		}

		/**
		 * @param string \Goji\Core\App::APP_MODE $appMode
		 */
		public function setAppMode(string $appMode = self::RELEASE): void {

			$appMode = mb_strtolower($appMode);

			if ($appMode == self::DEBUG
				|| $appMode == self::RELEASE) {

				$this->m_appMode = $appMode;

			} else {

				$this->m_appMode = self::RELEASE; // Default
			}
		}

		/**
		 * @return \Goji\Translation\Languages
		 * @throws \Exception
		 */
		public function getLanguages(): Languages {

			if (isset($this->m_languages))
				return $this->m_languages;
			else
				throw new Exception('No languages have been set.', self::E_NO_LANGUAGES);
		}

		/**
		 * @param \Goji\Translation\Languages $languages
		 */
		public function setLanguages(Languages $languages): void {
			$this->m_languages = $languages;
		}

		/**
		 * @return bool
		 */
		public function hasLanguages(): bool {
			return isset($this->m_languages);
		}

		/**
		 * @return \Goji\Core\RequestHandler
		 */
		public function getRequestHandler(): RequestHandler {
			return $this->m_requestHandler;
		}

		/**
		 * Creates a new database instance. If one existed before, it will be replaced.
		 *
		 * @param string|null $databaseId
		 * @return \PDO
		 * @throws \Exception
		 */
		public function createDatabase(string $databaseId = null): PDO {

			$db = new Database($databaseId);

			// Save db under ID
			$this->m_databases[$db->getDatabaseID()] = $db;

			if ($this->m_appMode == self::DEBUG)
				$db->logErrors(true);

			return $db;
		}

		/**
		 * @param string|null $databaseId
		 * @return \Goji\Core\Database|\PDO
		 * @throws \Exception
		 */
		public function getDatabase(string $databaseId = null): PDO {

			if (!empty($databaseId)) { // Wants specific one

				if (isset($this->m_databases[$databaseId]))
					return $this->m_databases[$databaseId];
				else
					return $this->createDatabase($databaseId);
			}

			// Wants first one that works
			if ($this->hasDatabase())
				return $this->m_databases[array_key_first($this->m_databases)];
			else
				return $this->createDatabase();
		}

		/**
		 * Alias to App::getDatabase(), only shorter.
		 *
		 * @param array $args
		 * @return \Goji\Core\Database|\PDO
		 * @throws \Exception
		 */
		public function db(...$args): PDO {
			return $this->getDatabase(...$args);
		}

		/**
		 * Add an externally defined PDO to the database list.
		 *
		 * @param \PDO|\Goji\Core\Database $database
		 * @param string $databaseId
		 */
		public function addDatabase(PDO $database, string $databaseId): void {
			$this->m_databases[$databaseId] = $database;
		}

		/**
		 * @return bool
		 */
		public function hasDatabase(): bool {
			return !empty($this->m_databases);
		}

		/**
		 * @return \Goji\HumanResources\MemberManager
		 */
		public function getMemberManager(): MemberManager {
			return $this->m_memberManager;
		}

		/**
		 * @param \Goji\HumanResources\MemberManager|null $memberManager
		 * @throws \Exception
		 */
		public function setMemberManager(?MemberManager $memberManager): void {

			if (empty($memberManager) && $this->m_user->isLoggedIn())
				throw new Exception('Cannot set MemberManager to null while User is still logged in.', self::E_USER_LOGGED_IN);

			$this->m_memberManager = $memberManager;
		}

		/**
		 * @return \Goji\HumanResources\User
		 */
		public function getUser(): User {
			return $this->m_user;
		}

		/**
		 * @param \Goji\HumanResources\User $user
		 */
		public function setUser(User $user) {
			$this->m_user = $user;
		}

		/**
		 * @return \Goji\HumanResources\Authentication
		 */
		public function getAuthentication(): Authentication {
			return $this->m_authentication;
		}

		/**
		 * @return \Goji\Core\Firewall
		 */
		public function getFirewall(): Firewall {
			return $this->m_firewall;
		}

		/**
		 * @return \Goji\Core\Router
		 * @throws \Exception
		 */
		public function getRouter(): Router {

			if (isset($this->m_router))
				return $this->m_router;
			else
				throw new Exception('No router has been set.', self::E_NO_ROUTER);
		}

		/**
		 * @param \Goji\Core\Router $router
		 */
		public function setRouter(Router $router): void {
			$this->m_router = $router;
		}

		/**
		 * @return bool
		 */
		public function hasRouter(): bool {
			return isset($this->m_router);
		}

		/**
		 * @return \Goji\Translation\Translator
		 * @throws \Exception
		 */
		public function getTranslator(): Translator {

			if (isset($this->m_translator))
				return $this->m_translator;
			else
				throw new Exception('No translator has been set.', self::E_NO_TRANSLATOR);
		}

		/**
		 * @param \Goji\Translation\Translator $translator
		 */
		public function setTranslator(Translator $translator): void {
			$this->m_translator = $translator;
		}

		/**
		 * @return bool
		 */
		public function hasTranslator(): bool {
			return isset($this->m_translator);
		}

		/**
		 * Returns password set in config/app.json5, or null if not set
		 *
		 * @return string|null
		 */
		public function getPasswordWallPassword(): ?string {
			return $this->m_passwordWallPassword;
		}

		/**
		 * Starts the routing process.
		 *
		 * @throws \Exception
		 */
		public function exec(): void {

			if (!isset($this->m_languages))
				$this->m_languages = new Languages($this);

			if (!isset($this->m_router))
				$this->m_router = new Router($this);

			if ($this->m_requestHandler->getForcedLocaleDetected() !== null)
				$this->m_router->requestLocaleSwitch($this->m_requestHandler->getForcedLocaleDetected());
			else if ($this->m_showPasswordWall)
				$this->m_router->redirectToPasswordWall();
			else if ($this->m_requestHandler->getErrorDetected())
				$this->m_router->redirectToErrorDocument($this->m_requestHandler->getRedirectStatus());
			else
				$this->m_router->route();
		}

		/**
		 * Bypasses default routing process to display error page instantly.
		 *
		 * @param int|null $errorCode
		 * @throws \Exception
		 */
		public function redirectToErrorDocument(?int $errorCode): void {

			if (!isset($this->m_languages))
				$this->m_languages = new Languages($this);

			if (!isset($this->m_router))
				$this->m_router = new Router($this);

			$this->m_router->redirectToErrorDocument($errorCode);
		}
	}

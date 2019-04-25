<?php

	namespace Goji\Core;

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
		private $m_cookiesPrefix;

		private $m_isLocalEnvironment;
		private $m_appMode;

		private $m_dataBase;
		private $m_requestHandler;
		private $m_router;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/app.json5';

		const DEBUG = 'debug';
		const RELEASE = 'release';

		const E_NO_DATABASE = 0;
		const E_NO_ROUTER = 1;

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
		public function __construct($configFile = self::CONFIG_FILE) {

			$config = ConfigurationLoader::loadFileToArray($configFile);

			$this->setSiteUrl($config['site_url'] ?? '');
			$this->setSiteName($config['site_name'] ?? '');
			$this->setSiteDomainName($config['site_domain_name'] ?? '');
			$this->setSiteFullDomain($config['site_full_domain'] ?? '');
			// TODO: Cookies class, prefix auto loaded
			$this->setCookiesPrefix($config['cookies_prefix'] ?? '');

			$this->setIsLocalEnvironment(false);
			$this->setAppMode($config['app_mode']);

			$this->m_dataBase = null;
			$this->m_requestHandler = new RequestHandler();
			$this->m_router = null;
		}

		/**
		 * @return string
		 */
		public function getSiteUrl(): string {
			return $this->m_siteUrl;
		}

		/**
		 * Set site name attribute.
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
			$this->m_siteFullDomain = $siteFullDomain;
		}

		/**
		 * @return string
		 */
		public function getCookiesPrefix(): string {
			return $this->m_cookiesPrefix;
		}

		/**
		 * @param string $cookiesPrefix
		 */
		public function setCookiesPrefix(string $cookiesPrefix): void {
			$this->m_cookiesPrefix = $cookiesPrefix;
		}

		/**
		 * @return bool
		 */
		public function getIsLocalEnvironment(): bool {
			return $this->m_isLocalEnvironment;
		}

		/**
		 * @param bool $isLocalEnvironment
		 */
		public function setIsLocalEnvironment(bool $isLocalEnvironment): void {
			$this->m_isLocalEnvironment = $isLocalEnvironment;
		}

		/**
		 * @return string
		 */
		public function getAppMode(): string {
			return $this->m_appMode ?? self::DEBUG;
		}

		/**
		 * @param \Goji\Core\App::APP_MODE $applicationMode
		 */
		public function setAppMode(string $appMode): void {

			if ($appMode == self::DEBUG
				|| $appMode == self::RELEASE) {

				$this->m_appMode = $appMode;
			}
		}

		/**
		 * Creates a new database instance. If one existed before, it will be replaced.
		 *
		 * @throws \Exception
		 */
		public function createDataBase(): void {
			$this->m_dataBase = new DataBase();
		}

		/**
		 * @return \Goji\Core\DataBase|\PDO
		 * @throws \Exception
		 */
		public function getDataBase(): PDO {

			if (isset($this->m_dataBase))
				return $this->m_dataBase;
			else
				throw new Exception('No database has been set.', self::E_NO_DATABASE);
		}

		/**
		 * Alias to App::getDataBase(), only shorter.
		 *
		 * @return \Goji\Core\DataBase|\PDO
		 * @throws \Exception
		 */
		public function db(): PDO {
			return $this->getDataBase();
		}

		/**
		 * @param \PDO|\Goji\Core\DataBase $database
		 */
		public function setDataBase(PDO $database): void {
			$this->m_dataBase = $database;
		}

		/**
		 * @return bool
		 */
		public function hasDataBase(): bool {
			return isset($this->m_dataBase);
		}

		/**
		 * @return \Goji\Core\RequestHandler
		 */
		public function getRequestHandler(): RequestHandler {
			return $this->m_requestHandler;
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
		 * @return bool
		 */
		public function hasRouter(): bool {
			return isset($this->m_router);
		}

		/**
		 * Starts the routing process.
		 *
		 * @throws \Exception
		 */
		public function exec(): void {

			if (!isset($this->m_router))
				$this->m_router = new Router($this);


			$this->m_router->route();
		}
	}

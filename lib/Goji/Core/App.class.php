<?php

	namespace Goji\Core;

	use Goji\Translation\Languages;
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

		private $m_appMode;
		private $m_linkedFilesMode;

		private $m_languages;
		private $m_requestHandler;
		private $m_router;
		private $m_dataBase;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/app.json5';

		const DEBUG = 'debug';
		const RELEASE = 'release';

		const NORMAL = 'normal';
		const MERGED = 'merged';

		const E_NO_LANGUAGES = 0;
		const E_NO_ROUTER = 1;
		const E_NO_DATABASE = 2;

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

			$this->setAppMode($config['app_mode']);
			$this->setLinkedFilesMode($config['linked_files_mode']);

			$this->m_languages = null;
			$this->m_requestHandler = new RequestHandler();
			$this->m_router = null;
			$this->m_dataBase = null;
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
		 * @return string
		 */
		public function getAppMode(): string {
			return $this->m_appMode ?? self::DEBUG;
		}

		/**
		 * @param \Goji\Core\App::APP_MODE $applicationMode
		 */
		public function setAppMode($appMode): void {

			$appMode = mb_strtolower($appMode);

			if ($appMode == self::DEBUG
				|| $appMode == self::RELEASE) {

				$this->m_appMode = $appMode;

			} else {

				$this->m_appMode = self::DEBUG; // Default
			}
		}

		/**
		 * @return string
		 */
		public function getLinkedFilesMode(): string {
			return $this->m_linkedFilesMode ?? self::NORMAL;
		}

		/**
		 * @param \Goji\Core\App::LINKED_FILES_MODE $linkedFilesMode
		 */
		public function setLinkedFilesMode($linkedFilesMode): void {

			$linkedFilesMode = mb_strtolower($linkedFilesMode);

			if ($linkedFilesMode == self::NORMAL
			|| $linkedFilesMode == self::MERGED) {

				$this->m_linkedFilesMode = $linkedFilesMode;

			} else {

				$this->m_linkedFilesMode = self::NORMAL; // Default
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
		 * Starts the routing process.
		 *
		 * @throws \Exception
		 */
		public function exec(): void {

			if (!isset($this->m_languages))
				$this->m_languages = new Languages();

			if (!isset($this->m_router))
				$this->m_router = new Router($this);


			$this->m_router->route();
		}
	}

<?php

	namespace Goji\Core;

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

		/* <CONSTANTS> */

		const DEBUG = 'debug';
		const RELEASE = 'release';

		const E_NO_DATABASE = 0;

		/**
		 * App constructor.
		 */
		public function __construct() {

			$this->m_siteUrl = '';
			$this->m_siteName = '';
			$this->m_siteDomainName = '';
			$this->m_siteFullDomain = '';
			$this->m_cookiesPrefix = '';

			$this->m_isLocalEnvironment = false;
			$this->m_appMode = self::DEBUG;

			$this->m_dataBase = null;
			$this->m_requestHandler = new RequestHandler();
		}

		/**
		 * @return string
		 */
		public function getSiteURL() {
			return $this->m_siteUrl;
		}

		/**
		 * @param string $siteUrl
		 */
		public function setSiteURL($siteUrl) {
			$this->m_siteUrl = $siteUrl;
		}

		/**
		 * @return string
		 */
		public function getSiteName() {
			return $this->m_siteName;
		}

		/**
		 * @param string $siteName
		 */
		public function setSiteName($siteName) {
			$this->m_siteName = $siteName;
		}

		/**
		 * domain.com
		 *
		 * @return string
		 */
		public function getSiteDomainName() {
			return $this->m_siteDomainName;
		}

		/**
		 * domain.com
		 *
		 * @param string $siteDomainName
		 */
		public function setSiteDomainName($siteDomainName) {
			$this->m_siteDomainName = $siteDomainName;
		}

		/**
		 * www.domain.com
		 *
		 * @return string
		 */
		public function getSiteFullDomain() {
			return $this->m_siteFullDomain;
		}

		/**
		 * www.domain.com
		 *
		 * @param string $siteFullDomain
		 */
		public function setSiteFullDomain($siteFullDomain) {
			$this->m_siteFullDomain = $siteFullDomain;
		}

		/**
		 * @return string
		 */
		public function getCookiesPrefix() {
			return $this->m_cookiesPrefix;
		}

		/**
		 * @param string $cookiesPrefix
		 */
		public function setCookiesPrefix($cookiesPrefix) {
			$this->m_cookiesPrefix = $cookiesPrefix;
		}

		/**
		 * @return bool
		 */
		public function getIsLocalEnvironment() {
			return $this->m_isLocalEnvironment;
		}

		/**
		 * @param bool $isLocalEnvironment
		 */
		public function setIsLocalEnvironment($isLocalEnvironment) {

			if (is_bool($isLocalEnvironment))
				$this->m_isLocalEnvironment = $isLocalEnvironment;
		}

		/**
		 * @return string
		 */
		public function getAppMode() {
			return $this->m_appMode;
		}

		/**
		 * @param \Goji\Core\App::APP_MODE $applicationMode
		 */
		public function setAppMode($appMode) {

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
		public function createDataBase() {
			$this->m_dataBase = new DataBase();
		}

		/**
		 * @return \Goji\Core\DataBase
		 * @throws \Exception
		 */
		public function getDataBase() {

			if (isset($this->m_dataBase))
				return $this->m_dataBase;
			else
				throw new Exception('No database has been set.', self::E_NO_DATABASE);
		}

		/**
		 * Alias to App::getDataBase(), only shorter.
		 *
		 * @return \Goji\Core\DataBase
		 * @throws \Exception
		 */
		public function db() {
			return $this->getDataBase();
		}

		/**
		 * @param \Goji\Core\DataBase $database
		 * @throws \Exception
		 */
		public function setDataBase(DataBase $database) {
			$this->m_dataBase = $database;
		}

		/**
		 * @return bool
		 */
		public function hasDataBase() {
			return isset($this->m_dataBase);
		}

		/**
		 * @return \Goji\Core\RequestHandler
		 */
		public function getRequestHandler() {
			return $this->m_requestHandler;
		}
	}

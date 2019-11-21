<?php

	namespace Goji\Translation;

	use Goji\Core\App;
	use Goji\Core\ConfigurationLoader;
	use Goji\Core\Cookies;
	use Goji\Core\Session;
	use Exception;

	/**
	 * Class Languages
	 *
	 * Determines which language to use if none given.
	 * You can use this class just to get the user's preferred languages.
	 *
	 * @package Goji\Translation
	 */
	class Languages {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_configurationLocales; // Loaded from config file + $this->formatConfigurationLocales($config);
		private $m_supportedLocales; // Same as $this->m_configurationLocales, but just the keys, w/o description
		private $m_fallbackLocale; // Loaded from config file + $this->formatConfigurationLocales($config);
		private $m_userPreferredLocales; // $this->fetchUserPreferredLocales(); Defined in HTTP header, formatted
		private $m_currentLocale; // $this->fetchCurrentLocale(); Best match between config & user
		private $m_currentCountryCode; // just the two first letters en_US -> en

		/* <CONSTANTS> */

		const CONFIG_FILE = ROOT_PATH . '/config/languages.json5';

		const E_NO_LANGUAGES_CONFIGURED = 0;
		const E_LOCALE_DOES_NOT_EXIST = 1;

		/**
		 * Languages constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $configFile
		 */
		public function __construct(App $app, string $configFile = self::CONFIG_FILE) {

			$this->m_app = $app;

			// First we load config
			$config = null;

			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

				// If loading was successful, we format it so we can use it more easily
				list($this->m_configurationLocales, $this->m_fallbackLocale) = $this->formatConfigurationLocales($config);

				$this->m_supportedLocales = [];

				foreach ($this->m_configurationLocales as $locale => $description) {
					$this->m_supportedLocales[] = $locale;
				}

			} catch (Exception $e) {

				// If we couldn't load the file, we can't fill-in those attributes
				$this->m_configurationLocales = null;
				$this->m_supportedLocales = null;
				$this->m_fallbackLocale = null;
			}

			// Get user language config (from HTTP headers)
			$this->fetchUserPreferredLocales();

			// Set with setCurrentLocale() or by loadCurrentLocale() if getCurrentLocale() is
			// called and $this->m_currentLocale is still null
			//
			// We don't init it systematically, because it is a heavy process and if the locale
			// is forced (most cases), we don't need the old one. This saves us some processing.
			//
			// This is because pages "force" their language, but there are some pages
			// without a specific language, so we must get the best one possible (last one used
			// and if there's no last one used (first visit) we get the best match according to
			// the user's browser preferences.)
			$this->m_currentLocale = null;
			$this->m_currentCountryCode = null;
		}

		function __debugInfo() {

			$configurationLocales = isset($this->m_configurationLocales) ?
									print_r($this->m_configurationLocales, true) :
									'NULL';

			$userPreferredLocales = isset($this->m_userPreferredLocales) ?
									print_r($this->m_userPreferredLocales, true) :
									'NULL';

			echo 'Configuration Locales: ' . $configurationLocales, PHP_EOL;
			echo 'Fallback Locale: ' . ($this->m_fallbackLocale ?? 'NULL'), PHP_EOL;
			echo 'User Preferred Locales: ' . $userPreferredLocales, PHP_EOL;
			echo 'Current Locale: ' . ($this->m_currentLocale ?? 'NULL'), PHP_EOL;
		}

		/**
		 * Returns all locales in configuration with description.
		 *
		 * 'en_US' => 'English (US)'
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function getConfigurationLocales(): array {

			if (isset($this->m_configurationLocales))
				return $this->m_configurationLocales;
			else
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);
		}

		/**
		 * Returns all locales, without description.
		 *
		 * 'en_US', 'en_GB', 'fr'
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function getSupportedLocales(): array {

			if (isset($this->m_supportedLocales))
				return $this->m_supportedLocales;
			else
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);
		}

		/**
		 * Returns true if country code matches, else returns false.
		 *
		 * Examples:
		 * - en, en_US ? true
		 * - en, en ? true
		 * - en_US, en_GB ? true
		 * - fr, en ? false
		 *
		 * @param $locale1
		 * @param $locale2
		 * @return bool
		 */
		public static function countryMatches($locale1, $locale2): bool {
			return mb_substr($locale1, 0, 2) == mb_substr($locale2, 0, 2);
		}

		/**
		 * Takes two array of locales and checks if at least there are two where the country matches.
		 *
		 * en_US - it_IT
		 * en_GB - en_NZ
		 * fr_FR - zh_CN
		 * de_DE -
		 *
		 * Match: en_US && en_NZ
		 *
		 * (en_GB - en_NZ doesn't match because we already returned after first match)
		 *
		 * @param $locales1
		 * @param $locales2
		 * @return bool|array False if none, locales as array if found
		 */
		public static function atLeastOneCountryMatches($locales1, $locales2): bool {

			$locales1 = (array) $locales1;
			$locales2 = (array) $locales2;

			foreach ($locales1 as $loc1) {

				foreach ($locales2 as $loc2) {

					if (self::countryMatches($loc1, $loc2))
						return [$loc1, $loc2];
				}
			}

			return false;
		}

		/**
		 * If two locales match
		 *
		 * exactMatch: true
		 *    en_NZ, en_GB -> no
		 *
		 * exactMatch: false
		 *    en_NZ, en_GB -> yes (on 'en')
		 *
		 * @param $locale1
		 * @param $locale2
		 * @param bool $exactMatch Country only
		 * @return bool
		 */
		public static function localeMatches($locale1, $locale2, $exactMatch = true): bool {

			if ($locale1 == $locale2)
				return true;

			if ($exactMatch) // We know they don't match exactly at this point
				return false;

			return self::countryMatches($locale1, $locale2);
		}

		/**
		 * Takes two array of locales and checks if at least there are two where they match.
		 *
		 * @param $locales1
		 * @param $locales2
		 * @param bool $exactMatch Country only
		 * @return bool|array False if none, both as array of true
		 */
		public static function atLeastOneLocaleMatches($locales1, $locales2, $exactMatch = true) {

			$locales1 = (array) $locales1;
			$locales2 = (array) $locales2;

			foreach ($locales1 as $loc1) {

				foreach ($locales2 as $loc2) {

					if (self::localeMatches($loc1, $loc2, $exactMatch))
						return [$loc1, $loc2];
				}
			}

			return false;
		}

		/**
		 * Returns first locale in config that matches a given country code.
		 *
		 * @param string $countryCode
		 * @return string|null
		 */
		public function getBestLocaleMatchForCountryCode(string $countryCode): ?string {

			// Make sure it's a country code and not a full locale
			$countryCode = mb_substr($countryCode, 0, 2);

			foreach ($this->m_supportedLocales as $locale) {

				if ($countryCode == mb_substr($locale, 0, 2))
					return $locale;
			}

			return null;
		}

		/**
		 * Returns all locales, without description, and with hyphens instead of underscores.
		 *
		 * 'en-US', 'en-GB', 'fr'
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function getSupportedHyphenLocales(): array {

			$locales = $this->getSupportedLocales();

			foreach ($locales as &$locale) {
				$locale = $this->hyphenateLocale($locale);
			}

			return $locales;
		}

		/**
		 * Replace underscore by hyphen in locale.
		 *
		 * en_US -> en-US
		 *
		 * @param string $locale
		 * @return string
		 */
		public static function hyphenateLocale(string $locale): string {
			return (string) str_replace('_', '-', $locale);
		}

		/**
		 * @return array
		 */
		public function getUserPreferredLocales(): array {
			return $this->m_userPreferredLocales;
		}

		/**
		 * @return string
		 * @throws \Exception
		 */
		public function getFallbackLocale(): string {

			if (isset($this->m_configurationLocales))
				return $this->m_fallbackLocale;
			else
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);
		}

		/**
		 * Returns current locale, and creates it if it doesn't exist. Ex: en_US.
		 *
		 * @return string
		 * @throws \Exception
		 */
		public function getCurrentLocale(): string {

			// If a locale is set (this getter has already been called, or one has been set by hand)
			if (isset($this->m_currentLocale))
				return $this->m_currentLocale;

			// If not (first time this getter called & none set by hand)
			// We must load the last locale or generate one into $this->m_currentLocale;
			$this->loadCurrentLocale();

			return $this->m_currentLocale;
		}

		/**
		 * Returns current locale but with a hyphen separator. Ex: en-US.
		 *
		 * HTML uses the dash version.
		 *
		 * @return string
		 * @throws \Exception
		 */
		public function getCurrentHyphenLocale(): string {
			return $this->hyphenateLocale($this->getCurrentLocale());
		}

		public function getCurrentCountryCode(): string {

			if (empty($this->m_currentCountryCode))
				$this->m_currentCountryCode = mb_substr($this->getCurrentLocale(), 0, 2);

			return $this->m_currentCountryCode;
		}

		/**
		 * Set the current locale by hand.
		 *
		 * @param string $locale
		 * @throws \Exception
		 */
		public function setCurrentLocale(string $locale): void {

			// If we use config, locale must comply
			if (isset($this->m_configurationLocales)) {

				if (isset($this->m_configurationLocales[$locale]))
					$this->m_currentLocale = $locale;
				else
					throw new Exception('Locale does not exist: ' . $locale, self::E_LOCALE_DOES_NOT_EXIST);

			} else {

				// If we don't use config, it's not useful, but it doesn't matter either
				$this->m_currentLocale = $locale;
			}

			Cookies::set('locale', $this->m_currentLocale);
			Session::set('locale', $this->m_currentLocale);
		}

		/**
		 * Fetch all user preferred locales as a list of locales.
		 *
		 * Extracted from $_SERVER['HTTP_ACCEPT_LANGUAGE']
		 * Returned as ['en_US', 'en_GB', 'fr']
		 *
		 * Called from __construct()
		 */
		private function fetchUserPreferredLocales(): void {

			// Format: fr-FR,en-US;q=0.8,en-GB;q=0.7,en;q=0.5,it;q=0.3,de-DE;q=0.2
			// Not standard across browsers
			$httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

			if (empty($httpAcceptLanguage)) {
				$this->m_userPreferredLocales = [];
				return;
			}

			// Split on what is not a letter, hyphen - or underscore _ (i.e. what is not part of a locale)
			$httpAcceptLanguage = preg_split('#[^a-z-_]#i', $httpAcceptLanguage);

			$locales = [];

			// Extract languages in order of preference
			foreach ($httpAcceptLanguage as $locale) {

				if (preg_match('#^([a-z]{2})[_-]([a-z]{2})$#i', $locale, $matches)) {

					// Standardized format lang_REGION
					$locales[] = mb_strtolower($matches[1]) . '_' . mb_strtoupper($matches[2]);

				} else if (preg_match('#^([a-z]{2})$#i', $locale)) {

					$locales[] = mb_strtolower($locale);
				}
			}

			$this->m_userPreferredLocales = $locales;
		}

		/**
		 * Make sure locales in config match lang_REGION format.
		 *
		 * Config file example:
		 * Array
		 * (
		 *      [fallback] => en_US
		 *      [en_US] => English (US)
		 *      [en_GB] => English (GB)
		 *      [fr] => Français
		 * )
		 *
		 * Called from __construct()
		 *
		 * @param array $locales
		 * @return array
		 */
		private function formatConfigurationLocales(array $locales): array {

			// Not the most memory efficient (references will be copied) but the safest
			$newLocales = [];

			$fallbackLocale = null;

			foreach ($locales as $locale => $alias) {

				if ($locale == 'fallback') {

					if (preg_match('#^([a-z]{2})[_-]([a-z]{2})$#i', $alias, $matches))
						$alias = mb_strtolower($matches[1]) . '_' . mb_strtoupper($matches[2]);
					else
						$alias = mb_strtolower($alias);

					$fallbackLocale = $alias;

					continue;
				}

				if (preg_match('#^([a-z]{2})[_-]([a-z]{2})$#i', $locale, $matches)) // lang_REGION
					$locale = mb_strtolower($matches[1]) . '_' . mb_strtoupper($matches[2]);
				else if (preg_match('#^([a-z]{2})[*_-]+$#i', $locale, $matches)) // Clean any en_ or en_*
					$locale = mb_strtolower($matches[1]);
				else // eN -> en
					$locale = mb_strtolower($locale);

				$newLocales[$locale] = $alias;
			}

			if (!isset($fallbackLocale)) { // If no fallback set, we use first language in list

				foreach ($newLocales as $locale => $alias) {

					// Break the loop after first pass, we only want the first
					$fallbackLocale = $locale;
					break;
				}
			}

			return [$newLocales, $fallbackLocale];
		}

		/**
		 * Matches config locales with user locales and selects best match (first one)
		 *
		 * ALWAYS returns a locale.
		 * 1. Perfect match (lang + region)
		 * 2. First locale of preferred language in config (lang)
		 * 3. Fallback locale
		 * 4. First locale in config
		 *
		 * If no locale in config, throws Exception(E_NO_LANGUAGES_CONFIGURED)
		 *
		 * Called from loadCurrentLocale()
		 *
		 * @throws \Exception
		 */
		private function fetchCurrentLocale(): string {

			if (empty($this->m_configurationLocales))
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);

			$langMatch = null;

			// We isolate the language first, without the locale
			foreach ($this->m_userPreferredLocales as $userLocale) {

				$userLang = mb_substr($userLocale, 0, 2); // en_US -> en

				foreach ($this->m_configurationLocales as $configLocale => $alias) {

					$configLocale = mb_substr($configLocale, 0, 2); // en_US -> en

					if ($userLang == $configLocale) { // en == en

						// We found one, we good
						$langMatch = $userLang;
						break;
					}
				}

				// Same here
				if ($langMatch !== null)
					break;
			}

			// So now that we have isolated the language, we refine our match by locale.
			if ($langMatch !== null) {

				foreach ($this->m_configurationLocales as $configLocale => $alias) {

					// Only work with matched language
					if (mb_substr($configLocale, 0, 2) !== $langMatch)
						continue;

					foreach ($this->m_userPreferredLocales as $userLocale) {

						// We found a match
						if ($configLocale === $userLocale) {

							return $configLocale;
						}
					}
				}
			}

			// If still no match, means we have the language, but not the locale
			// So we take the first corresponding language in the list
			if ($langMatch !== null) {

				foreach ($this->m_configurationLocales as $configLocale => $alias) {

					// Only work with matched language
					if (mb_substr($configLocale, 0, 2) !== $langMatch)
						continue;

					// Select the first matching locale (by lang) and quit
					return $configLocale;
				}
			}

			// Now, if we are here, it just means that user language isn't supported.
			// We use our fallback
			if (isset($this->m_configurationLocales[$this->m_fallbackLocale])) {

				return $this->m_fallbackLocale;
			}

			// If fallback didn't work, we take the first lang in config
			foreach ($this->m_configurationLocales as $locale => $alias) {

				// Break the loop after first pass, we only want the first
				return $locale;
			}
		}

		/**
		 * Load $this->m_currentLocale either from cookie or generates it.
		 *
		 * Called from getCurrentLocale() if $this->m_currentLocale is null
		 *
		 * This function ALWAYS sets a string value for $this->m_currentLocale, because
		 * it either loads one from a cookie and makes sure it's valid, or it uses
		 * fetchCurrentLocale() which always returns a locale.
		 *
		 * @throws \Exception
		 */
		private function loadCurrentLocale(): void {

			if (empty($this->m_configurationLocales))
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);

			// If config is used and valid, we look for a current locale,
			// either in a cookie, or we generate it

			// If it's in a session, load from session
			if (!empty(Session::get('locale'))) {

				$locale = Session::get('locale');

				// Look if the locale is a valid one
				if (isset($this->m_configurationLocales[$locale])) {
					$this->m_currentLocale = $locale;
				} else {
					$this->m_currentLocale = $this->fetchCurrentLocale();
					Session::set('locale', $this->m_currentLocale);
				}

			// If it's in a cookie, load cookie
			} else if (!empty(Cookies::get('locale'))) {

				$locale = Cookies::get('locale');

				// Look if the locale is a valid one
				if (isset($this->m_configurationLocales[$locale])) {
					$this->m_currentLocale = $locale;
				} else {
					$this->m_currentLocale = $this->fetchCurrentLocale();
					Cookies::set('locale', $this->m_currentLocale);
				}

			} else {

				$this->m_currentLocale = $this->fetchCurrentLocale();
				Cookies::set('locale', $this->m_currentLocale);
				Session::set('locale', $this->m_currentLocale);
			}
		}
	}

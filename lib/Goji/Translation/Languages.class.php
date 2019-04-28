<?php

	namespace Goji\Translation;

	use Goji\Core\ConfigurationLoader;
	use Goji\Core\Cookies;
	use Exception;

	/**
	 * Class Languages
	 *
	 * @package Goji\Translation
	 */
	class Languages {

		/* <ATTRIBUTES> */

		private $m_configurationLocales; // Loaded from config file + $this->formatConfigurationLocales($config);
		private $m_fallbackLocale; // Loaded from config file + $this->formatConfigurationLocales($config);
		private $m_userPreferredLocales; // $this->fetchUserPreferredLocales(); Defined in HTTP header, formatted
		private $m_currentLocale; // $this->fetchCurrentLocale(); Best match between config & user

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/languages.json5';

		const E_NO_LANGUAGES_CONFIGURED = 0;
		const E_LOCALE_DOES_NOT_EXIST = 1;

		public function __construct($configFile = self::CONFIG_FILE) {

			// First we load config
			$config = null;

			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

				// If loading was successful, we format it so we can use it more easily
				list($this->m_configurationLocales, $this->m_fallbackLocale) = $this->formatConfigurationLocales($config);

			} catch (Exception $e) {

				// If we couldn't load the file, we can't fill-in those attributes
				$this->m_configurationLocales = null;
				$this->m_fallbackLocale = null;
			}

			// Get user language config (from HTTP headers)
			$this->fetchUserPreferredLocales();

			// Set with setCurrentLocale() or by loadCurrentLocale() if getCurrentLocale() is
			// called and $this->m_currentLocale is still null
			// This is because pages "force" their language, but there are some pages
			// without a specific language, so we must get the best one possible (last one used
			// and if there's no last one used (first visit) we get the best match according to
			// the user's browser preferences.)
			$this->m_currentLocale = null;
		}

		function __debugInfo() {

			$configurationLocales = isset($this->m_configurationLocales) ?
									print_r($this->m_configurationLocales, true) :
									'NULL';

			$userPreferredLocales = isset($this->m_userPreferredLocales) ?
									print_r($this->m_userPreferredLocales, true) :
									'NULL';

			echo 'Configuration Locales: ' . $configurationLocales . PHP_EOL;
			echo 'Fallback Locale: ' . ($this->m_fallbackLocale ?? 'NULL') . PHP_EOL;
			echo 'User Preferred Locales: ' . $userPreferredLocales . PHP_EOL;
			echo 'Current Locale: ' . ($this->m_currentLocale ?? 'NULL') . PHP_EOL;
		}

		/**
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
		}

		/**
		 * Fetch all accepted locales by user as a list of locales.
		 *
		 * Extracted from $_SERVER['HTTP_ACCEPT_LANGUAGE']
		 * Returned as array('en_US', 'en_GB', 'fr')
		 *
		 * Called from __construct()
		 */
		private function fetchUserPreferredLocales(): void {

			// Format: fr-FR,en-US;q=0.8,en-GB;q=0.7,en;q=0.5,it;q=0.3,de-DE;q=0.2
			// Not standard across browsers
			$httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

			if (empty($httpAcceptLanguage)) {
				$this->m_userPreferredLocales = array();
				return;
			}

			// Split on what is not a letter, hyphen - or underscore _ (i.e. what is not part of a locale)
			$httpAcceptLanguage = preg_split('#[^a-z-_]#i', $httpAcceptLanguage);

			$locales = array();

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
			$newLocales = array();

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

			return array($newLocales, $fallbackLocale);
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
		private function fetchCurrentLocale(): void {

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

							$this->m_currentLocale = $configLocale;
							return;
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
					$this->m_currentLocale = $configLocale;
					return;
				}
			}

			// Now, if we are here, it just means that user language isn't supported.
			// We use our fallback
			if (isset($this->m_configurationLocales[$this->m_fallbackLocale])) {
				$this->m_currentLocale = $this->m_fallbackLocale;
				return;
			}

			// If fallback didn't work, we take the first lang in config
			foreach ($this->m_configurationLocales as $locale => $alias) {
				$this->m_currentLocale = $locale;
				// Break the loop after first pass, we only want the first
				return;
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

			// If it's in a cookie, load cookie
			if (!empty(Cookies::get('locale'))) {

				$locale = Cookies::get('locale');

				// Look if the locale is a valid one
				if (isset($this->m_configurationLocales[$locale])) {

					$this->m_currentLocale = $locale;

				} else {

					$this->fetchCurrentLocale();
					Cookies::set('locale', $this->m_currentLocale);
				}

			} else {

				$this->fetchCurrentLocale();
				Cookies::set('locale', $this->m_currentLocale);
			}
		}
	}

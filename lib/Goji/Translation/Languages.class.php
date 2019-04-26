<?php

	namespace Goji\Translation;

	use Goji\Core\ConfigurationLoader;
	use Exception;

	/**
	 * Class Languages
	 *
	 * @package Goji\Translation
	 */
	class Languages {

		/* <ATTRIBUTES> */

		private $m_useLanguages;
		private $m_userPreferredLocales;
		private $m_configurationLocales;
		private $m_fallbackLocale;
		private $m_currentLocale;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/languages.json5';

		const E_NO_LANGUAGES_CONFIGURED = 0;

		public function __construct($configFile = self::CONFIG_FILE) {

			$this->m_useLanguages = true;
			$config = null;

			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

			} catch (Exception $e) {

				$this->m_useLanguages = false;
			}

			$this->fetchUserPreferredLocales();

			if ($this->m_useLanguages) {

				$this->m_configurationLocales = $this->formatConfigurationLocales($config);

				if (!empty($this->m_configurationLocales))
					$this->fetchCurrentLocale();
				else
					$this->m_useLanguages = false;
			}

			if ($this->m_useLanguages === false) {

				$this->m_configurationLocales = null;
				$this->m_fallbackLocale = null;
				$this->m_currentLocale = null;
			}

		}

		/**
		 * @return array
		 */
		public function getUserPreferredLocales(): array {
			return $this->m_userPreferredLocales;
		}

		/**
		 * @return array
		 * @throws \Exception
		 */
		public function getConfigurationLocales(): array {

			if ($this->m_useLanguages)
				return $this->m_configurationLocales;
			else
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);
		}

		/**
		 * @return string
		 * @throws \Exception
		 */
		public function getFallbackLocale(): string {

			if ($this->m_useLanguages)
				return $this->m_fallbackLocale;
			else
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);
		}

		public function getCurrentLocale(): string {

			if ($this->m_useLanguages)
				return $this->m_currentLocale;
			else
				throw new Exception('No languages have been configured.', self::E_NO_LANGUAGES_CONFIGURED);
		}

		/**
		 * Fetch all accepted locales by user as a list of locales.
		 *
		 * Extracted from $_SERVER['HTTP_ACCEPT_LANGUAGE']
		 * Returned as array('en_US', 'en_GB', 'fr')
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
		 * @param array $locales
		 * @return array
		 */
		private function formatConfigurationLocales(array $locales): array {

			// Not the most memory efficient (references will be copied) but the safest
			// Also it will be cached so whatever
			$newLocales = array();

			foreach ($locales as $locale => $alias) {

				if ($locale == 'fallback') {

					if (preg_match('#^([a-z]{2})[_-]([a-z]{2})$#i', $alias, $matches))
						$alias = mb_strtolower($matches[1]) . '_' . mb_strtoupper($matches[2]);
					else
						$alias = mb_strtolower($alias);

					$this->m_fallbackLocale = $alias;

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

			return $newLocales;
		}

		/**
		 * Matches config locales with user locales and selects best match (first one)
		 */
		private function fetchCurrentLocale(): void {

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
	}

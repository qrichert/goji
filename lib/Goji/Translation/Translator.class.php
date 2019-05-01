<?php

	namespace Goji\Translation;

	use Goji\Core\App;
	use Exception;

	/**
	 * Class Translator
	 *
	 * @package Goji\Translation
	 */
	class Translator {

		/* <ATTRIBUTES> */

		private $m_app;
		private $m_targetLocale;
		private $m_resourcesLoaded;

		/* <CONSTANTS> */

		const BASE_PATH = '../translation/';

		const E_NO_TARGET_LOCALE = 0;
		const E_RESOURCE_NOT_FOUND = 1;
		const E_RESOURCE_ALREADY_LOADED = 2;

		/**
		 * Translator constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @throws \Exception
		 */
		public function __construct(App $app) {

			$this->m_app = $app;
			$this->m_targetLocale = $app->getLanguages()->getCurrentLocale();
			$this->m_resourcesLoaded = array();
		}

		/**
		 * @return string
		 * @throws \Exception
		 */
		public function getTargetLocale(): string {

			if (isset($this->m_targetLocale))
				return $this->m_targetLocale;
			else
				throw new Exception('Called translator but no target locale has been set.', self::E_NO_TARGET_LOCALE);
		}

		/**
		 * @param string $targetLocale
		 * @throws \Exception
		 */
		public function setTargetLocale(string $targetLocale): void {
			$this->m_targetLocale = $targetLocale;
		}

		/**
		 * Load a translation resource.
		 *
		 * Use %{LOCALE} placeholder in file path to dynamically use the current locale.
		 * Don't include ../translation/ in file path, this is automatic.
		 *
		 * If current locale is en_US, it will look for the file with en_US locale.
		 * If it doesn't find it, it looks for a file with the language code alone (en)
		 *
		 * @param string|array $file
		 * @throws \Exception
		 */
		public function loadTranslationResource($file): void {

			if (!is_array($file))
				$file = array($file);

			foreach ($file as $f) {

				// Try exact locale, and if not, use just the language code
				$locales = array(
					$this->m_targetLocale, // en_US
					mb_substr($this->m_targetLocale, 0, 2) // en
				);

				$translationResourceFound = false;

				foreach ($locales as $locale) {

					$translationResource = self::BASE_PATH . str_replace('%{LOCALE}', $locale, $f);

					if (isset($this->m_resourcesLoaded[$translationResource]))
						throw new Exception('Resource already loaded: ' . $translationResource, self::E_RESOURCE_ALREADY_LOADED);

					if (is_file($translationResource)) {

						require_once $translationResource;
						$translationResourceFound = true;
						// Hash tables are faster than array search.
						$this->m_resourcesLoaded[$translationResource] = 1;

						break;
					}
				}

				if (!$translationResourceFound) {

					throw new Exception('Translation source file not found: '
					                    . self::BASE_PATH . str_replace('%{LOCALE}', $this->m_targetLocale, $f),
					                    self::E_RESOURCE_NOT_FOUND);
				}
			}
		}

		public function translate(string $textID, int $count = -1): void {
			// TODO: use domain.key => value instead of constants
		}

		/**
		 * Alias for Translator::translate()
		 *
		 * @param string $textID
		 * @param int $count
		 */
		public function tr(string $textID, int $count = -1): void {
			$this->translate($textID, $count);
		}
	}

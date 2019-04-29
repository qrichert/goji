<?php

	namespace Goji\Translation;

	use Goji\Core\App;
	use Goji\Core\ConfigurationLoader;
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
		private $m_configuration;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/translation.json5';
		const BASE_PATH = '../translation/';

		const E_NO_TARGET_LOCALE = 0;
		const E_SOURCE_FILE_NOT_FOUND = 1;

		/**
		 * Translator constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @param string $configFile (optional) default = Translator::CONFIG_FILE
		 * @throws \Exception
		 */
		public function __construct(App $app, $configFile = self::CONFIG_FILE) {

			$this->m_app = $app;
			$this->m_targetLocale = null;
			// Contains language source files arranged by locale.
			$this->m_configuration = ConfigurationLoader::loadFileToArray($configFile);
		}

		/**
		 * @return string|null
		 * @throws \Exception
		 */
		public function getTargetLocale(): ?string {

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

			$this->loadLanguage();
		}

		/**
		 * @throws \Exception
		 */
		private function loadLanguage(): void {

			$keys = array('all', $this->m_targetLocale);
			foreach ($keys as $key) {

				if (isset($this->m_configuration[$key])
				    && !empty($this->m_configuration[$key])) {

					foreach ($this->m_configuration[$key] as $file) {

						$file = self::BASE_PATH . $file;

						if (is_file($file))
							require_once $file;
						else
							throw new Exception('Translation source file not found: ' . $file, self::E_SOURCE_FILE_NOT_FOUND);
					}
				}
			}
		}

		public function translate(string $textID, int $count = -1): void {
			// TODO: use domain.key => value instead of constants
		}
	}

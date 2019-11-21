<?php

	namespace Goji\Translation;

	use Goji\Core\App;
	use Goji\Toolkit\SimpleCache;
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
		private $m_segments;

		/* <CONSTANTS> */

		const BASE_PATH = ROOT_PATH . '/translation/';

		const E_NO_TARGET_LOCALE = 0;
		const E_RESOURCE_NOT_FOUND = 1;
		const E_RESOURCE_ALREADY_LOADED = 2;
		const E_RESOURCE_TYPE_NOT_SUPPORTED = 3;
		const E_COULD_NOT_PARSE_XML_FILE = 4;

		/**
		 * Translator constructor.
		 *
		 * @param \Goji\Core\App $app
		 * @throws \Exception
		 */
		public function __construct(App $app) {

			$this->m_app = $app;
			$this->m_targetLocale = $app->getLanguages()->getCurrentLocale();
			$this->m_resourcesLoaded = [];
			$this->m_segments = [];

			$this->m_app->setTranslator($this);
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
		 * @param string $file
		 */
		private function loadPHPConstants(string $file): void {
			require_once $file;
		}

		/**
		 * @param string $file
		 * @param string $currentPage
		 * @param bool $loadSegmentsAsConstants (optional) default = false. Load segments as define( ID , VALUE)
		 * @throws \Exception
		 */
		private function loadXML(string $file, string $currentPage, bool $loadSegmentsAsConstants = false): void {

			// If cached, load from cache
			$cacheId = SimpleCache::cacheIDFromFileFullPath($file) . '-' .
			           SimpleCache::cacheIDFromString($currentPage);

			if (SimpleCache::isValidFilePreprocessed($cacheId, $file)) {

				$segments = SimpleCache::loadFilePreprocessed($cacheId);
				$segments = json_decode($segments, true);

				// Load segments
				if ($loadSegmentsAsConstants) {

					foreach ($segments as $segmentID => $segmentValue)
						define($segmentID, $segmentValue);

				} else {

					$this->m_segments = array_merge($this->m_segments, $segments);
				}

				return;
			}

			// If we're here, cache is invalid

			libxml_use_internal_errors(true);
			$xml = simplexml_load_file($file);

			if ($xml === false) {

				$errors = "\n";

				foreach(libxml_get_errors() as $error) {
					$errors .= "\t-> " . $error->message;
				}

				throw new Exception('XML file could not be parsed: ' . $file . "\n" .  $errors . "\n", self::E_COULD_NOT_PARSE_XML_FILE);
			}

			$segments = [];

			foreach ($xml->page as $page) {

				// If not isset, it is global and we include it no matter what
				if (isset($page['id'])) {

					$pageID = (string) $page['id'];
						//$pageID = str_replace('*', '°°°WILDCARD°°°', $pageID); // Protect wildcard * http-error-°°°WILDCARD°°°
						//$pageID = preg_quote($pageID, '#');
						//$pageID = str_replace('°°°WILDCARD°°°', '(?:.*)', $pageID);
						$pageID = str_replace('#', '\#', $pageID); // Page ID is a regex, just escape #

					// If ID doesn't match, we don't include it
					if (!preg_match('#^' . $pageID . '$#i', $currentPage))
						continue;
				}

				foreach ($page->segment as $segment) {

					$segmentID = (string) $segment['id'];
					$segmentValue = null;

					// If segment is an array of values
					if (isset($segment->option)) {

						/*
						 * Array => {
						 *      'option-id-1' => 'option value 1',
						 *      'option-id-2' => 'option value 2',
						 * }
						 */
						$segmentValue = [];

						foreach ($segment->option as $option) {

							$segmentValue[(string) $option['id']] = (string) $option;
						}

					} else if (isset($segment->alternative)) {

						/*
						 * Array => {
						 *      'count-regex' => 'value',
						 *      'count-regex' => 'value',
						 *      'count-regex' => 'value'
						 * }
						 */
						$segmentValue = [];

						foreach ($segment->alternative as $alternative) {

							// Make sure it is lowercase, so that we can safely
							// use lowercase version in the rest of the code
							if (mb_strtolower($alternative['count']) == 'rest')
								$alternative['count'] = 'rest';

							$segmentValue[(string) $alternative['count']] = (string) $alternative;
						}

					} else {

						$segmentValue = (string) $segment;
					}

					$segments[$segmentID] = $segmentValue;
				}
			}

			// Load segments
			if ($loadSegmentsAsConstants) {

				foreach ($segments as $segmentID => $segmentValue)
					define($segmentID, $segmentValue);

			} else {

				$this->m_segments = array_merge($this->m_segments, $segments);
			}

			// Cache page segments
			SimpleCache::cacheFilePreprocessed(json_encode($segments), $file, $cacheId);
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
		 * You can load all segments as constants by setting $loadSegmentsAsConstants to true.
		 *
		 * @param string|array $file
		 * @param bool $loadSegmentsAsConstants (optional) default = false. Load segments as define( ID , VALUE)
		 * @param string|null $bypassCurrentPageWith Specify Id of page to load instead of using current page
		 * @throws \Exception
		 */
		public function loadTranslationResource($file, bool $loadSegmentsAsConstants = false, string $bypassCurrentPageWith = null): void {

			$currentPage = null;

				if ($bypassCurrentPageWith !== null)
					$currentPage = $bypassCurrentPageWith;
				else
					$currentPage = $this->m_app->getRouter()->getCurrentPage();

			$file = (array) $file;

			foreach ($file as $f) {

				// Try exact locale, and if not, use just the language code
				$locales = [
					$this->m_targetLocale, // en_US
					mb_substr($this->m_targetLocale, 0, 2) // en
				];

				$translationResourceFound = false;

				foreach ($locales as $locale) {

					$translationResource = self::BASE_PATH . str_replace('%{LOCALE}', $locale, $f);

					if (isset($this->m_resourcesLoaded[$translationResource . '-' . $currentPage]))
						throw new Exception('Resource already loaded: ' . $translationResource, self::E_RESOURCE_ALREADY_LOADED);

					if (is_file($translationResource)) {

						$extension = pathinfo($translationResource, PATHINFO_EXTENSION);
						$extension = mb_strtolower($extension);

						switch ($extension) {
							case 'php': $this->loadPHPConstants($translationResource);                                break;
							case 'xml': $this->loadXML($translationResource, $currentPage, $loadSegmentsAsConstants); break;
							default:
								throw new Exception('Resource type not supported: ' . $extension, self::E_RESOURCE_TYPE_NOT_SUPPORTED);
								break;
						}

						$translationResourceFound = true;
						// Hash tables are faster than array search.
						$this->m_resourcesLoaded[$translationResource . '-' . $currentPage] = 1;

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

		/**
		 * Print (or returns content) translation file as is (with the benefit of %{LOCALE} variable.
		 *
		 * Used to print resources containing raw text or data.
		 *
		 * @param string $file
		 * @param bool $output If true, print directly. If false, returns content as string
		 * @return string|null
		 * @throws \Exception
		 */
		public function printRawTranslationResource(string $file, $output = true): ?string {

			// Try exact locale, and if not, use just the language code
			$locales = [
				$this->m_targetLocale, // en_US
				mb_substr($this->m_targetLocale, 0, 2) // en
			];

			foreach ($locales as $locale) {

				$translationResource = self::BASE_PATH . str_replace('%{LOCALE}', $locale, $file);

				if (is_file($translationResource)) {

					// Output directly (default)
					if ($output) {
						readfile($translationResource);
						return null;
					}

					// Return as string
					return file_get_contents($translationResource);
				}
			}

			throw new Exception('Translation source file not found: '
			                    . self::BASE_PATH . str_replace('%{LOCALE}', $this->m_targetLocale, $file),
			                    self::E_RESOURCE_NOT_FOUND);
		}

		/**
		 * @param string $segmentID
		 * @param int $count
		 * @return array|string
		 */
		public function translate(string $segmentID, $count = -1) {

			if (isset($this->m_segments[$segmentID])) {

				// If we don't use count, return the segment as string or array (<option> or not)
				if ($count === -1)
					return $this->m_segments[$segmentID];

				// From here on we use the $count option

				// If it's not an array, the segment doesn't support $count
				// and it's just a regular segment
				// ($count is an associative array of REGEX => STRING)
				if (!is_array($this->m_segments[$segmentID])) {
					trigger_error("Segment '$segmentID' doesn't accept count, string returned.", E_USER_WARNING);
					return $this->m_segments[$segmentID];
				}

				// If we use count, we search for the alternative with the corresponding count
				foreach ($this->m_segments[$segmentID] as $altCount => $alternative) {

					// If $count matches <alternative count="REGEX">, return it
					// and replace %{COUNT} in string by $count: There are %{COUNT} foobars. -> There are 2 foobars.
					if (preg_match('#' . $altCount . '#', $count))
						return str_replace('%{COUNT}', $count, $alternative);
				}

				// If nothing found, we return the one that has 'rest' as count, or an error if none is set as 'rest'
				if (isset($this->m_segments[$segmentID]['rest'])) {
					return str_replace('%{COUNT}', $count, $this->m_segments[$segmentID]['rest']);
				} else {
					trigger_error("Segment '$segmentID' has no alternative set for '$count' and no default count, ID returned.", E_USER_WARNING);
					return $segmentID;
				}

			} else {
				trigger_error("Undefined segment '$segmentID', ID returned.", E_USER_WARNING);
				return $segmentID;
			}
		}

		/**
		 * Alias for Translator::translate()
		 *
		 * @param array $args
		 * @return array|string
		 */
		public function tr(...$args) {
			return $this->translate(...$args);
		}

		/**
		 * Alias for Translator::translate()
		 *
		 * @param array $args
		 * @return array|string
		 */
		public function _(...$args) {
			return $this->translate(...$args);
		}
	}

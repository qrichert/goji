<?php

	namespace Goji\StaticFiles;

	use Exception;
	use Goji\Core\ConfigurationLoader;
	use Goji\Core\HttpResponse;
	use Goji\Parsing\SimpleMinifierCSS;
	use Goji\Toolkit\SimpleCache;

	/**
	 * Class FileRendererCSS
	 *
	 * @package Goji\StaticFiles
	 */
	class FileRendererCSS extends FileRendererAbstract {

		/* <ATTRIBUTES> */

		private $m_replaceCSSVariablesByValue;

		/* <CONSTANTS> */

		const CONFIG_FILE = ROOT_PATH . '/config/templating.json5';

		public function __construct(StaticServer $server, string $configFile = self::CONFIG_FILE) {

			parent::__construct($server);

			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

				$this->m_replaceCSSVariablesByValue = $config['replace_css_variables_by_value'] ?? false;

			} catch (Exception $e) {

				$this->m_replaceCSSVariablesByValue = false;
			}

			HttpResponse::setContentType(HttpResponse::CONTENT_CSS);
		}

		public function renderMerged() {

			// Generating cache ID
			$cacheId = SimpleCache::cacheIDFromFileFullPath($this->m_files);

			if (SimpleCache::isValidFilePreprocessed($cacheId, $this->m_files)) { // Get cached version

				SimpleCache::loadFilePreprocessed($cacheId, true);

			} else { // Regenerate and cache

				$content = SimpleMinifierCSS::minifyFile($this->m_files, $this->m_replaceCSSVariablesByValue);

				SimpleCache::cacheFilePreprocessed($content, $this->m_files, $cacheId);

				echo $content;
			}
		}
	}

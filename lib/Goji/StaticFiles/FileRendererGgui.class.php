<?php

	namespace Goji\StaticFiles;

	use Goji\Core\HttpResponse;
	use Goji\Parsing\SimpleMinifierJS;
	use Goji\Toolkit\SimpleCache;

	/**
	 * Class FileRendererGgui
	 *
	 * @package Goji\StaticFiles
	 */
	class FileRendererGgui extends FileRendererAbstract {

		/* <CONSTANTS> */

		const GGUI_IMPORT_PATH = 'js/lib/Goji/Ggui/';
		const GGUI_IMPORT_FILE = self::GGUI_IMPORT_PATH . 'Ggui.js';

		public function __construct(StaticServer $server) {

			parent::__construct($server);

			$this->m_files = [];

			HttpResponse::setContentType(HttpResponse::CONTENT_JS);
		}

		private function buildGgui(): string {

			$importFile = file_get_contents(self::GGUI_IMPORT_FILE);

			// matches -, * or + followed by any white space, and then a class name
			preg_match_all('#^[\s\r\n\t\p{Z}]*[-*+][\s\r\n\t\p{Z}]*([a-zA-Z0-9_]+)#m', $importFile, $matches, PREG_PATTERN_ORDER);
			$matches = $matches[1]; // first capturing group (contains index name without the brackets [])

			$matches = (array) $matches;

			foreach ($matches as $match) {

				$match = self::GGUI_IMPORT_PATH . $match . '.class.js';

				if (!in_array($match, $this->m_files))
					$this->m_files[] = $match;
			}

			return SimpleMinifierJS::minifyFile($this->m_files);
		}

		/**
		 * Content of js/lib/Goji/Ggui/Ggui.js is replaced with built library (merged files) and cached.
		 */
		private function renderGgui() {

			if (!is_file(self::GGUI_IMPORT_FILE))
				$this->m_server->fileNotFound();

			$cacheId = SimpleCache::cacheIDFromFileFullPath(self::GGUI_IMPORT_FILE);

			if (SimpleCache::isValidFilePreprocessed($cacheId, self::GGUI_IMPORT_FILE)) {

				SimpleCache::loadFilePreprocessed($cacheId, true);

			} else {

				$content = $this->buildGgui();

				SimpleCache::cacheFilePreprocessed($content, self::GGUI_IMPORT_FILE, $cacheId);

				echo $content;
			}
		}

		public function renderFlat() {
			$this->renderGgui();
		}

		public function renderMerged() {
			$this->renderGgui();
		}
	}

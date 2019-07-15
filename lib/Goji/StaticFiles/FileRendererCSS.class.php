<?php

	namespace Goji\StaticFiles;

	use Goji\Parsing\SimpleMinifierCSS;
	use Goji\Toolkit\SimpleCache;

	/**
	 * Class FileRendererCSS
	 *
	 * @package Goji\StaticFiles
	 */
	class FileRendererCSS extends FileRendererAbstract {

		public function __construct(StaticServer $server) {

			parent::__construct($server);

			header('Content-type: text/css; charset=utf-8');
		}

		public function renderMerged() {

			// Generating cache ID
			$cacheId = SimpleCache::cacheIDFromFileFullPath($this->m_files);

			if (SimpleCache::isValidFilePreprocessed($cacheId, $this->m_files)) { // Get cached version

				SimpleCache::loadFilePreprocessed($cacheId, true);

			} else { // Regenerate and cache

				$content = SimpleMinifierCSS::minifyFile($this->m_files);

				SimpleCache::cacheFilePreprocessed($content, $this->m_files, $cacheId);

				echo $content;
			}
		}
	}

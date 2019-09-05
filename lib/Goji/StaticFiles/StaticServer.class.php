<?php

	namespace Goji\StaticFiles;

	use Goji\Core\ConfigurationLoader;
	use Exception;

	/**
	 * Class StaticServer
	 *
	 * @package Goji\StaticFiles
	 */
	class StaticServer {

		/* <ATTRIBUTES> */

		private $m_linkedFilesMode;
		private $m_requestFileURI;
		private $m_fileType;
		private $m_files;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/templating.json5';

		const NORMAL = 'normal';
		const MERGED = 'merged';

		const CSS = 'css';
		const JAVASCRIPT = 'js';
		const GGUI = 'ggui';

		const SUPPORTED_FILE_TYPES = [self::CSS, self::JAVASCRIPT, self::GGUI];

		const E_REQUEST_IS_EMPTY = 0;
		const E_FILE_NOT_FOUND = 1;
		const E_FILE_TYPE_NOT_SUPPORTED = 2;

		public function __construct($configFile = self::CONFIG_FILE) {

			// Config
			try {

				$config = ConfigurationLoader::loadFileToArray($configFile);

				if (isset($config['linked_files_mode'])
				    && ($config['linked_files_mode'] == self::NORMAL
				        || $config['linked_files_mode'] == self::MERGED))
							$this->m_linkedFilesMode = $config['linked_files_mode'];
				else
					$this->m_linkedFilesMode = self::NORMAL;

			} catch (Exception $e) {

				$this->m_linkedFilesMode = self::NORMAL;
			}

			// Request file URI
			$this->m_requestFileURI = $_SERVER['REQUEST_URI'];

				if (strpos($this->m_requestFileURI, '%7C') !== false)
					$this->m_requestFileURI = rawurldecode($this->m_requestFileURI);

				// We only want the page, not the query string
				// /home?q=query -> /home
				$pos = mb_strpos($this->m_requestFileURI, '?');

				if ($pos !== false)
					$this->m_requestFileURI = mb_substr($this->m_requestFileURI, 0, $pos);

				if (empty($this->m_requestFileURI))
					throw new Exception("Request is empty", self::E_REQUEST_IS_EMPTY);

			// Extract files from request
			$this->m_files = explode('|', $this->m_requestFileURI); // explode() always returns an array

				$webRoot = WEBROOT . '/';
				$webRootLength = mb_strlen($webRoot);

				// Remove WEBROOT & Quick validity check
				// We remove the WEBROOT because we are already in it (/public/static.php)
				foreach ($this->m_files as &$f) {

					if (mb_substr($f, 0, $webRootLength) == $webRoot)
						$f = mb_substr($f, $webRootLength);

					if (!is_file($f))
						throw new Exception("File not found: $f", self::E_FILE_NOT_FOUND);
				}
				unset($f);

			// File type
			$this->m_fileType = pathinfo($this->m_files[0], PATHINFO_EXTENSION);
				$this->m_fileType = mb_strtolower($this->m_fileType);

			// Ggui ?
			if ($this->m_files[0] == FileRendererGgui::GGUI_IMPORT_FILE)
				$this->m_fileType = self::GGUI;

			if (!in_array($this->m_fileType, self::SUPPORTED_FILE_TYPES))
				throw new Exception("File type not supported: {$this->m_fileType}", self::E_FILE_TYPE_NOT_SUPPORTED);
		}

		/**
		 * @return string|array|null
		 */
		public function getFiles() {
			return $this->m_files;
		}

		/**
		 * Starts routing
		 */
		public function exec(): void {

			if ($this->m_fileType === null || $this->m_files === null)
				$this->fileNotFound();

			$renderer = null;

			switch ($this->m_fileType) {

				case self::CSS:
					$renderer = new FileRendererCSS($this);
					break;

				case self::JAVASCRIPT:
					$renderer = new FileRendererJS($this);
					break;

				case self::GGUI:
					$renderer = new FileRendererGgui($this);
					break;
			}

			if ($renderer === null)
				$this->fileNotFound();

			if ($this->m_linkedFilesMode == self::MERGED)
				$renderer->renderMerged();
			else
				$renderer->renderFlat();
		}

		/**
		 * Sends 404 and exit;s
		 */
		public function fileNotFound(): void {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
			exit;
		}
	}

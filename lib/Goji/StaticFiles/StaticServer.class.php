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
		private $m_fileType;
		private $m_files;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/templating.json5';

		const NORMAL = 'normal';
		const MERGED = 'merged';

		const FILE_CSS = 'css';
		const FILE_JS = 'js';

		const SUPPORTED_FILE_TYPES = [self::FILE_CSS, self::FILE_JS];

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

			// File type
			$this->m_fileType = null;

				if (!empty($_GET['type']))
					$_GET['type'] = mb_strtolower($_GET['type']);

				if (in_array($_GET['type'], self::SUPPORTED_FILE_TYPES)) // else $TYPE = null;
					$this->m_fileType = $_GET['type'];

			// File(s)
			$this->m_files = null;

				if (!empty($_GET['file'])) {

					if (mb_strpos($_GET['file'], '|') !== false) { // If there are several files given

						// css/main.css|css/responsive.css
						$_GET['file'] = explode('|', $_GET['file']);
						$this->m_files = [];

						foreach ($_GET['file'] as $f) {

							if (file_exists($f))
								$this->m_files[] = $f;
						}

						if (count($this->m_files) === 0)
							$this->m_files = null; // Tried to cheat lol

					} else { // Single file

						if (file_exists($_GET['file']))
							$this->m_files = $_GET['file'];
					}
				}
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

				case self::FILE_CSS:
					$renderer = new FileRendererCSS($this);
					break;

				case self::FILE_JS:
					$renderer = new FileRendererJS($this);
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
		private function fileNotFound(): void {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
			exit;
		}
	}

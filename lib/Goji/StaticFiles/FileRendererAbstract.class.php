<?php

namespace Goji\StaticFiles;

// use Goji\Core\HttpResponse;

/**
 * Class FileRendererAbstract
 *
 * @package Goji\StaticFiles
 */
abstract class FileRendererAbstract {

	/* <ATTRIBUTES> */

	protected $m_server;
	protected $m_files;

	public function __construct(StaticServer $server) {

		$this->m_server = $server;
		$this->m_files = $this->m_server->getFiles();

		// Done in .htaccess for all files
		// HttpResponse::setHttpCachingPolicy([]);
	}

	/**
	 * Render flat file, without any treatment
	 */
	public function renderFlat() {

		// Single file, read and exit
		if (!is_array($this->m_files)) {

			if (is_file($this->m_files))
				readfile($this->m_files);

			return;
		}

		// Multiple files, read them one by one
		foreach ($this->m_files as $f) {

			if (is_file($f)) {
				readfile($f);
				echo PHP_EOL;
			}
		}
	}

	abstract public function renderMerged();
}

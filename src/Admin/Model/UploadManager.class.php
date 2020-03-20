<?php

namespace Admin\Model;

use Goji\Core\App;

class UploadManager {

	private $m_app;

	public function __construct(App $app) {
		$this->m_app = $app;
	}

	/**
	 * @param int $offset
	 * @param int $count (-1 = all)
	 * @param string|null $type ex: image/* or image/png or *png
	 * @return array
	 * @throws \Exception
	 */
	public function getUploads(int $offset = 0, int $count = -1, string $type = null): array {

		$query = 'SELECT * FROM g_upload ';
		$queryParameters = [];

		if ($type !== null) {
			$type = str_replace('*', '%', $type); // SQL
			$query .= 'WHERE type LIKE :type ';
			$queryParameters['type'] = $type;
		}

		$query .= 'ORDER BY id DESC ';

		// LIMIT $count OFFSET $offset
		// OFFSET needs LIMIT, but LIMIT doesn't need OFFSET

		if ($count > -1) // LIMIT supplied
			$query .= "LIMIT $count OFFSET $offset ";

		$query = $this->m_app->db()->prepare($query);
		$query->execute($queryParameters);
		$reply = $query->fetchAll();
		$query->closeCursor();

		/*
		 * MySQL: CONCAT("thumb_", name) as thumb
		 * SQLite: ("thumb_" || name) as thumb
		 *
		 * So we do it in PHP to stay compatible without changing the code...
		 */
		foreach ($reply as &$entry) {
			$entry['thumb'] = 'thumb_' . $entry['name'];
		}
		unset($entry);

		return $reply;
	}
}

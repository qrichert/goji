<?php

namespace Admin\Model;

use Goji\Core\App;
use Goji\Toolkit\SaveImage;
use Goji\Toolkit\SwissKnife;

class UploadManager {

	private $m_app;
	private $m_db;

	public function __construct(App $app) {
		$this->m_app = $app;
		$this->m_db = $this->m_app->getDatabase();
	}

	/**
	 * @param array $formFile
	 * @return array
	 */
	public function saveUpload(array $formFile): array {

		$newImageSavePath = date('Y/m/');

		$newImageName = SaveImage::save($formFile, SaveImage::UPLOAD_DIRECTORY . '/' . $newImageSavePath);

		// Save thumb as well
		$thumbName = pathinfo($newImageName, PATHINFO_FILENAME);
		SaveImage::save($formFile, SaveImage::UPLOAD_DIRECTORY . '/' . $newImageSavePath, 'thumb_', $thumbName, true, 450);

		$fileType = SwissKnife::mime_content_type($formFile['tmp_name']);

		$newImageSavePath = 'upload/' . $newImageSavePath;

		$query = $this->m_db->prepare('INSERT INTO g_upload
											   ( path,  name,  type,  size,  uploaded_by,  upload_date)
										VALUES (:path, :name, :type, :size, :uploaded_by, :upload_date)');

		$query->execute([
			'path' => $newImageSavePath,
			'name' => $newImageName,
			'type' => $fileType,
			'size' => (int) $formFile['size'],
			'uploaded_by' => $this->m_app->getUser()->getId(),
			'upload_date' => date('Y-m-d H:i:s')
		]);

		$query->closeCursor();

		return [$newImageSavePath, $newImageName];
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

		$query = $this->m_db->prepare($query);
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

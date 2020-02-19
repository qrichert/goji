<?php

namespace Goji\Toolkit;

use Goji\Core\Database;

/**
 * Class BackUp
 *
 * @package Goji\Toolkit
 */
class BackUp {

	/* <CONSTANTS> */

	const BACKUP_PATH = ROOT_PATH . '/var/backup/';
	const BACKUP_FILE_EXTENSION = '.backup';

	const DATABASE_PREFIX = 'db__';

	/**
	 * Backup SQLite database file
	 *
	 * @param \Goji\Core\Database $db
	 * @param bool $addBackupDate
	 * @param bool $addFileMTime
	 * @return bool
	 */
	public static function database(Database $db, bool $addBackupDate = true, bool $addFileMTime = true): bool {

		$dbFile = $db->getDatabaseFile();

		if ($dbFile === null || !is_file($dbFile))
			return false;

		$fileName = basename($dbFile);

		if ($addBackupDate)
			$fileName .= '.' . date('Y-m-d');

		if ($addFileMTime)
			$fileName .= '.' . (string) filemtime($dbFile);

		$backupFile = self::BACKUP_PATH . self::DATABASE_PREFIX . $fileName . self::BACKUP_FILE_EXTENSION;

		if (!is_dir(self::BACKUP_PATH))
			mkdir(self::BACKUP_PATH, 0777, true);

		if (!copy($dbFile, $backupFile))
			return false;

		// Remove old backups
		self::removeOldBackupFiles(self::DATABASE_PREFIX);

		return true;
	}

	/**
	 * Removes oldest files (sorted by name) if number of files exceeds $maxCount
	 *
	 * @param string $prefix What prefix is concerned (ex: db__*)
	 * @param int $maxCount How many should be kept (optional, default = 15)
	 * @return bool
	 */
	public static function removeOldBackupFiles(string $prefix, int $maxCount = 15): bool {

		$pattern = self::BACKUP_PATH . $prefix . '*' . self::BACKUP_FILE_EXTENSION;

		$backupFiles = glob($pattern, GLOB_NOSORT); // GLOB_NOSORT to improve efficiency, will be sorted by rsort() later anyway
		// Get them sorted in natural order, newest first
		rsort($backupFiles, SORT_NATURAL);

		$count = count($backupFiles);

		$success = true;

		for ($i = 0; $i < $count; $i++) {
			// Remove file if we are over $maxCount
			if ($i >= $maxCount) {
				if (!unlink($backupFiles[$i]))
					$success = false;
			}
		}

		return $success;
	}
}

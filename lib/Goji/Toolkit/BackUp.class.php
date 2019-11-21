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
		const DATABASE_PREFIX = 'db__';

		public static function database(Database $db): bool {

			$dbFile = $db->getDatabaseFile();

			if ($dbFile === null || !is_file($dbFile))
				return false;

			$backupFile = self::BACKUP_PATH . self::DATABASE_PREFIX . basename($dbFile);

			if (!is_dir(self::BACKUP_PATH))
				mkdir(self::BACKUP_PATH, 0777, true);

			return copy($dbFile, $backupFile);
		}
	}

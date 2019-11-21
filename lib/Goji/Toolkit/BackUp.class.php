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

		public static function database(Database $db): bool {

			return true;
		}
	}

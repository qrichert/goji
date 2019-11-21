<?php

	namespace Goji\Toolkit;

	use Goji\Core\DataBase;

	/**
	 * Class BackUp
	 *
	 * @package Goji\Toolkit
	 */
	class BackUp {

		/* <CONSTANTS> */

		const BACKUP_PATH = ROOT_PATH . '/var/backup/';

		public static function dataBase(DataBase $db): bool {

			return true;
		}
	}

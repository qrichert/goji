<?php

	namespace Goji\Core;

	use PDO;
	use Exception;

	class DataBase extends PDO {

		private $m_databaseID;

		public function __construct() {

			// TODO: Use config file to list multiple DBs, select the first one that works. They all have an id
			$this->m_databaseID = 'production';

			$mysqlID = array(
				'host' => PASSWORD_DB_HOST,
				'port' => null,
				'dbname' => PASSWORD_DB_NAME,
				'unix_socket' => null,
				'charset' => 'utf8mb4'
			);

			$dsn = array();

			foreach ($mysqlID as $el => $val) {

				if (is_numeric($val))
					$val = strval($val);

				if (is_string($val))
					$dsn[] = $el . '=' . $val;
			}

			$dsn = 'mysql:' . implode(';', $dsn);

			$username = PASSWORD_DB_LOGIN;
			$password = PASSWORD_DB_PASSWORD;

			try { // Prod

				parent::__construct($dsn, $username, $password);

			} catch (Exception $e) { // Local

				$this->m_databaseID = 'local';

				$mysqlID = array(
					'host' => 'localhost',
					'port' => null,
					'dbname' => 'skalifactory',
					'unix_socket' => null,
					'charset' => 'utf8mb4'
				);

				$dsn = array();

				foreach ($mysqlID as $el => $val) {

					if (is_numeric($val))
						$val = strval($val);

					if (is_string($val))
						$dsn[] = $el . '=' . $val;
				}

				$dsn = 'mysql:' . implode(';', $dsn);

				$username = 'root';
				$password = 'root';

				parent::__construct($dsn, $username, $password);
			}
		}

		/**
		 * Returns ID as defined in configuration file.
		 *
		 * @return string
		 */
		public function getDataBaseID() {
			return $this->m_databaseID;
		}
	}

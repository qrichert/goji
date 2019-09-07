<?php

	namespace Goji\Core;

	use PDO;
	use Exception;

	/**
	 * Class DataBase
	 *
	 * @package Goji\Core
	 */
	class DataBase extends PDO {

		/* <ATTRIBUTES> */

		private $m_dataBaseID;

		/* <CONSTANTS> */

		const CONFIG_FILE = '../config/databases.json5';
		const DATABASES_SAVE_PATH = '../var/db/';

		/**
		 * DataBase constructor.
		 *
		 * Loads config file (/config/databases.json5) and creates new PDO from it.
		 *
		 * The first database appearing in the config and that works is selected.
		 * You could have a production, test and local one for example.
		 *
		 * To have multiple databases, do it like this (in config file):
		 *
		 * ```json
		 * {
		 *      "production": {
		 *          "prefix": "mysql",
		 *          "host": "hostname",
		 *          "dbname": "databasename",
		 *          "port": 3306,
		 *          "username": "username",
		 *          "password": "userpassword"
		 *      },
		 *      "localhost": {
		 *          "prefix": "mysql",
		 *          "host": "hostname",
		 *          "dbname": "databasename",
		 *          "username": "username",
		 *          "password": "userpassword"
		 *      }
		 * }
		 * ```
		 *
		 * The database identification name is entirely up to you (here we have 'production' and 'localhost').
		 * You'll be able to access the selected one via DataBase::getDataBaseID();
		 *
		 * Usable parameters are prefix, host, port, dbname, unix_socket, charset, username, password.
		 * If a parameter is missing or null it will be ignored.
		 *
		 * @param string $configFile (optional) default = DataBase::CONFIG_FILE
		 * @throws \Exception
		 */
		public function __construct($configFile = self::CONFIG_FILE) {

			$this->m_dataBaseID = null;

			$config = ConfigurationLoader::loadFileToArray($configFile);

			$connectionSuccessful = false;
			$lastException = null;

			// For each given database, extract config & try to connect
			foreach ($config as $dataBaseID => $databaseConfig) {

				$savedInLocalFile = false;
				$prefix = '';
				$dsn = [];
				$file = ''; // SQLite for example
				$username = '';
				$password = '';

				// Extract configuration
				foreach ($databaseConfig as $parameter => $value) {

					// null
					if (!isset($value))
						continue;

					// Look for prefix

					if ($parameter == 'prefix') {
						$prefix = strval($value);
						continue;
					}

					// Look for a filename

					if ($parameter == 'file') {

						if (!is_dir(self::DATABASES_SAVE_PATH))
							mkdir(self::DATABASES_SAVE_PATH, 0777, true);

						$file = self::DATABASES_SAVE_PATH . strval($value);

						$savedInLocalFile = true;

						continue;
					}

					// Look for username and password, not part of DSN

					if ($parameter == 'username') {
						$username = strval($value);
						continue;
					}

					if ($parameter == 'password') {
						$password = strval($value);
						continue;
					}

					// If not username or password, it is part of DSN

					// 3306 -> "3306"
					if (is_numeric($value))
						$value = strval($value);

					// "host" => "localhost" -> "host=localhost"
					if (is_string($value))
						$dsn[] = $parameter . '=' . $value;
				}

				// "host=localhost", "dbname=dbname" -> "host=localhost;dbname=dbname"
				$dsn = implode(';', $dsn);

				// "mysql" . ":" . "host=localhost;dbname=dbname" -> "mysql:host=localhost;dbname=dbname"
				if (!empty($prefix))
					$dsn = $prefix . ':' . $dsn;

				// Connect to database
				try {

					$options = [
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
					];

					// Call PDO::__construct
					if ($savedInLocalFile)
						parent::__construct($prefix . ':' . $file, null, null, $options);
					else
						parent::__construct($dsn, $username, $password, $options);

				} catch (Exception $e) {

					$lastException = $e;
					continue;
				}

				// We found the right one, now we save it and exit
				$connectionSuccessful = true;
				$this->m_dataBaseID = $dataBaseID;
				break;
			}

			// If every connection has failed, we want to see the error if possible
			if (!$connectionSuccessful && isset($lastException))
				throw $lastException;
		}

		/**
		 * Returns ID as defined in configuration file.
		 *
		 * @return string
		 */
		public function getDataBaseID() {
			return $this->m_dataBaseID;
		}

		/**
		 * Shows errors in console
		 *
		 * @param bool $logErrors
		 */
		public function logErrors(bool $logErrors): void {

			if ($logErrors)
				$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			else
				$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		}
	}

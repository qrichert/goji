<?php

namespace Goji\Core;

use PDO;
use Exception;

/**
 * Class Database
 *
 * @package Goji\Core
 */
class Database extends PDO {

	/* <ATTRIBUTES> */

	private $m_config;
	private $m_databaseId;
	private $m_databaseFile;

	/* <CONSTANTS> */

	const CONFIG_FILE = ROOT_PATH . '/config/databases.json5';
	const DATABASES_SAVE_PATH = ROOT_PATH . '/var/db/';

	const E_DATABASE_NOT_CONFIGURED = 1;

	/**
	 * Database constructor.
	 *
	 * Loads config file (/config/databases.json5) and creates new PDO from it.
	 *
	 * If you provide an ID for the constructor, the corresponding parameters will be used.
	 *
	 * If no ID is given, the first database appearing in the config and that works is selected.
	 * You could have a production, test and local one for example.
	 *
	 * Usually you would have the production first and the local second. So if you're on your
	 * production server, the production DB will be loaded. And if you're on the local server,
	 * the production won't load and will fail, and the local one will be selected automatically.
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
	 *      "development": {
	 *          "prefix": "mysql",
	 *          "host": "hostname",
	 *          "dbname": "databasename",
	 *          "username": "username",
	 *          "password": "userpassword"
	 *      }
	 * }
	 * ```
	 *
	 * The database identification name is entirely up to you (here we have 'production' and 'development').
	 * You'll be able to access the selected one via Database::getDatabaseID();
	 *
	 * Usable parameters are prefix, host, port, dbname, unix_socket, charset, username, password, file.
	 * If a parameter is missing or null it will be ignored.
	 *
	 * @param string|null $databaseId
	 * @param string $configFile (optional) default = Database::CONFIG_FILE
	 * @throws \Exception
	 */
	public function __construct(string $databaseId = null, string $configFile = self::CONFIG_FILE) {

		$this->m_config = ConfigurationLoader::loadFileToArray($configFile);
		$this->m_databaseId = null; // Will be set on loading success
		$this->m_databaseFile = null; // We be set if database is in a file (like SQLite)

		if (!empty($databaseId))
			$this->connectToDatabaseFromId($databaseId);
		else
			$this->connectToFirstWorkingDatabase();
	}

	/**
	 * Connect to the given database
	 *
	 * @param string $databaseId
	 * @throws \Exception
	 */
	private function connectToDatabaseFromId(string $databaseId): void {

		$databaseConfig = $this->m_config[$databaseId] ?? null;

		if ($databaseConfig === null)
			throw new Exception("Database not configured: '$databaseId'.", self::E_DATABASE_NOT_CONFIGURED);

		// Extracting infos
		$savedInLocalFile = false;
		$prefix = '';
		$dsn = [];
		$file = ''; // SQLite for example
		$username = '';
		$password = '';

		$exception = null;

		// Extract configuration
		foreach ($databaseConfig as $parameter => $value) {

			// null
			if (!isset($value))
				continue;

			// Look for prefix
			if ($parameter == 'prefix') {
				$prefix = (string) $value;
				continue;
			}

			// Look for a filename
			if ($parameter == 'file') {

				if (!is_dir(self::DATABASES_SAVE_PATH))
					mkdir(self::DATABASES_SAVE_PATH, 0777, true);

				$file = self::DATABASES_SAVE_PATH . (string) $value;

				$savedInLocalFile = true;

				continue;
			}

			// Look for username and password, not part of DSN
			if ($parameter == 'username') {
				$username = (string) $value;
				continue;
			}

			if ($parameter == 'password') {
				$password = (string) $value;
				continue;
			}

			// If not username or password, it is part of DSN

			// 3306 -> "3306" (string)
			// "host" => "localhost" -> "host=localhost"
			$dsn[] = (string) $parameter . '=' . (string) $value;
		}

		// "host=localhost", "dbname=dbname" -> "host=localhost;dbname=dbname"
		$dsn = implode(';', $dsn);

		// "mysql" . ":" . "host=localhost;dbname=dbname" -> "mysql:host=localhost;dbname=dbname"
		if (!empty($prefix))
			$dsn = $prefix . ':' . $dsn;

		// Connect to database
		$options = [
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		];

		// Call PDO::__construct
		if ($savedInLocalFile) {

			parent::__construct($prefix . ':' . $file, null, null, $options);

			// If we're here, it worked
			// So save file path
			$this->m_databaseFile = $file;

		} else {

			parent::__construct($dsn, $username, $password, $options);
		}

		// Connection worked, update id
		$this->m_databaseId = $databaseId;
	}

	/**
	 * Connect to the first working database
	 *
	 * @throws \Exception
	 */
	private function connectToFirstWorkingDatabase(): void {

		$connectionSuccessful = false;
		$lastException = null;

		// For each given database, try to connect
		foreach ($this->m_config as $databaseId => $_) {

			try {

				$this->connectToDatabaseFromId($databaseId);

			} catch (Exception $e) {

				$lastException = $e;
				continue;
			}

			// We found the right one, now we save it and exit
			$connectionSuccessful = true;
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
	public function getDatabaseID() {
		return $this->m_databaseId;
	}

	/**
	 * Returns database file.
	 *
	 * Only returns !=null if SQLite or something, not with DB server
	 *
	 * @return string|null
	 */
	public function getDatabaseFile(): ?string {
		return $this->m_databaseFile;
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

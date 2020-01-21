<?php

namespace Goji\Blueprints;

use Exception;
use Goji\Core\App;

/**
 * Class ModelObjectAbstract
 *
 * This is a auto-hydrated model base. It automatically creates an object that reflects
 * a row from a database table (according to the database table structure)
 *
 * TODO: auto-sanitize values according to table column types
 *
 * Takes a row from the database and automatically creates getters and setters:
 * id, last_name -> getId() / setId(), getLastName() / setLastName()
 *
 * Use writeLock() / writeUnlock() to lock colmuns that should not be modified (like id)
 *
 * You must call ModelObjectAbstract::save() to 'push' changes to the databse.
 * If you don't want to, you can set $m_autoSave to true (in which case it will update automatically after every 'set*()')
 *
 * Inherit from this class like (for example):
 *
 * class Member extends ModelObjectAbstract {
 *
 *     public function __construct(App $app, $id) {
 *
 *         //                                   = WHERE id=3
 *         parent::__construct($app, 'g_member', 'id=' . (string) $id);
 *
 *         $this->m_autoSave = true; // If you want that
 *
 *         $this->writeLock('date_registered');
 *     }
 * }
 *
 * new Member($this->m_app, 3); // Selects member with id 3
 *
 * @package Goji\Blueprints
 */
abstract class ModelObjectAbstract {

	/* <ATTRIBUTES> */

	protected $m_app;
	protected $m_db;
	protected $m_autoSave;
	protected $m_tableName;
	protected $m_whereCondition;
	protected $m_data; // FormattedColumnName (for getColumn/setColumn) [ original column name, value ]

	/* <CONSTANTS> */

	const E_OBJECT_NOT_FOUND_IN_DATABASE = 0;
	const E_COLUMN_IS_WRITE_LOCKED = 1;

	/**
	 * ModelAbstract constructor.
	 *
	 * SELECT * FROM $table WHERE referenceColumn=referenceValue
	 *
	 * @param \Goji\Core\App $app
	 * @param string $tableName Name of the database table
	 * @param string $whereCondition WHERE condition (without WHERE keyword) /!\ Be careful with SQL injections here !!!, this will be used as is
	 * @throws \Exception
	 */
	public function __construct(App $app, string $tableName, string $whereCondition) {

		$this->m_app = $app;
		$this->m_db = $app->db();
		$this->m_autoSave = false; // If true, save after every change (call to set*())
		$this->m_tableName = $tableName;
		$this->m_whereCondition = $whereCondition;

		$query = $this->m_db->query("SELECT * FROM {$this->m_tableName} WHERE {$this->m_whereCondition}");
		$reply = $query->fetch();
		$query->closeCursor();

		if ($reply === false)
			throw new Exception("Object not found in table {$this->m_tableName} WHERE {$this->m_whereCondition}", self::E_OBJECT_NOT_FOUND_IN_DATABASE);

		$this->m_data = [];

		foreach ($reply as $columnName => $value) {

			// say__hello--world -> SayHelloWorld
			$formattedColumnName = preg_replace('#[-_]#i', '', ucwords($columnName, '-_'));

			$this->m_data[$formattedColumnName] = [
				'column' => $columnName,
				'value' => $value,
				'locked' => false,
				'modified' => false
			];
		}

		$this->writeLock('id');
	}

	/**
	 * Prevent columns from being modified (like database ID)
	 *
	 * @param string|array $columnsToAffect (unformatted) column names to write lock
	 * @param bool $lock
	 */
	protected function writeLock($columnsToAffect, bool $lock = true): void {

		$columnsToAffect = (array) $columnsToAffect;

		foreach ($this->m_data as $_ => &$column) {

			if (in_array($column['column'], $columnsToAffect))
				$column['locked'] = $lock;
		}
		unset($column);
	}

	protected function writeUnlock($columnsToAffect): void {
		$this->writeLock($columnsToAffect, false);
	}

	public function __call($name, $arguments = null) {

		$columnName = mb_substr($name, 3);
		$method = mb_substr($name, 0, 3); // get || set

		if ($method == 'get')
			return $this->get($columnName);

		elseif ($method == 'set')
			return $this->set($columnName, $arguments[0] ?? null);

		trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
	}

	/**
	 * @param string $name Column name
	 * @return bool
	 */
	protected function get(string $name) {

		// Can't be empty if column exists, always contains associative array with original name and value field
		if (empty($this->m_data[$name]))
			trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);

		return $this->m_data[$name]['value'] ?? null;
	}

	/**
	 * @param string $name
	 * @param null $arguments
	 * @throws \Exception
	 */
	protected function set(string $name, $arguments = null) {

		// Can't be empty if column exists, always contains associative array with original name and value field
		if (empty($this->m_data[$name]))
			trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);

		if ($this->m_data[$name]['locked'])
			throw new Exception("Column '{$this->m_data[$name]['column']}' is write-locked.", self::E_COLUMN_IS_WRITE_LOCKED);


		$this->m_data[$name]['value'] = $arguments;
		$this->m_data[$name]['modified'] = true;

		if ($this->m_autoSave)
			$this->saveColumn($name);
	}

	/**
	 * @param $name
	 * @throws \Exception
	 */
	protected function saveColumn($name): void {

		// Can't be empty if column exists, always contains associative array with original name and value field
		if (empty($this->m_data[$name]))
			trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);

		if ($this->m_data[$name]['locked'])
			throw new Exception("Column '{$this->m_data[$name]['column']}' is write-locked.", self::E_COLUMN_IS_WRITE_LOCKED);


		$query = $this->m_db->prepare("UPDATE {$this->m_tableName}
										SET {$this->m_data[$name]['column']}=:new_value
										WHERE {$this->m_whereCondition}");

		$query->execute([
			'new_value' => $this->m_data[$name]['value']
		]);

		$query->closeCursor();

		$this->m_data[$name]['modified'] = false;
	}

	/**
	 * Saves state into DB (updates DB)
	 *
	 * This saves only the fields that were modified through set*().
	 */
	public function save(): void {

		$columnsToSave = [];
		$dataToSave = [];
		$inputParameters = [];

		foreach ($this->m_data as $columnName => $column) {

			if ($column['locked'])
				continue;

			// We update only those that have been modified
			if (!$column['modified'])
				continue;

			$columnsToSave[] = $columnName;
			$dataToSave[] = $column['column'] . '=:' . $column['column'] . '_new_value';
			$inputParameters[$column['column'] . '_new_value'] = $column['value'];
		}

		$dataToSave = implode(', ', $dataToSave);


		$query = $this->m_db->prepare("UPDATE {$this->m_tableName}
										SET $dataToSave
										WHERE {$this->m_whereCondition}");

		$query->execute($inputParameters);

		$query->closeCursor();


		// Make them as not modified (since they are now equal to the values in the database)
		foreach ($columnsToSave as $column) {
			$this->m_data[$column]['modified'] = false;
		}
	}
}

<?php

namespace Ubiquity\db\providers;

/**
 * Ubiquity\db\providers$AbstractDbWrapper
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class AbstractDbWrapper {
	protected $dbInstance;

	abstract public function query(string $sql);

	abstract public function queryAll(string $sql, int $fetchStyle = null);

	abstract public function queryColumn(string $sql, int $columnNumber = null);

	abstract public static function getAvailableDrivers();

	abstract public function getStatement(string $sql);

	abstract public function connect(string $dbType, $dbName, $serverName, string $port, string $user, string $password, array $options);

	abstract public function getDSN(string $serverName, string $port, string $dbName, string $dbType = 'mysql');

	abstract public function execute(string $sql);

	abstract public function prepareStatement(string $sql);

	abstract public function executeStatement($statement, array $values = null);

	abstract public function statementRowCount($statement);

	abstract public function lastInsertId();

	abstract public function bindValueFromStatement($statement, $parameter, $value);

	abstract public function fetchColumn($statement, array $values = null, int $columnNumber = null);

	abstract public function fetchAll($statement, array $values = null, $mode = null);

	abstract public function fetchOne($statement, array $values = null, $mode = null);

	abstract public function fetchAllColumn($statement, array $values = null, string $column = null);

	abstract public function getTablesName();

	abstract public function beginTransaction();

	abstract public function commit();

	abstract public function inTransaction();

	abstract public function rollBack();

	abstract public function nestable();

	abstract public function savePoint($level);

	abstract public function releasePoint($level);

	abstract public function rollbackPoint($level);

	abstract public function ping();

	abstract public function getPrimaryKeys($tableName);

	abstract public function getFieldsInfos($tableName);

	public function close() {
		$this->dbInstance = null;
	}

	/**
	 *
	 * @return object
	 */
	public function getDbInstance() {
		return $this->dbInstance;
	}

	/**
	 *
	 * @param object $dbInstance
	 */
	public function setDbInstance($dbInstance) {
		$this->dbInstance = $dbInstance;
	}
}
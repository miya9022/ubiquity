<?php

namespace Ubiquity\orm\traits;

use Ubiquity\orm\core\prepared\DAOPreparedQueryOne;
use Ubiquity\orm\core\prepared\DAOPreparedQueryById;
use Ubiquity\orm\core\prepared\DAOPreparedQueryAll;
use Ubiquity\db\Database;

/**
 * Ubiquity\orm\traits$DAOPreparedTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
trait DAOPreparedTrait {
	protected static $preparedDAOQueries = [ ];

	public static function prepareGetById($name, $className, $included = false, ?Database $db) {
		return self::$preparedDAOQueries [$name] = new DAOPreparedQueryById ( $className, $included, $db );
	}

	public static function prepareGetOne($name, $className, $condition = '', $included = false, ?Database $db) {
		return self::$preparedDAOQueries [$name] = new DAOPreparedQueryOne ( $className, $condition, $included, $db );
	}

	public static function prepareGetAll($name, $className, $condition = '', $included = false, ?Database $db) {
		return self::$preparedDAOQueries [$name] = new DAOPreparedQueryAll ( $className, $condition, $included, $db );
	}

	public static function executePrepared($name, $params = [ ], $useCache = false, $dbInstance = null) {
		if (isset ( self::$preparedDAOQueries [$name] )) {
			return self::$preparedDAOQueries [$name]->execute ( $params, $useCache, $dbInstance );
		}
		return null;
	}
}


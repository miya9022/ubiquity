<?php

namespace Ubiquity\orm\traits;

use Ubiquity\controllers\Startup;
use Ubiquity\exceptions\DAOException;
use Ubiquity\db\Database;

/**
 * Ubiquity\orm\traits$DAOPooling
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 * @property array $db
 *
 */
trait DAOPooling {

	abstract public static function startDatabase(&$config, $offset = null);

	abstract public static function getDbOffset(&$config, $offset = null);

	/**
	 * Create database pooling (To invoke during Swoole startup)
	 *
	 * @param array $config
	 * @param ?string $offset
	 * @param int $size
	 */
	public static function createDbPool(&$config, $offset = null, int $size = 16) {
		$dbConfig = self::getDbOffset ( $config, $offset );
		$wrapperClass = $dbConfig ['wrapper'] ?? \Ubiquity\db\providers\pdo\PDOWrapper::class;
		if (\method_exists ( $wrapperClass, 'getPoolClass' )) {
			$poolClass = \call_user_func ( $wrapperClass . '::getPoolClass' );
			if (\class_exists ( $poolClass, true )) {
				$reflection_class = new \ReflectionClass ( $poolClass );
				$pool = $reflection_class->newInstanceArgs ( [ &$config,$offset,$size ] );
				$db = self::startDatabase ( $config, $offset );
				$db->setPool ( $pool );
				return $db;
			} else {
				throw new DAOException ( $poolClass . ' class does not exists!' );
			}
		} else {
			throw new DAOException ( $wrapperClass . ' does not support connection pooling!' );
		}
	}

	public static function initPool(Database $db) {
		$db->initPool ();
	}

	/**
	 * gets a new DbConnection from pool
	 *
	 * @param Database $db
	 * @return mixed
	 */
	public static function pool(Database $db) {
		return $db->pool ();
	}

	public static function freePool(Database $db, $dbInstance) {
		$db->freePool ( $dbInstance );
	}
}


<?php

namespace Ubiquity\orm\core\prepared;

use Ubiquity\db\Database;

/**
 * Ubiquity\orm\core\prepared$DAOPreparedQueryOne
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class DAOPreparedQueryOne extends DAOPreparedQueryById {

	public function __construct($className, $condition = '', $included = false, ?Database $db = null) {
		DAOPreparedQuery::__construct ( $className, $condition, $included, $db );
	}

	protected function prepare() {
		$this->conditionParser->limitOne ();
		DAOPreparedQuery::prepare ();
	}
}


<?php
/**
 * Base migration class used to perform migrations from version to version
 * @author Mark Hobson
 */
abstract class Migration extends MojaviObject {
	
	const DEBUG = true;
	
	private $exceptions;
	private $warnings;
	private static $databaseManager = null;
	
	/**
	 * Pushes a version up one version
	 * @return boolean
	 */
	abstract function up();
	
	/**
	 * Pushes a version down one version
	 * @return boolean
	 */
	abstract function down();
	
	/**
	 * Returns the exceptions as a string
	 * @return array
	 */
	function getExceptionsAsString() {
		$ret_val = array();
		/* @var $exception Exception */
		foreach ($this->getExceptions() as $exception) {
			$trace = $exception->getTrace();
			array_shift($trace);
			$source_file = array_shift($trace);
			$ret_val[] = $exception->getMessage() . ' in file ' . $source_file['file'] . ' on line ' . $source_file['line'];
		}
		return implode("\n", $ret_val);
	}
	
	/**
	 * Returns the warnings as a string
	 * @return array
	 */
	function getWarningsAsString() {
		$ret_val = array();
		/* @var $exception Exception */
		foreach ($this->getWarnings() as $exception) {
			$trace = $exception->getTrace();
			// unshift the first file
			array_shift($trace);
			$source_file = array_shift($trace);
			$ret_val[] = $exception->getMessage() . ' in file ' . $source_file['file'] . ' on line ' . $source_file['line'];
		}
		return implode("\n", $ret_val);
	}
	
	/**
	 * Returns the warnings
	 * @return array
	 */
	function getWarnings() {
		if (is_null($this->warnings)) {
			$this->warnings = array();
		}
		return $this->warnings;
	}
	/**
	 * Sets the warnings
	 * @param array
	 */
	function setWarnings($arg0) {
		$this->warnings = $arg0;
		return $this;
	}
	
	/**
	 * Sets the warnings
	 * @param array
	 */
	function addWarning($arg0) {
		$tmp_array = $this->getWarnings();
		$tmp_array[] = $arg0;
		$this->setWarnings($tmp_array);
		return $this;
	}
	
	/**
	 * Returns the exceptions
	 * @return array
	 */
	function getExceptions() {
		if (is_null($this->exceptions)) {
			$this->exceptions = array();
		}
		return $this->exceptions;
	}
	/**
	 * Sets the exceptions
	 * @param array
	 */
	function setExceptions($arg0) {
		$this->exceptions = $arg0;
		return $this;
	}
	/**
	 * Sets the exceptions
	 * @param array
	 */
	function addException($arg0) {
		if (strpos($arg0->getMessage(), 'Duplicate column name') === 0) {
			return $this->addWarning($arg0);	
		}
		$tmp_array = $this->getExceptions();
		$tmp_array[] = $arg0;
		$this->setExceptions($tmp_array);
		return $this;
	}
	
	/**
	 * Returns the default connection name
	 * @return string
	 */
	function getDefaultConnectionName() {
		return 'default';	
	}
	
	/**
	 * Adds a new column to a table
	 * @param $db - db name
	 * @param $table - table name
	 * @param $column - column name
	 * @param $column_definition - column definition
	 */
	function addColumn($db, $table, $column, $col_definition, $connection_name = null) {
		try {
			if (is_null($connection_name)) {
				$connection_name = $this->getDefaultConnectionName();
			}
			
			$qry = '
				ALTER TABLE <<db>>.<<table>> ADD COLUMN <<column>> <<column_definition>>
			';
			$ps = new KeyBasedPreparedStatement($qry);
			$ps->setBareString('db', $db);
			$ps->setBareString('table', $table);
			$ps->setBareString('column', $column);
			$ps->setBareString('column_definition', $col_definition);
			
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
			{
				$retVal = mysql_affected_rows($con);
			}
			return $retVal;	
		} catch (Exception $e) {
			$this->addException($e);	
		}
		return false;
	}
	
	/**
	 * Checks if a column exists in a table
	 * @param $db - db name
	 * @param $table - table name
	 * @param $column - column name
	 */
	function keyExists($db, $table, $key_name, $connection_name = null) {
		$table_definition = $this->getTableDefinition($db, $table);
		foreach ($table_definition['keys'] as $key) {
			if (strtolower($key['Key_name']) == strtolower($key_name)) {
				return true;	
			}
		}
		return false;
	}
	
	/**
	 * Checks if a column exists in a table
	 * @param $db - db name
	 * @param $table - table name
	 * @param $column - column name
	 */
	function columnExists($db, $table, $column_name, $connection_name = null) {
		$table_definition = $this->getTableDefinition($db, $table);
		foreach ($table_definition['columns'] as $column) {
			if (strtolower($column['Field']) == strtolower($column_name)) {
				return true;	
			}
		}
		return false;
	}
	
	/**
	 * Gets the table definition for a table
	 * @param $db - db name
	 * @param $table - table name
	 */
	function getTableDefinition($db, $table, $connection_name = null) {
		$table_definition = array('database' => $db,
								  'table' => $table,
								  'columns' => array(),
								  'keys' => array());
		try {
			if (is_null($connection_name)) {
				$connection_name = $this->getDefaultConnectionName();
			}
			
			$qry = '
				SHOW FULL COLUMNS FROM <<db>>.<<table>>
			';
			$ps = new KeyBasedPreparedStatement($qry);
			$ps->setBareString('db', $db);
			$ps->setBareString('table', $table);
						
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if (($rs = $this->executeQuery($ps, $connection_name, $con, self::DEBUG)))
			{
				while (($row = mysql_fetch_array($rs, MYSQL_ASSOC)))
				{
					$table_definition['columns'][] = $row;
				}
			}
			
			$qry = '
				SHOW KEYS FROM <<db>>.<<table>>
			';
			$ps = new KeyBasedPreparedStatement($qry);
			$ps->setBareString('db', $db);
			$ps->setBareString('table', $table);
						
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if (($rs = $this->executeQuery($ps, $connection_name, $con, self::DEBUG)))
			{
				while (($row = mysql_fetch_array($rs, MYSQL_ASSOC)))
				{
					$table_definition['keys'][] = $row;
				}
			}
			return $table_definition;	
		} catch (Exception $e) {
			$this->addException($e);	
		}
		return $table_definition;
	}
	
	/**
	 * Adds a new table
	 * @param $db - table name
	 * @param $table - table name
	 * @param $column_definition - column definition
	 */
	function addTable($db, $table, $connection_name = null) {
		try {
			if (is_null($connection_name)) {
				$connection_name = $this->getDefaultConnectionName();
			}
			
			$qry = '
				CREATE TABLE IF NOT EXISTS <<db>>.<<table>>
			';
			$ps = new KeyBasedPreparedStatement($qry);
			$ps->setBareString('db', $db);
			$ps->setBareString('table', $table);
			
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
			{
				$retVal = mysql_affected_rows($con);
			}
			return $retVal;	
		} catch (Exception $e) {
			$this->addException($e);	
		}
		return false;
	}
	
	/**
	 * Runs raw sql
	 * @param $table_definition - table definition
	 * @param $connection_name - connection name
	 */
	function runSql($table_definition, $connection_name = null) {
		try {
			if (is_null($connection_name)) {
				$connection_name = $this->getDefaultConnectionName();
			}
			
			$qry = $table_definition;
			$ps = new KeyBasedPreparedStatement($qry);
			
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
			{
				$retVal = mysql_affected_rows($con);
			}
			return $retVal;
		} catch (Exception $e) {
			$this->addException($e);	
		}
		return false;
	}
	
	/**
	 * Drops an existing table
	 * @param $db - table name
	 * @param $table - table name
	 */
	function dropTable($db, $table, $connection_name = null) {
		try {
			if (is_null($connection_name)) {
				$connection_name = $this->getDefaultConnectionName();
			}
			
			$qry = '
				DROP TABLE IF EXISTS <<db>>.<<table>>
			';
			$ps = new KeyBasedPreparedStatement($qry);
			$ps->setBareString('db', $db);
			$ps->setBareString('table', $table);
			
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
			{
				$retVal = mysql_affected_rows($con);
			}
			return $retVal;
		} catch (Exception $e) {
			$this->addException($e);	
		}
		return false;
	}
	
	/**
	 * Drops an existing column in a table
	 * @param $db - table name
	 * @param $table - table name
	 * @param $column - column name
	 */
	function dropColumn($db, $table, $column, $connection_name = null) {
		try {
			if (is_null($connection_name)) {
				$connection_name = $this->getDefaultConnectionName();
			}
			
			$qry = '
				DROP TABLE <<db>>.<<table>> DROP COLUMN <<column>>
			';
			$ps = new KeyBasedPreparedStatement($qry);
			$ps->setBareString('db', $db);
			$ps->setBareString('table', $table);
			$ps->setBareString('column', $column);
			
			// Execute Query
		 	$retVal = false;
			$con = $this->getDatabaseConnection($connection_name);
			if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
			{
				$retVal = mysql_affected_rows($con);
			}
			return $retVal;
		} catch (Exception $e) {
			$this->addException($e);	
		}
		return false;
	}
	
	/**
	 * Returns the databaseManager
	 * @return DatabaseManager
	 */
	function getDatabaseManager() {
		if (is_null(self::$databaseManager)) {
			self::$databaseManager = new DatabaseManager();
			self::$databaseManager->initialize();
		}
		return self::$databaseManager;
	}
	/**
	 * Sets the databaseManager
	 * @param DatabaseManager
	 */
	function setDatabaseManager($arg0) {
		self::$databaseManager = $arg0;
		return $this;
	}
	
	/**
	 * Returns a database connection
	 * @param string $connection_name
	 * @return resource
	 */
	function getDatabaseConnection($connection_name) {
		try {
			return $this->getDatabaseManager()->getDatabase($connection_name)->getConnection();
		} catch (Exception $e) {
			LoggerManager::fatal($e->printStackTrace (''));
		}
	}
	
	/**
	 * Executes a query
	 * @param PreparedStatement $ps
	 * @param string $connection_name,
	 * @param resource $con
	 * @param boolean $debug
	 * @return mixed
	 */
	function executeQuery($ps, $connection_name = 'default', $con = null, $debug = true) {
		$retval = false;
		// Connect to database
		if (is_null($con)) {
			if (self::DEBUG) { LoggerManager::debug(__METHOD__  . ":: Retrieving New DB Connection for '" . $connection_name . "'..."); }
			$con = $this->getDatabaseConnection($connection_name);
		}
		$ps->setConnection($con);

		// Get the prepared query
		$query = $ps->getPreparedStatement();

		if ($debug) {
			LoggerManager::debug(__METHOD__ . " -- " . $query);
		}
		// Execute the query
		$rs = mysql_query($query, $con);

		if (!$rs) { 
			throw new Exception(mysql_error ($con));
		} else {
			$retval = $rs;
		}
		return $retval;
	}
}
?>
<?php
/**
 * Base migration class used to perform migrations from version to version
 * @author Mark Hobson
 */
abstract class Migration extends MojaviObject {
	
	const DEBUG = true;
	
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
		if (is_null($connection_name)) {
			$connection_name = $this->getDefaultConnectionName();
		}
		
		$qry = '
			ALTER TABLE <<db>>.<<table>> ADD COLUMN <<column>> <<column_definition>>
		';
		$ps = new PreparedStatement($qry);
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
	}
	
	/**
	 * Adds a new table
	 * @param $db - table name
	 * @param $table - table name
	 * @param $column_definition - column definition
	 */
	function addTable($db, $table, $connection_name = null) {
		if (is_null($connection_name)) {
			$connection_name = $this->getDefaultConnectionName();
		}
		
		$qry = '
			CREATE TABLE IF NOT EXISTS <<db>>.<<table>>
		';
		$ps = new PreparedStatement($qry);
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
	}
	
	/**
	 * Runs raw sql
	 * @param $table_definition - table definition
	 * @param $connection_name - connection name
	 */
	function runSql($table_definition, $connection_name = null) {
		if (is_null($connection_name)) {
			$connection_name = $this->getDefaultConnectionName();
		}
		
		$qry = $table_definition;
		$ps = new PreparedStatement($qry);
		
		// Execute Query
	 	$retVal = false;
		$con = $this->getDatabaseConnection($connection_name);
		if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
		{
			$retVal = mysql_affected_rows($con);
		}
		return $retVal;
	}
	
	/**
	 * Drops an existing table
	 * @param $db - table name
	 * @param $table - table name
	 */
	function dropTable($db, $table, $connection_name = null) {
		if (is_null($connection_name)) {
			$connection_name = $this->getDefaultConnectionName();
		}
		
		$qry = '
			DROP TABLE IF EXISTS <<db>>.<<table>>
		';
		$ps = new PreparedStatement($qry);
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
	}
	
	/**
	 * Drops an existing column in a table
	 * @param $db - table name
	 * @param $table - table name
	 * @param $column - column name
	 */
	function dropColumn($db, $table, $column, $connection_name = null) {
		if (is_null($connection_name)) {
			$connection_name = $this->getDefaultConnectionName();
		}
		
		$qry = '
			DROP TABLE <<db>>.<<table>> DROP COLUMN <<column>>
		';
		$ps = new PreparedStatement($qry);
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
<?php
/**
 * Base migration class used to perform migrations from version to version
 * @author Mark Hobson
 */
abstract class Migration extends MojaviObject {
	
	const DEBUG = true;
	
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
		$con = $this->getContext()->getDatabaseConnection($connection_name);
		if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
		{
			$retVal = mysql_rows_affected($con);
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
		$con = $this->getContext()->getDatabaseConnection($connection_name);
		if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
		{
			$retVal = mysql_rows_affected($con);
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
		$con = $this->getContext()->getDatabaseConnection($connection_name);
		if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
		{
			$retVal = mysql_rows_affected($con);
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
		$con = $this->getContext()->getDatabaseConnection($connection_name);
		if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
		{
			$retVal = mysql_rows_affected($con);
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
		$con = $this->getContext()->getDatabaseConnection($connection_name);
		if ($this->executeQuery($ps, $connection_name, $con, self::DEBUG))
		{
			$retVal = mysql_rows_affected($con);
		}
		return $retVal;
	}
}
?>
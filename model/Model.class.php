<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003, 2004 Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.mojavi.org.                             |
// +---------------------------------------------------------------------------+

/**
 * Model provides a convention for separating business logic from application
 * logic. When using a model you're providing a globally accessible API for
 * other modules to access, which will boost interoperability among modules in
 * your web application.
 *
 * @package    mojavi
 * @subpackage model
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     3.0.0
 * @version   $Id: Model.class.php 449 2004-11-24 17:57:22Z seank $
 */
abstract class Model extends PdoModel
{
	const DEBUG = MO_DEBUG;
	const CRITERIA_RETVAL_TYPE_ITERATOR	= 1;
	const CRITERIA_RETVAL_TYPE_FORM		= 2;

    // +-----------------------------------------------------------------------+
    // | PRIVATE VARIABLES                                                     |
    // +-----------------------------------------------------------------------+

    // +-----------------------------------------------------------------------+
    // | METHODS                                                               |
    // +-----------------------------------------------------------------------+

	// -------------------------------------------------------------------------

	/**
	 * Execute an SQL Statement and return result to be handled by calling function.
	 *
	 * @param	mixed PreparedStatement or KeyBasedPreparedStatement
	 * @param	string Name of connection to be used
	 * @param	resource $connection Connection resource handler
	 * @return	mixed Resource if query executed successfully, otherwise false
	 *
	 * @author	Mark Hobson
	 */
	public function executeQuery (PreparedStatement $ps, $name = 'default', $con = NULL, $debug = self::DEBUG)
	{
		if ($ps instanceof PdoPreparedStatement) {
			return parent::executeQuery($ps, $name, $con, $debug);	
		}
		
		$retval = false;
		try {

			// Connect to database
			if (is_null($con)) {
				if (self::DEBUG) { LoggerManager::debug(__METHOD__  . ":: Retrieving New DB Connection for '" . $name . "'..."); }
				$con = $this->getContext()->getDatabaseConnection($name);
			}
			
			if (function_exists('mysql_ping')) {
				if (!mysql_ping($con)) {
					$this->getContext()->getDatabaseManager()->getDatabase($name)->shutdown();
					$con = $this->getContext()->getDatabaseConnection($name);	
				}
			} else {
				throw new Exception('Missing php-mysql libraries on this server');	
			}

			// Get the prepared query
			$query = $ps->getPreparedStatement($con);

			if ($debug) {
				LoggerManager::error(__METHOD__ . " :: " . $ps->getDebugQueryString());
			}
			// Execute the query
			$rs = mysql_query ($query, $con);

			if (!$rs) { 
				throw new Exception (mysql_error ($con));
			} else {
				$retval = $rs;
			}

		} catch (MojaviException $e) {

			$this->getErrors ()->addError ('error', new Error ($e->getMessage ()));
			LoggerManager::fatal ($e->printStackTrace (''));

		} catch (PDOException $e) {
			$e = new MojaviException ($e->getMessage());
			LoggerManager::fatal ($e->printStackTrace(''));
			throw $e;
		} catch (Exception $e) {
			$this->getErrors ()->addError ('error', new Error ($e->getMessage ()));
			$e = new MojaviException ($e->getMessage());
			LoggerManager::fatal ($e->printStackTrace (''));

		}
		return $retval;
	}
	
	/**
	 * Execute an SQL Statement and return result to be handled by calling function.
	 *
	 * @param	mixed PreparedStatement or KeyBasedPreparedStatement
	 * @param	string Name of connection to be used
	 * @param	resource $connection Connection resource handler
	 * @return	mixed Resource if query executed successfully, otherwise false
	 *
	 * @author	Mark Hobson
	 */
	public function executeUpdate (PreparedStatement $ps, $name = 'default', $con = NULL, $debug = self::DEBUG)
	{
		if ($ps instanceof PdoPreparedStatement) {
			return parent::executeUpdate($ps, $name, $con, $debug);	
		}
		$this->executeQuery($ps, $name, $con, $debug);
		return mysql_affected_rows($con);
	}
	
	/**
	 * Execute an SQL Statement and return result to be handled by calling function.
	 *
	 * @param	mixed PreparedStatement or KeyBasedPreparedStatement
	 * @param	string Name of connection to be used
	 * @param	resource $connection Connection resource handler
	 * @return	mixed Resource if query executed successfully, otherwise false
	 *
	 * @author	Mark Hobson
	 */
	public function executeInsert (PreparedStatement $ps, $name = 'default', $con = NULL, $debug = self::DEBUG)
	{
		if ($ps instanceof PdoPreparedStatement) {
			return parent::executeInsert($ps, $name, $con, $debug);	
		}
		
		$retval = false;
		try {

			// Connect to database
			if (is_null($con)) {
				if (self::DEBUG) { LoggerManager::debug(__METHOD__  . ":: Retrieving New DB Connection for '" . $name . "'..."); }
				$con = $this->getContext()->getDatabaseConnection($name);
			}

			if (function_exists('mysql_ping')) {
				if (!mysql_ping($con)) {
					$this->getContext()->getDatabaseManager()->getDatabase($name)->shutdown();
					$con = $this->getContext()->getDatabaseConnection($name);	
				}
			} else {
				throw new Exception('Missing php-mysql libraries on this server');	
			}

			// Get the prepared query
			$query = $ps->getPreparedStatement($con);

			if ($debug) {
				LoggerManager::error(__METHOD__ . " :: " . $ps->getDebugQueryString());
			}
			// Execute the query
			$rs = mysql_query ($query, $con);

			if (!$rs) { 
				throw new Exception (mysql_error ($con));
			} else {
				return mysql_insert_id($con);
			}

		} catch (MojaviException $e) {

			$this->getErrors ()->addError ('error', new Error ($e->getMessage ()));
			LoggerManager::fatal ($e->printStackTrace (''));
			throw $e;
		} catch (PDOException $e) {	
			$e = new MojaviException ($e->getMessage());
			LoggerManager::fatal ($e->printStackTrace(''));
			throw $e;
		} catch (Exception $e) {

			$this->getErrors ()->addError ('error', new Error ($e->getMessage ()));
			$e = new MojaviException ($e->getMessage());
			LoggerManager::fatal ($e->printStackTrace (''));
			throw $e;
		}
	}

	// -------------------------------------------------------------------------
}

?>

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
abstract class PdoModel extends MojaviObject
{
	const DEBUG = MO_DEBUG;
	const CRITERIA_RETVAL_TYPE_ITERATOR	= 1;
	const CRITERIA_RETVAL_TYPE_FORM		= 2;
	const RETRY_TRANSACTION_LIMIT       = 5; // How many times to re-attempt a transaction
	const RETRY_TRANSACTION_SLEEP       = 200; // How long to sleep between retrying failed transactions

    // +-----------------------------------------------------------------------+
    // | PRIVATE VARIABLES                                                     |
    // +-----------------------------------------------------------------------+

    // +-----------------------------------------------------------------------+
    // | METHODS                                                               |
    // +-----------------------------------------------------------------------+

    /**
     * Retrieve the current application context.
     *
     * @return Context The current Context instance.
     *
     * @author Sean Kerr (skerr@mojavi.org)
     * @since  3.0.0
     */
    public function getContext ()
    {
		return Controller::getInstance()->getContext();
    }

    // -------------------------------------------------------------------------

    /**
     * Initialize this model.
     *
     * @param Context The current application context.
     *
     * @return bool true, if initialization completes successfully, otherwise
     *              false.
     *
     * @author Sean Kerr (skerr@mojavi.org)
     * @since  3.0.0
     */
    public function initialize ($context)
    {
        return true;
    }

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
		$retval = false;
		// Retry transactions up to five times
		for ($i=0;$i<self::RETRY_TRANSACTION_LIMIT;$i++) {
			try {

				// Connect to database
				if (is_null($con)) {
					$con = $this->getContext()->getDatabaseConnection($name);
				}

				// Get the prepared query
				/* @var $sth PDOStatement */
				$sth = $ps->getPreparedStatement($con);

				// Debug the query to the log
				if ($debug) {
					LoggerManager::error(__METHOD__ . " :: (ATTEMPT " . $i . ") " . $ps->getDebugQueryString());
				}

				// Execute the query
				$sth->execute();

				// Set the retval to the statement because everything worked
				$retval = $sth;

				return $retval;


			} catch (PDOException $e) {
				// If the MySQL server has gone away, try reconnecting, otherwise throw an exception
				if ($e->getMessage() == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') {
					try {
						// If the server went away, then close the connection and try again
						$this->getContext()->getDatabaseManager()->getDatabase($name)->shutdown();
						// Give the server time to recover
						usleep(self::RETRY_TRANSACTION_SLEEP);
						// Reconnect to the server and try again
						$con = $this->getContext()->getDatabaseConnection($name);
					} catch (Exception $e) {
						// We can ignore this error because it'll be caught when we retry the transaction
					}
				} else if (strpos($e->getMessage(), 'try restarting transaction') !== false) {
					// If there was a lock on the transaction, then try it again before failing
					try {
						$this->getContext()->getDatabaseManager()->getDatabase($name)->shutdown();
						// Give the server time to recover
						usleep(self::RETRY_TRANSACTION_SLEEP);
						// Reconnect to the server and try again
						$con = $this->getContext()->getDatabaseConnection($name);
					} catch (Exception $e) {
						// We can ignore this error because it'll be caught when we retry the transaction
					}
				} else {
					LoggerManager::fatal($e->printStackTrace(''));
					usleep(self::RETRY_TRANSACTION_SLEEP);
				}
			} catch (MojaviException $e) {
				// Output Mojavi Exceptions to the log, and continue
				$this->getErrors()->addError('error', new Error ($e->getMessage()));
				LoggerManager::fatal($e->printStackTrace(''));
				usleep(self::RETRY_TRANSACTION_SLEEP);
			} catch (Exception $e) {
				// Output Normal Exceptions to the log, and continue
				$this->getErrors()->addError('error', new Error ($e->getMessage()));
				$e = new MojaviException ($e->getMessage());
				LoggerManager::fatal($e->printStackTrace(''));
				usleep(self::RETRY_TRANSACTION_SLEEP);
			}
		}
		throw new \Exception('Could not execute statement in timely manner.  try restarting transaction');
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
		$retval = $this->executeQuery($ps, $name, $con, $debug);
		return $retval->rowCount();
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
		$retval = false;
		try {

			// Connect to database
			if (is_null($con)) {
				if (self::DEBUG) { LoggerManager::debug(__METHOD__  . ":: Retrieving New DB Connection for '" . $name . "'..."); }
				$con = $this->getContext()->getDatabaseConnection($name);
			}

			// Get the prepared query
			/* @var $sth PDOStatement */
			$sth = $ps->getPreparedStatement($con);

			// Debug the query to the log
			if ($debug) { LoggerManager::error(__METHOD__ . " :: " . $ps->getDebugQueryString()); }

			// Execute the query
			$sth->execute();

			// Return the last insert id
			$retval = $con->lastInsertId();
		} catch (PDOException $e) {
			// If the MySQL server has gone away, try reconnecting, otherwise throw an exception
			if ($e->getMessage() == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') {
				try {
					$this->getContext()->getDatabaseManager()->getDatabase($name)->shutdown();

					// Connect to database
					$con = $this->getContext()->getDatabaseConnection($name);

					// Get the prepared query
					/* @var $sth PDOStatement */
					$sth = $ps->getPreparedStatement($con);

					if ($debug) {
						LoggerManager::error(__METHOD__ . " :: " . $ps->getDebugQueryString());
					}
					// Execute the query
					$sth->execute();

					// Return the last insert id
			        $retval = $con->lastInsertId();

				} catch (Exception $e) {
					ob_start();
					$sth->debugDumpParams();
					$stmt = ob_get_clean();

					$this->getErrors ()->addError ('error', new Error ($e->getMessage() . ": " . $sth->queryString));

					$e = new MojaviException ($e->getMessage());
					LoggerManager::fatal ($sth->queryString);
					LoggerManager::fatal ($stmt);
					LoggerManager::fatal ($e->printStackTrace(''));
					throw $e;
				}
			} else if (strpos($e->getMessage(), 'Lock wait timeout exceeded; try restarting transaction') !== false) {
				// If there was a lock on the transaction, then try it again before failing
				try {
					$this->getContext()->getDatabaseManager()->getDatabase($name)->shutdown();

					// Connect to database
					$con = $this->getContext()->getDatabaseConnection($name);

					// Get the prepared query
					/* @var $sth PDOStatement */
					$sth = $ps->getPreparedStatement($con);

					if ($debug) {
						LoggerManager::error(__METHOD__ . " :: " . $ps->getDebugQueryString());
					}
					// Execute the query
					$sth->execute();

					// Return the last insert id
			        $retval = $con->lastInsertId();

				} catch (Exception $e) {
					ob_start();
					$sth->debugDumpParams();
					$stmt = ob_get_clean();

					$this->getErrors ()->addError ('error', new Error ($e->getMessage() . ": " . $sth->queryString));

					$e = new MojaviException ($e->getMessage());
					LoggerManager::fatal ($sth->queryString);
					LoggerManager::fatal ($stmt);
					LoggerManager::fatal ($e->printStackTrace(''));
					throw $e;
				}

			} else {
				ob_start();
				$sth->debugDumpParams();
				$stmt = ob_get_clean();

				$this->getErrors ()->addError ('error', new Error ($e->getMessage() . ": " . $sth->queryString));

				$e = new MojaviException ($e->getMessage());
				LoggerManager::fatal ($sth->queryString);
				LoggerManager::fatal ($stmt);
				LoggerManager::fatal ($e->printStackTrace(''));
				throw $e;
			}
		} catch (MojaviException $e) {
			// Output Mojavi Exceptions to the log and throw the Exception
			$this->getErrors ()->addError ('error', new Error ($e->getMessage ()));
			LoggerManager::fatal ($e->printStackTrace (''));
			throw $e;
		} catch (Exception $e) {
			// Output Normal Exceptions to the log and throw the Exception
			$this->getErrors ()->addError ('error', new Error ($e->getMessage ()));
			$e = new MojaviException ($e->getMessage());
			LoggerManager::fatal ($e->printStackTrace (''));
			throw $e;
		}
		return $retval;
	}

	// -------------------------------------------------------------------------
}

?>

<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * PdoDatabase provides connectivity for the PDO database API layer.
 *
 * @package    agavi
 * @subpackage database
 *
 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
 * @author     David ZŸlke <dz@bitxtender.com>
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @author     Veikko MŠkinen <veikko@veikkomakinen.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.9.0
 *
 * @version    $Id: AgaviPdoDatabase.class.php 4578 2010-08-20 19:16:28Z david $
 */
class PdoDatabase extends Database
{
	/**
	 * Connect to the database.
	 *
	 * @throws     <b>DatabaseException</b> If a connection could not be 
	 *                                           created.
	 *
	 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
	 * @author     David ZŸlke <dz@bitxtender.com>
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @author     Veikko MŠkinen <veikko@veikkomakinen.com>
	 * @since      0.9.0
	 */
	function connect()
	{
		// determine how to get our parameters
		$method = $this->getParameter('method', 'dsn');

		// get parameters
		switch($method) {
			case 'dsn' :
				$dsn = $this->getParameter('dsn');
				if($dsn == null) {
					// missing required dsn parameter
					$error = 'Database configuration specifies method "dsn", but is missing dsn parameter';
					throw new DatabaseException($error);
				}
				break;
		}

		try {
			$username = $this->getParameter('user');
			$password = $this->getParameter('password');

			$options = array();
			// Enable local infile access
			$options[PDO::MYSQL_ATTR_LOCAL_INFILE] = true;

			if($this->hasParameter('options')) {
				foreach((array)$this->getParameter('options') as $key => $value) {
					$options[is_string($key) && strpos($key, '::') ? constant($key) : $key] = is_string($value) && strpos($value, '::') ? constant($value) : $value;
				}
			}
			$dsn .= ";charset=UTF8";
			$this->connection = new PDO($dsn, $username, $password, $options);

			// default connection attributes
			$attributes = array(
				// lets generate exceptions instead of silent failures
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_EMULATE_PREPARES => true,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
			);
			if($this->hasParameter('attributes')) {
				foreach((array)$this->getParameter('attributes') as $key => $value) {
					$attributes[is_string($key) && strpos($key, '::') ? constant($key) : $key] = is_string($value) && strpos($value, '::') ? constant($value) : $value;
				}
			}
			foreach($attributes as $key => $value) {
				$this->connection->setAttribute($key, $value);
			}
			// since we're not an abstraction layer, we copy the connection
	        // to the resource
	        $this->resource = $this->connection;
		} catch(PDOException $e) {
			throw new DatabaseException($e->getMessage());
		}
	}

	/**
	 * Execute the shutdown procedure.
	 *
	 * @throws     <b>DatabaseException</b> If an error occurs while shutting
	 *                                           down this database.
	 *
	 * @author     Daniel Swarbrick <daniel@pressure.net.nz>
	 * @since      0.9.0
	 */
	public function shutdown()
	{
		// assigning null to a previously open connection object causes a disconnect
		$this->connection = null;
	}
}

?>
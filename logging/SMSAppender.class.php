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
 *
 *
 * @package    mojavi
 * @subpackage logging
 *
 * @author     Sean Kerr (skerr@mojavi.org)
 * @copyright  (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since      3.0.0
 * @version    $Id: MailAppender.class.php 65 2004-10-26 03:16:15Z seank $
 */
class SMSAppender extends Appender
{

	protected $toAddress;
	protected $fromAddress;
	protected $host;
	
	/**
	 * Initialize the FileAppender.
	 * 
	 * @param array An array of parameters.
	 * 
	 * @return void
	 * 
	 * @author Bob Zoller (bob@agavi.org)
	 * @since 0.9.1
	 */
	public function initialize($params)
	{
		if (isset($params['to'])) {
			$this->setToAddress($params['to']);
		}
		if (isset($params['from'])) {
			$this->setFromAddress($params['from']);
		}
		if (isset($params['host'])) {
			$this->setHost($params['host']);
		}		
	}
	
	/**
	 * returns the host
	 * @return string
	 */
	function getHost() {
	    if (is_null($this->host)) {
	        $this->host = "localhost";
	    }
	    return $this->host;
	}
	
	/**
	 * sets the host
	 * @param string $arg0
	 */
	function setHost($arg0) {
	    $this->host = $arg0;
	}
	
	/**
	 * returns the fromAddress
	 * @return string
	 */
	function getFromAddress() {
	    if (is_null($this->fromAddress)) {
	        $this->fromAddress = "localhost";
	    }
	    return $this->fromAddress;
	}
	
	/**
	 * sets the fromAddress
	 * @param string $arg0
	 */
	function setFromAddress($arg0) {
	    $this->fromAddress = $arg0;
	}
	
	/**
	 * returns the toAddress
	 * @return string
	 */
	function getToAddress() {
	    if (is_null($this->toAddress)) {
	        $this->toAddress = "";
	    }
	    return $this->toAddress;
	}
	
	/**
	 * sets the toAddress
	 * @param string $arg0
	 */
	function setToAddress($arg0) {
	    $this->toAddress = $arg0;
	}
	
	/**
	 * Execute the shutdown procedure.
	 * 
	 * If open, close the filehandle.
	 * 
	 * return void
	 * 
	 * @author Bob Zoller (bob@agavi.org)
	 * @since 0.9.1
	 */
	public function shutdown()
	{
	}

   /**
	 * Write a Message to the file.
	 * 
	 * @param Message
	 * 
	 * @throws <b>LoggingException</b> if no Layout is set or the file
	 *         cannot be written.
	 * 
	 * @return void
	 * 
	 * @author Bob Zoller (bob@agavi.org)
	 * @since 0.9.1
	 */
	public function write($message)
	{
		if ($layout = $this->getLayout() === null) {
			throw new LoggingException('No Layout set');
		}

		$str = sprintf("%s", $this->getLayout()->format($message));
		$fromHeader = "From: " . $this->getFromAddress() . "\r\n";
		mail($this->getToAddress(), date("m/d/Y h:i.s",strtotime("now")),$str, $fromHeader);
	}

}

?>
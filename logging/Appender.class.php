<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005 Agavi Foundation.                                 |
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
 * Appender allows you to specify a destination for log data and provide
 * a custom layout for it, through which all log messages will be formatted.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Bob Zoller (bob@agavi.org)
 * @copyright (c) Authors
 * @since     0.9.1
 * @version   $Id$
 */
abstract class Appender extends MojaviObject
{

	private $layout = null;
	private $priority = null;

	/**
	 * Initialize the object.
	 *
	 * @return void
	 *
	 * @author Bob Zoller (bob@agavi.org)
	 * @since  0.9.1
	 */
	abstract function initialize($params);

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the layout.
	 *
	 * @return Layout A Layout instance, if one has been set, otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function getLayout ()
	{
		return $this->layout;
	}

	// -------------------------------------------------------------------------

	/**
	 * Set the layout.
	 *
	 * @param Layout A Layout instance.
	 *
	 * @return Appender
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	public function setLayout ($layout)
	{
		$this->layout = $layout;
		return $this;
	}

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	abstract function shutdown ();

	// -------------------------------------------------------------------------

	/**
	 * returns the priority
	 * @return string
	 */
	function getPriority() {
	    if (is_null($this->priority)) {
	        $this->priority = Logger::INFO;
	    }
	    return $this->priority;
	}
	
	/**
	 * sets the priority
	 * @param string $arg0
	 */
	function setPriority($arg0) {
	    $this->priority = $arg0;
	}
	
	/**
	 * Write log data to this appender.
	 *
	 * @param string Log data to be written.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  0.9.0
	 */
	abstract function write ($message);

}

?>

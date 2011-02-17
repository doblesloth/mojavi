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
 * FileAppender appends Messages to a given file.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Bob Zoller (bob@agavi.org)
 * @copyright (c) Authors
 * @since     0.9.1
 * @version   $Id$
 */
class ErrorLogAppender extends Appender
{

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
		if (isset($params['priority'])) {
			$this->setPriority($params['priority']);
		}
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
		error_log(strtr($str, array("\t" => "    ", "\r\n" => '', "\r" => '', "\n" => '')));
	}

}

?>

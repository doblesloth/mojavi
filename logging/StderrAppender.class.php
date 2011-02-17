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
 * StderrAppender appends Messages to the stderr.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Bob Zoller (bob@agavi.org)
 * @copyright (c) Authors
 * @since     0.9.1
 * @version   $Id$
 */
class StderrAppender extends FileAppender
{

	/**
	 * Initialize the object.
	 * 
	 * @param array An array of parameters.
	 * 
	 * @return mixed
	 * 
	 * @author Bob Zoller (bob@agavi.org)
	 * @since 0.9.1
	 */
	public function initialize($params)
	{
		$params['file'] = 'php://stderr';
		return parent::initialize($params);
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
		if (($fp = fopen('php://stderr', 'a'))) {
			fwrite($fp, strtr($str, array("\t" => "    ", "\r\n" => PHP_EOL, "\r" => PHP_EOL, "\n" => PHP_EOL)) . PHP_EOL);
			fclose($fp);
		}
	}

}

?>

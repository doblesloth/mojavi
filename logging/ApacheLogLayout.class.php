<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2003-2005 Agavi Foundation                                  |
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
 * PassthruLayout is a Layout that will return the Message text unaltered.
 *
 * @package    agavi
 * @subpackage logging
 *
 * @author    Bob Zoller (bob@agavi.org)
 * @copyright (c) Authors
 * @since     0.9.1
 * @version   $Id: PatternLayout.class.php 87 2005-06-03 21:19:23Z bob $
 */
class ApacheLogLayout extends Layout
{

    /**
     * Format a message.
     *
     * @param Message A Message instance.
     *
     * @return string A formatted message.
     *
     * @author Mark Hobson (hobby@redfiveconsulting.com)
     * @since  0.9.1
     */
    public function &format ($message)
    {
		$dateString = date("D M j h:i:s Y", strtotime("now"));
		$priorityString = "";
		if ($message->getPriority() == Logger::WARN) {
			$priorityString = "warn";
		} else if ($message->getPriority() == Logger::DEBUG) {
			$priorityString = "debug";
		} else if ($message->getPriority() == Logger::INFO) {
			$priorityString = "info";
		} else if ($message->getPriority() == Logger::ERROR) {
			$priorityString = "error";
		} else if ($message->getPriority() == Logger::FATAL) {
			$priorityString = "fatal";
		}

		$msgString = sprintf('[%s] [%s] %s', $dateString, $priorityString, str_replace("\t", "    ", $message->__toString()));
        return $msgString;
    }

}

?>

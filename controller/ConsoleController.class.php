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
 * @package    mojavi
 * @subpackage controller
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     3.0.0
 * @version   $Id: ConsoleController.class.php 65 2004-10-26 03:16:15Z seank $
 */
abstract class ConsoleController extends Controller
{
	/**
	 * Initializes this controller for use outside a normal context
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function loadContext()
	{
		// initialize the controller
		$this->initialize();
		
		// get the application context
		$context = $this->getContext();
		
		return true;
	}
}

?>
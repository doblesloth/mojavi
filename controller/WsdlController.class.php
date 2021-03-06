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
 * PageWebController allows you to dispatch a request by specifying a module
 * and action name in the dispatch() method.
 *
 * @package    mojavi
 * @subpackage controller
 *
 * @author    Mark Hobson (hobby@redfiveconsulting.com)
 * @since     3.0.0
 * @version   $Id: PageWebController.class.php 746 2005-01-12 19:54:07Z seank $
 */
class WsdlController extends WebController
{
	const DEBUG = MO_DEBUG;
    // +-----------------------------------------------------------------------+
    // | METHODS                                                               |
    // +-----------------------------------------------------------------------+

    /**
     * Dispatch a request.
     *
     * @param string A module name.
     * @param string An action name.
     * @param array  An associative array of parameters to be set.
     *
     * @return void
     *
     * @author Sean Kerr (skerr@mojavi.org)
     * @since  3.0.0
     */
    public function dispatch ($moduleName, $actionName, $parameters = null)
    {
        try
        {

            // initialize the controller
            $this->initialize();

            // set parameters
            if ($parameters != null)
            {

                $this->getContext()
                     ->getRequest()
                     ->setParametersByRef($parameters);

            }
            
            $this->forward("WS", "WSLogin");
			$login_buffer = "";
			for ($i=0;$i<$this->getActionStack()->getSize();$i++) {
				$login_buffer .= $this->getActionStack()->getEntry($i)->getPresentation();	
			}
			if ($login_buffer == "") {
            	// make the first request
            	$this->forward($moduleName, $actionName);
			}

        } catch (MojaviException $e)
        {

            $e->printStackTrace();

        } catch (Exception $e)
        {

            // most likely an exception from a third-party library
            $e = new MojaviException($e->getMessage());

            $e->printStackTrace();

        }
    }
    
    /**
     * Generic function used for webservice calls
     * @param string $func
     * @param array $vars
     * @return string
     */
    function __call($func, $vars) {
		$controller = Controller::newInstance("WsdlController");
		$controller->setRenderMode(View::RENDER_VAR);
		$controller->dispatch("WS", $func, $vars);
		for ($i=0;$i<$controller->getActionStack()->getSize();$i++) {
			$buffer .= $controller->getActionStack()->getEntry($i)->getPresentation();
		}
		return $buffer;
	}
}

?>
<?php

/**
 * BasicConsoleController is used for basic console commands to hook into a WebController
 *
 * @version $Id$
 * @copyright 2005 
 **/
 
class BasicConsoleController extends ConsoleController {

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+
	
	/**
	 * Dispatch a request.
	 *
	 * This will determine which module and action to use by request parameters
	 * specified by the user.
	 *
	 * @param array $arg_options
	 * @param array $req_args
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function dispatch ($arg_options = array(), $req_args = array())
	{

		try
		{
			// Setup default arg_options and req_args arrays
			$opt_array = Array(
				"-m" 			=> "module",
				"--module" 	=> "module",
				"-a" 			=> "action",
				"--action" 	=> "action",
				"-h" 			=> "help_flag",
				"--help" 	=> "help_flag"
			);
			$arg_options = array_merge($opt_array, $arg_options);
			$req_array = Array("module", "action");
			$req_args = array_merge($req_array, $req_args);
			
			$this->parseArgs($arg_options);
			
			$moduleName = @$_REQUEST['module'];
			$actionName = @$_REQUEST['action'];
			
			// initialize the controller
			$this->initialize();

			// get the application context
			$context = $this->getContext();
			
			// Check to see if script is being run on console
			if(isset($_SERVER['_']) && !isset($_SERVER['HTTP_HOST'])) {
				$context->getUser()->setAuthenticated(true);
				$this->getContext()->getUser()->addCredential(MO_CONSOLE_CREDENTIAL);
				define("MO_IS_CONSOLE", true);
			}

			if ($moduleName == null)
			{

				// no module has been specified
				$moduleName = MO_DEFAULT_MODULE;

			}

			if ($actionName == null)
			{

				// no action has been specified
				if ($this->actionExists($moduleName, 'Index'))
				{

					// an Index action exists
					$actionName = 'Index';

				} else
				{

					// use the default action
					$actionName = MO_DEFAULT_ACTION;

				}

			}
			
			if (!$this->hasRequiredArgs($req_args) || isset($_REQUEST['help_flag'])) {
				$this->getAction($moduleName, $actionName)->showHelpMessage();
			} else {
				// make the first request
				$this->forward($moduleName, $actionName);
			}
		} catch (MojaviException $e)
		{

			$e->printStackTrace('console');

		} catch (Exception $e)
		{

			// most likely an exception from a third-party library
			$e = new MojaviException($e->getMessage());

			$e->printStackTrace('console');

		}

	}
	
	/**
	 * Parses $argv and enters name/value pairs into $_GET and $_REQUEST
	 */
	function parseArgs($arg_options) {
		global $argv;
		if(isset($argv) && is_array($argv)) {
			$last_key = '';
			$arg_arr = array();
			foreach($argv as $arg_num => $arg) {
				if($arg_num > 0) { // Excludes script name
					if(substr($arg, 0, 2) == "--") {
						// Double Dash
						if(stristr($arg, "=") !== false) {
							// Name Value Pair
							$tmp_arr = explode("=", $arg);
							$tmp_key = array_shift($tmp_arr);
							$tmp_value = implode("=", $tmp_arr);
						} else {
							// Boolean Arg
							$tmp_key = $arg;
							$tmp_value = "1";
						}
						$tmp_key = ( isset($arg_options[$tmp_key]) ? $arg_options[$tmp_key] : substr($tmp_key, 2) );
						$arg_arr[$tmp_key] = $tmp_value;
						$last_key = '';
					} elseif(substr($arg, 0, 1) == "-") {
						// Single Dash
						if(strlen($arg) == 2) {
							// Single Arg
							$tmp_key = ( isset($arg_options[$arg]) ? $arg_options[$arg] : substr($arg, 1) );
							$tmp_value = "1";
							$arg_arr[$tmp_key] = $tmp_value;
							$last_key = $tmp_key;
						} else {
							// Multiple Args
							$tmp_arg = substr($arg, 1);
							for($i=0;$i<strlen($tmp_arg);$i++) {
								$tmp_key = "-" . substr($tmp_arg, $i, 1);
								$tmp_key = ( isset($arg_options[$tmp_key]) ? $arg_options[$tmp_key] : substr($tmp_key, 1) );
								$tmp_value = "1";
								$arg_arr[$tmp_key] = $tmp_value;
								$last_key = $tmp_key;
							}
						}
					} else {
						// Regular Arg
						if(stristr($arg, "=") !== false) {
							// Name Value Pair
							$tmp_arr = explode("=", $arg);
							$tmp_key = array_shift($tmp_arr);
							$tmp_value = implode("=", $tmp_arr);
							$tmp_key = ( isset($arg_options[$tmp_key]) ? $arg_options[$tmp_key] : $tmp_key );
							$arg_arr[$tmp_key] = $tmp_value;
						} elseif($last_key != '') {
							// Value For Previous Arg
							$arg_arr[$last_key] = $arg;
						} else {
							// Boolean Arg
							$tmp_key = ( isset($arg_options[$arg]) ? $arg_options[$arg] : $arg );
							$tmp_value = "1";
							$arg_arr[$tmp_key] = $tmp_value;
						}
						$last_key = '';
					}
				} // if
			} // foreach
			
			// Loop Through $arg_arr and insert args into $_GET and $_REQUEST
			foreach($arg_arr as $name => $value) {
				$_GET[$name] = $value;
				$_REQUEST[$name] = $value;
			}
		}
	}
	
	/**
	 * Checks to see if required args are present.  Returns true if all required args are present, otherwise false.
	 *
	 * @param array $req_args
	 * @return bool
	 */
	function hasRequiredArgs($req_args) {
		$retVal = true;
		foreach($req_args as $req_arg) {
			if(!isset($_REQUEST[$req_arg])) {
				$retVal = false;
			}
		}
		return $retVal;
	}
	
	/**
	 * Displays help message for console and then dies
	 *
	 * @param array $arg_options
	 * @param array $req_args
	 */
	function showHelpMessage($arg_options, $req_args) {
		global $argv;
		$help_msg = "Usage:\t";
		if (count($argv) > 0) {
			$help_msg .= $argv[0] . " ";
		}
		foreach($req_args as $req_arg) {
			$found_req_arg = false;
			foreach($arg_options as $arg_key => $arg_option) {
				if($arg_option == $req_arg && !$found_req_arg) {
					$help_msg .= $arg_key;
					if(!preg_match("/_flag$/", $arg_option)) {
						if(preg_match("/^\-[^\-]/", $arg_key)) {
							$help_msg .= " ";
						} else {
							$help_msg .= "=";
						}
						$help_msg .= "<" . $arg_option . ">";
					}
					$help_msg .= " ";
					$found_req_arg = true;
				}
			}
		}
		$help_msg .= "[args...]\n";
		$help_msg .= "\n";
		$unique_args = array_unique($arg_options);
		foreach($unique_args as $arg) {
			$option_count = 1;
			$help_msg .= "   ";
			foreach($arg_options as $arg_key => $arg_option) {
				if($arg_option == $arg) {
					$help_msg .= ( $option_count > 1 ? ", " : "" ) . $arg_key;
					if(!preg_match("/_flag$/", $arg_option)) {
						if(preg_match("/^\-[^\-]/", $arg_key)) {
							$help_msg .= " ";
						} else {
							$help_msg .= "=";
						}
						$help_msg .= "<" . $arg_option . ">";
					}
					$help_msg .= "";
					$option_count++;
				}
			}
			$help_msg .= "\n";
		}
		echo $help_msg;
		die();
	}
	
	function redirect() {
		echo StringTools::getDebugBacktraceForLogs();
		die();
	}
	
}

?>
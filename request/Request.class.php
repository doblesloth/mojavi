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
 * Request provides methods for manipulating client request information such
 * as attributes, errors and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    mojavi
 * @subpackage request
 *
 * @author    Sean Kerr (skerr@mojavi.org)
 * @copyright (c) Sean Kerr, {@link http://www.mojavi.org}
 * @since     1.0.0
 * @version   $Id: Request.class.php 640 2004-12-10 14:10:30Z seank $
 */
abstract class Request extends ParameterHolder
{

	// +-----------------------------------------------------------------------+
	// | CONSTANTS                                                             |
	// +-----------------------------------------------------------------------+

	/**
	 * Process validation and execution for only GET requests.
	 *
	 * @since 3.0.0
	 */
	const GET = 2;

	/**
	 * Skip validation and execution for any request method.
	 *
	 * @since 3.0.0
	 */
	const NONE = 1;

	/**
	 * Process validation and execution for only POST requests.
	 *
	 * @since 3.0.0
	 */
	const POST = 4;
	
	/**
	 * Process validation and execution for only PUT requests.
	 *
	 * @since 3.0.0
	 */
	const PUT = 8;
	
	/**
	 * Process validation and execution for only DELETE requests.
	 *
	 * @since 3.0.0
	 */
	const DELETE = 16;
	
	/**
	 * Allows any request method
	 */
	const ANY = 32;

	// +-----------------------------------------------------------------------+
	// | PRIVATE VARIABLES                                                     |
	// +-----------------------------------------------------------------------+

	private
		$attributes = array(),
		$errors     = null,
		$method     = null,
		$rawBody	= null;

	// +-----------------------------------------------------------------------+
	// | METHODS                                                               |
	// +-----------------------------------------------------------------------+

	/**
	 * Clear all attributes associated with this request.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function clearAttributes ()
	{

		$this->attributes = null;
		$this->attributes = array();

	}

	// -------------------------------------------------------------------------

	/**
	 * Extract parameter values from the request.
	 *
	 * @param array An indexed array of parameter names to extract.
	 *
	 * @return array An associative array of parameters and their values. If
	 *               a specified parameter doesn't exist an empty string will
	 *               be returned for its value.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function & extractParameters ($names)
	{

		$array = array();

		foreach ($this->parameters as $key => &$value)
		{

			if (in_array($key, $names))
			{

				$array[$key] =& $value;

			}

		}

		return $array;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an attribute.
	 *
	 * @param string An attribute name.
	 * @param mixed The name of the module of the form you'd like returned OR the actual value you'd like returned if the parameter does not exist
	 * @param string The name of the form you'd like returned 
	 *
	 * @return mixed An attribute value, if the attribute exists, otherwise
	 *               null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function & getAttribute ($name, $default = null, $default2 = null)
	{

		$retval = $default;

		if (isset($this->attributes[$name])) {
			$retval = $this->attributes[$name];

		} elseif(!is_null($default2)) {
			
			$retval = Controller::getInstance()->getContext()->getController()->getForm($default,$default2);
			
		}

		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an array of attribute names.
	 *
	 * @return array An indexed array of attribute names.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function getAttributeNames ()
	{

		return array_keys($this->attributes);

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an error message.
	 *
	 * @param string An error name.
	 *
	 * @return string An error message, if the error exists, otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function getError ($name)
	{
		$retval = null;
		$error_arr = $this->getErrors()->getErrors($name);
		if(isset($error_arr[0])) {
			$retval = $error_arr[0]->getMessage();
		}
		return $retval;
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve an array of error names.
	 *
	 * @return array An indexed array of error names.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function getErrorNames ()
	{
		return $this->getErrors()->getErrorKeys();
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve the errors object
	 * @return Errors
	 */
	public function getErrors ()
	{
		if (is_null($this->errors)) {
			$this->errors = new Errors();
		}
		return $this->errors;

	}

	// -------------------------------------------------------------------------

	/**
	 * Clears the errors object of all errors
	 * @return void
	 */
	public function clearErrors ()
	{
		$this->errors = null;
	}

	
	// -------------------------------------------------------------------------

	/**
	 * Retrieve this request's method.
	 *
	 * @return int One of the following constants:
	 *             - Request::GET
	 *             - Request::POST
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function getMethod ()
	{

		return $this->method;

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not an attribute exists.
	 *
	 * @param string An attribute name.
	 *
	 * @return bool true, if the attribute exists, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function hasAttribute ($name)
	{

		return isset($this->attributes[$name]);

	}

	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not an error exists.
	 *
	 * @param string An error name.
	 *
	 * @return bool true, if the error exists, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function hasError ($name)
	{

		$error_arr = $this->getErrors()->getErrors($name);
		if(count($error_arr > 0)) {
			return true;
		} else {
			return false;
		}

	}


	// -------------------------------------------------------------------------

	/**
	 * Indicates whether or not any errors exist.
	 *
	 * @return bool true, if any error exist, otherwise false.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  2.0.0
	 */
	public function hasErrors ()
	{
		if($this->getErrors()->isEmpty()) {
			return false;
		} else {
			return true;
		}
	}

	// -------------------------------------------------------------------------

	/**
	 * Initialize this Request.
	 *
	 * @param Context A Context instance.
	 * @param array   An associative array of initialization parameters.
	 *
	 * @return bool true, if initialization completes successfully, otherwise
	 *              false.
	 *
	 * @throws <b>InitializationException</b> If an error occurs while
	 *                                        initializing this Request.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	abstract function initialize ($context, $parameters = null);

	// -------------------------------------------------------------------------

	/**
	 * Retrieve a new Request implementation instance.
	 *
	 * @param string A Request implementation name.
	 *
	 * @return Request A Request implementation instance.
	 *
	 * @throws <b>FactoryException</b> If a request implementation instance
	 *                                 cannot be created.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public static function newInstance ($class)
	{

		// the class exists
		$object = new $class();

		if (!($object instanceof Request))
		{

			// the class name is of the wrong type
			$error = 'Class "%s" is not of the type Request';
			$error = sprintf($error, $class);

			throw new FactoryException($error);

		}

		return $object;

	}

	// -------------------------------------------------------------------------

	/**
	 * Remove an attribute.
	 *
	 * @param string An attribute name.
	 *
	 * @return mixed An attribute value, if the attribute was removed,
	 *               otherwise null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function & removeAttribute ($name)
	{

		$retval = null;

		if (isset($this->attributes[$name]))
		{

			$retval =& $this->attributes[$name];

			unset($this->attributes[$name]);

		}

		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Remove an error.
	 *
	 * @param string An error name.
	 *
	 * @return string An error message, if the error was removed, otherwise
	 *                null.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function & removeError ($name)
	{

		$retval = null;

		$error_arr = $this->getErrors()->getErrors($name);
		if(count($error_arr) > 0) {
			$retval = '';
			foreach($error_arr as $error) {
				$retVal .= $error->getMessage() . " ";
			}
		}
		$this->getErrors()->removeErrorsByKey($name);
		
		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set an attribute.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param string An attribute name.
	 * @param mixed  An attribute value.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function setAttribute ($name, $value)
	{

		$this->attributes[$name] = $value;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set an attribute by reference.
	 *
	 * If an attribute with the name already exists the value will be
	 * overridden.
	 *
	 * @param string An attribute name.
	 * @param mixed  A reference to an attribute value.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function setAttributeByRef ($name, &$value)
	{

		$this->attributes[$name] =& $value;

	}

	// -------------------------------------------------------------------------

	/**
	 * Set an array of attributes.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param array An associative array of attributes and their associated
	 *              values.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function setAttributes ($attributes)
	{

		$this->attributes = array_merge($this->attributes, $attributes);

	}

	// -------------------------------------------------------------------------

	/**
	 * Set an array of attributes by reference.
	 *
	 * If an existing attribute name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @param array An associative array of attributes and references to their
	 *              associated values.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	public function setAttributesByRef (&$attributes)
	{

		foreach ($attributes as $key => &$value)
		{

			$this->attributes[$key] =& $value;

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Set an error.
	 *
	 * @param name    An error name.
	 * @param message An error message.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  1.0.0
	 */
	public function setError ($name, $message)
	{
		$this->getErrors()->addError($name, new Error($message));
	}


	// -------------------------------------------------------------------------

	/**
	 * Set an array of errors
	 *
	 * If an existing error name matches any of the keys in the supplied
	 * array, the associated message will be overridden.
	 *
	 * @param array An associative array of errors and their associated
	 *              messages.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  2.0.0
	 */
	public function setErrors ($errors)
	{
		if(is_array($errors)) {
			foreach($errors as $name => $message) {
				$this->getErrors()->addError($name, new Error($message));
			}
		}
	}

	// -------------------------------------------------------------------------

	/**
	 * Set the request method.
	 *
	 * @param int One of the following constants:
	 *            - Request::GET
	 *            - Request::POST
	 * 			  - Request::PUT
	 * 			  - Request::DELETE
	 *
	 * @return void
	 *
	 * @throws <b>MojaviException</b> - If the specified request method is
	 *                                  invalid.
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  2.0.0
	 */
	public function setMethod ($method)
	{

		if ($method == self::GET || $method == self::POST || $method == self::PUT || $method == self::DELETE)
		{

			$this->method = $method;

			return;

		}

		// invalid method type
		$error = 'Invalid request method: %s';
		$error = sprintf($error, $method);

		throw new MojaviException($error);

	}
	
	/**
     * Return the raw body of the request, if present
     *
     * @return string|false Raw body, or false if not present
     */
    public function getRawBody()
    {
        if ($this->rawBody == null) {
            $body = file_get_contents('php://input');

            if (strlen(trim($body)) > 0) {
                $this->rawBody = $body;
            } else {
                $this->rawBody = false;
            }
        }
        return $this->rawBody;
    }
    
    /**
     * Sets the raw body of the request, if present
     * @return Request
     */
    public function setRawBody($arg0) {
    	$this->rawBody = $arg0;
    	return $this;	
    }

	// -------------------------------------------------------------------------

	/**
	 * Execute the shutdown procedure.
	 *
	 * @return void
	 *
	 * @author Sean Kerr (skerr@mojavi.org)
	 * @since  3.0.0
	 */
	abstract function shutdown ();

}

?>
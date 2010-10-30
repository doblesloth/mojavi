<?php

 /**
 * Errors class to hold an array of errors in a map.  Many error objects can be assigned to the same
 * key (like a generic "errors" key) or they can be assigned to a specific key (like "firstname" which 
 * can then be used to display a specific error.  Multiple Error objects can be added to the Errors 
 * object with a key.  For instance:
 * 
 * $errors = new Errors();
 * $errors->addError("error", new Error("Your email address is invalid")); 
 * $errors->addError("error", new Error("Your phone number is invalid")); 
 * 
 * Or you can also add errors with individual keys so that you can display an appropriate error 
 * message next to each respective form field.  Like so:
 * 
 * $errors = new Errors();
 * $errors->addError("email", new Error("Your email address is invalid")); 
 * $errors->addError("phone", new Error("Your phone number is invalid")); 
 * 
 * To display an error, simply use the Errors object in the HTML code and call getError($arg0) 
 * function like so:
 * 
 * print $errors->getError("email");
 * print $errors->getError("phone");
 * print $errors->getError("error");
 * 
 * You can also call getErrorArray($arg0) to return an array of Error objects that you can iterate 
 * over like so:
 * 
 * $array = $errors->getErrorArray("error");
 * foreach ($array as $error) {
 * 		print $error->getMessage();
 * 		print "<p>";
 * }
 *
 */
 class Errors extends MojaviObject {
 
 	protected $errors;
 
 	/**
	 * Add an error to the list of errors.  Takes a keyname for the first argument and 
	 * an Error Object containing the error as the second argument.
	 * @param string $arg0 String key used to identify this error object
	 * @param Error $arg1 Error Object containing error message
	 */
 	function addError($arg0,$arg1) {
		/*
		* Check if the key exists.  If so, add this error to it, if not, create a new subarray and
		* then add the error to it.
		*/
		if(is_string($arg1)) {
			$arg1 = new Error($arg1);
		}
		$array = $this->getInternalErrors();
		if (is_array($array)) {
			if (array_key_exists($arg0,$array)) {
				$errarray = $array[$arg0];
				$errarray[] = $arg1;
				$array[$arg0] = $errarray;
			} else {
				$array[$arg0] = array($arg1);
			}
			$this->setInternalErrors($array);
		}
	}
	
	/**
	* Returns an array of errors based on the key passed in.  Generally, most errors that are not 
	* specific to a form field should just have the "error" key name.
	* @return array
	* @deprecated Use getErrors() instead
	*/
	function getErrorArray($arg0 = null) {
		return $this->getErrors($arg0);
	}
	
	/**
	* Returns an array of errors based on the key passed in.  Generally, most errors that are not 
	* specific to a form field should just have the "error" key name.
	* @return array
	*/
	function getErrors($arg0 = null) {
		$retVal = array();
		$internalErrors = $this->getInternalErrors();
		if (is_null($arg0)) {
			foreach ($internalErrors as $error) {
				$retVal = array_merge($retVal, $error);
			}
		} else {
			if (array_key_exists($arg0,$internalErrors)) {
				$retVal = $internalErrors[$arg0];
			}
		}
		return $retVal;
	}
	
	/**
	* Returns a string of errors based on the key passed in.  Generally, most errors that are not 
	* specific to a form field should just have the "error" key name.  Each error is separated by a 
	* break &lt;br&gt;
	* @return string
	*/
	function getErrorString($arg0 = null, $arg1 = "<br>") {
		$retVal = "";
		$errarray = $this->getErrors($arg0);
		foreach ($errarray as $error) {
	  		$retVal .= $error->getMessage();
	  		$retVal .= $arg1;
	  	}
		return $retVal;
	}
	
	/**
	 * Returns an array of keys
	 * @return array
	 */
	function getErrorKeys() {
		$array = $this->getInternalErrors();
		return array_keys($array);
	}
	
	/**
	 * Removes errors by passed in key
	 * @param string $arg0
	 */
	function removeErrorsByKey($arg0) {
		/*
		* Check if the key exists.  If so, remove errors
		*/
		$array = $this->getInternalErrors();
		if (is_array($array)) {
			if (array_key_exists($arg0,$array)) {
				unset($array[$arg0]);
			}
			$this->setInternalErrors($array);
		}
	}
	
	/**
	* Returns a string of all the errors.  Each error is separated by a 
	* break &lt;br&gt;
	* @return string
	* @deprecated Use getErrorString instread
	*/
	function getAllErrors() {
		return $this->getErrorString();
	}
	
	/**
	* Returns a string of all the errors.  Each error is separated by a 
	* break &lt;br&gt;
	* @return string
	* @deprecated Use getErrorString instread
	*/
	function getError($arg0 = null) {
		return $this->getErrorString($arg0);
	}
	
	/**
	* private function used internally to return the errors
	* @return array
	*/
	private function getInternalErrors() {
		if (is_null($this->errors)) {
			$this->errors = array();
		}
		return $this->errors;
	}
	
	/**
	* private function used internally to return the errors
	* @param Array $arg0
	*/
	private function setInternalErrors($arg0) {
		if (is_array($arg0)) {
		    $this->errors = $arg0;
		}
	}
	
	/**
	* Determines if the errors object is empty.  This is useful for forwards - if an error exists,
	* then you may want to forward to a failure page.
	* @return boolean
	*/
	function isEmpty() {
		$array = $this->getErrors();
		if (is_array($array)) {
			if (count($array) <= 0) {
			    return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	function getXml() {
		if ($this->isEmpty() == false)	{
		$retVal = '<errors>';
			foreach ($this->getErrors() as $error){
				$retVal .= $error->getXml();
			}
			$retVal .= '</errors>';
		}
		else {
			$retVal = '<errors/>';
		}
				return $retVal;
	}
	
	function clearAllErrors() {
		$new_errors = array();
		$this->setInternalErrors($new_errors);
	}
	
	/**
	 * Returns the errors as an array of strings
	 * @return array
	 */
	function toArray() {
		$ret_val = array();
		foreach ($this->getErrors() as $error) {
			$ret_val[] = $error->getMessage();
		}
		return $ret_val;
	}
 }
 
 /**
 * Error contains error information.  It is instantiated with an error string that will be 
 * displayed to the client.  Multiple Error objects can be added to the Errors object with a 
 * key.  For instance:
 * 
 * $errors = new Errors();
 * $errors->addError("error", new Error("Your email address is invalid")); 
 * $errors->addError("error", new Error("Your phone number is invalid")); 
 * 
 * Or you can also add errors with individual keys so that you can display an appropriate error 
 * message next to each respective form field.  Like so:
 * 
 * $errors = new Errors();
 * $errors->addError("email", new Error("Your email address is invalid")); 
 * $errors->addError("phone", new Error("Your phone number is invalid")); 
 * 
 * Refer to the Errors documentation on how to display the errors.
 */
 class Error {
		
	private $message;
	
	/**
	* Constructor to create a new Error object.  You can pass in a String for the argument to 
	* create a new Error Object.
	*/
	function __construct($arg0, $arg1 = null) {
		$this->setMessage($arg0);
	}
	
	/**
	* Returns the error message 
	* @return string Error Message that was stored in this Error object
	*/
	function getMessage() {
		if (is_null($this->message)) {
			$this->message= "";
		}
		return($this->message);
	}
	
	/**
	* Sets the error Mesage
	* @param string $arg0 String that contains the error message
	*/
	function setMessage($arg0) {
		$this->message = $arg0;
	}
}
?>

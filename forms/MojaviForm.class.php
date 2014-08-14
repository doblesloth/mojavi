<?php 
/**
 * BasicForm is the base class for ALL forms used.  It has a single function set (Id).  Every 
 * table should have an auto_increment field called id for conformity.  BasicForm has support 
 * for the Errors object and populate(Array).  Subclasses will be auto-populated as long as they 
 * contain getters and setters that match the criteria of the populate() method.
 * 
 * BasicForm can be instantiated with an errors object for ease of use. 
 */
abstract class MojaviForm extends MojaviObject {

	private $modified_columns;

	/**
	 * Populate will parse the elements of an array (ResultSet) or XML_ELEMENT_NODE and attempt 
	 * to populate the form.  It will convert _a to A (i.e. first_name => firstName) and will 
	 * search for an appropriate setter (such as first_name => setFirstName).  Be aware the it 
	 * will search for case sensitive functions, so first_name => firstName => setFirstName() is 
	 * not the same as firstname => setFirstname().
	 * @param array $arg0
	 */
	function populate($arg0) {
		$this->setModifiedColumns(null);
		if (is_array($arg0)) {
			// Attempt to populate the form
			foreach ($arg0 as $key => $value) {
				$callableName = null;				
				if (is_array($value)) {
					/*
					* If this is an array, then we need to add all the elements, so first check for an
					* add***($arg0) function.  If it does not exist, then fallback to a set***($arg0)
					*/
					# The regex will change '_a' to 'A' or '_1' to '1'
					$entry = preg_replace_callback("/_([a-zA-Z0-9])/", function($matches) { return strtoupper($matches[1]); }, strtolower($key));
					#$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
					if (is_callable(array($this, 'add' . ucfirst($entry)),false, $callableName)) {
						foreach ($value as $key2 => $value1) {
							if (substr($entry, -2) == "Id") {
								$value1 = IntegerTableEncoder::decodeInt($value1);	
							}
							$this->{'add' . ucfirst($entry)}($value1, $key2);
						}
					} else {
						# The regex will change '_a' to 'A' or '_1' to '1'
						$entry = preg_replace_callback("/_([a-zA-Z0-9])/", function($matches) { return strtoupper($matches[1]); }, strtolower($key));
						#$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
						if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
							if (substr($entry, -2) == "Id") {
								$value = IntegerTableEncoder::decodeInt($value);
							}
							$this->{'set' . ucfirst($entry)}($value);
						}
					}
				} else {
					# The regex will change '_a' to 'A' or '_1' to '1'
					$entry = preg_replace_callback("/_([a-zA-Z0-9])/", function($matches) { return strtoupper($matches[1]); }, strtolower($key));
					#$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
					if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
						if (substr($entry, -2) == "Id") {
							$value = IntegerTableEncoder::decodeInt($value);
						}
						$this->{'set' . ucfirst($entry)}($value);
					}
				}
			}
		} else if (is_object($arg0)) {
			# Treat the argument as an object and copy any getters to the appropriate setters
			$reflection = new ReflectionClass($arg0);
			$properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
			foreach ($properties as $property) {
				$method_name = ucfirst(StringTools::camelCase($property->getName()));
				if (is_callable(array($this, 'set' . $method_name), false, $callableName)) {
					if (method_exists($arg0, 'get' . $method_name)) {
						$value = $arg0->{'get' . $method_name}();
						# if this form has a setter that matches this getter (i.e. setId() would match getId()), then set it
						$this->{'set' . $method_name}($value);
					}
				}
			}
			
//			$methods = get_class_methods($arg0);
//			foreach ($methods as $method) {
//				$callableName = null;
//				# Only iterate the getters
//				if (strpos($method,"get") === 0) {
//					$method_name = substr($method,3);
//					# if this form has a setter that matches this getter (i.e. setId() would match getId()), then set it
//					if (is_callable(array($this, 'set' . $method_name),false, $callableName)) {
//						$this->{'set' . $method_name}($arg0->{$method}());
//					} 
//				} 
//			} // End foreach
		}// End is_array($arg0)
		return $this;
	}
	
	/**
	 * Returns the modified_columns
	 * @return array
	 */
	function getModifiedColumns() {
		if (is_null($this->modified_columns)) {
			$this->modified_columns = array();
		}
		return $this->modified_columns;
	}
	
	/**
	 * Sets the modified_columns
	 * @param array
	 */
	function setModifiedColumns($arg0) {
		$this->modified_columns = $arg0;
	}
	
	/**
	 * Sets the modified_columns
	 * @param string
	 */
	function addModifiedColumn($arg0) {
		$tmp_array = $this->getModifiedColumns();
		$tmp_array[] = $arg0;
		$this->setModifiedColumns($tmp_array);
	}
	
	/**
	 * Returns if this form has any modified columns
	 * @return boolean
	 */
	function isModified() {
		return (count($this->getModifiedColumns()) > 0);	
	}
	
	/**
	 * Checks if the value is null
	 * @return boolean
	 */
	function value_is_null($arg0) {
		return (is_null($arg0));
	}

	/**
	 * Returns the errors object.  Normally you want to setup an error object beforehand and pass 
	 * it to all the forms and models that you use so that you can collect all the errors
	 * @return Errors
	 */
	function getErrors() {
		return $this->getContext()->getErrors();
	}

	/**
	 * Attempts to validate this form.  If any errors occur, they are 
	 * populated in the internal errors object.
	 * @return boolean - true if validation succeeds
	 */
	abstract function validate();

	/**
	 * Resets a form.  This is mostly used with certain form elements (like a checkbox).
	 * If a checkbox is checked, it is passed in the request, if it is not checked, then 
	 * nothing is passed.  By resetting a checkbox property to false here, then every 
	 * request it is set to false, UNLESS a value is passed in - which is the way it's 
	 * supposed to work.
	 * @return boolean - true if validation succeeds
	 */
	abstract function reset();

	/**
	 * Initialize this form.
	 *
	 * @param Context The current application context.
	 *
	 * @return bool true, if initialization completes successfully, otherwise
	 *              false.
	 *
	 * @since  3.0.0
	 */
	public function initialize ($context) {
		return true;
	}

	/**
	 * Retrieve the current application context.
	 *
	 * @return Context The current Context instance.
	 *
	 * @since  3.0.0
	 */
	public function getContext() {
		return Controller::getInstance()->getContext();
	}
	
	/**
	 * Converts this object to an array
	 * @return array
	 */
	function toArray($deep = false) {
		$ret_val = array();
		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
		foreach ($properties as $property) {
			if (method_exists($this, 'get' . ucfirst(StringTools::camelCase($property->getName())))) {
				$value = $this->{'get' . ucfirst(StringTools::camelCase($property->getName()))}();
				if ($value instanceof MojaviForm) {
					$ret_val[$property->getName()] = $value->toArray($deep);
				} else if ($value instanceof DatabaseResultResource) {
					foreach ($value as $item) {
						$ret_val[$property->getName()][] = $item->toArray();	
					}
				} else {
					$ret_val[$property->getName()] = $value;
				}
			} else {
				$ret_val[$property->getName()] = $this->{$property->getName()};
			}
		}
		return $ret_val;
	}
}
?>
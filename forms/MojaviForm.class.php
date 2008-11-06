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

	private $errors;
	private $is_populating = false;
	private $register_modified_columns = true;
	protected $modified_columns;

	/**
	 * Default constructor used to instantiate this object with error support
	 * @param Errors $arg0
	 */
	function __construct($arg0 = null) {
		
	}

	/**
	 * Populate will parse the elements of an array (ResultSet) or XML_ELEMENT_NODE and attempt 
	 * to populate the form.  It will convert _a to A (i.e. first_name => firstName) and will 
	 * search for an appropriate setter (such as first_name => setFirstName).  Be aware the it 
	 * will search for case sensitive functions, so first_name => firstName => setFirstName() is 
	 * not the same as firstname => setFirstname().
	 * @param array $arg0
	 */
	function populate($arg0) {
		$modify_columns = true;
		if (func_num_args() >= 2) {
			$modify_columns = func_get_arg(1);
		}
		$this->setRegisterModifiedColumns($modify_columns);
		$this->setModifiedColumns(null);
		$this->is_populating = true;

		// Normalize input
		//$arg0 = $this->normalize($arg0);

		if (is_array($arg0)) {
			// Attempt to populate the form
			foreach ($arg0 as $key => $value) {
				if (is_array($value)) {
					/*
					* If this is an array, then we need to add all the elements, so first check for an
					* add***($arg0) function.  If it does not exist, then fallback to a set***($arg0)
					*/
					# The regex will change '_a' to 'A' or '_1' to '1'
					$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
					if (is_callable(array($this, 'add' . ucfirst($entry)),false, $callableName)) {
						foreach ($value as $key2 => $value1) {
							if (substr($entry, -2) == "Id") {
								$value1 = IntegerTableEncoder::decodeInt($value1);	
							}
							$this->{'add' . ucfirst($entry)}($value1, $key2);
						}
					} else {
						# The regex will change '_a' to 'A' or '_1' to '1'
						$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
						if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
							if (substr($entry, -2) == "Id") {
								$value = IntegerTableEncoder::decodeInt($value);
							}
							$this->{'set' . ucfirst($entry)}($value);
						}
					}
				} else {
					# The regex will change '_a' to 'A' or '_1' to '1'
					$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
					if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
						if (substr($entry, -2) == "Id") {
							$value = IntegerTableEncoder::decodeInt($value);
						}
						$this->{'set' . ucfirst($entry)}($value);
					}
				}
			}
		} else if (is_object($arg0)) {
			if (substr(get_class($arg0), 0, 3) == "DOM") {	
				# Attempt this as an xml string
				if ($arg0->nodeType == XML_ELEMENT_NODE) {
					// Populate the attributes first, this come in the form of <node attribute_1="" attribute_2="" />
					if ($arg0->hasAttributes()) {
						for ($i=0;$i<$arg0->attributes->length;$i++) {
							$attribute = $arg0->attributes->item($i);
							$entry = $attribute->nodeName;
							# The regex will change '_a' to 'A' or '_1' to '1'
							$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($entry));
							$value = $attribute->nodeValue;
							if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
								if (substr($entry, -2) == "Id") {
									$value = IntegerTableEncoder::decodeInt($value);
								}
								$this->{'set' . ucfirst($entry)}($value);
							}
						}
					}
					// Populate the child nodes next, this come in the form of <node><child_node_1>value_1</child_node_1></node>
					if ($arg0->hasChildNodes()) {
						for ($i=0;$i<$arg0->childNodes->length;$i++) {
							$element = $arg0->childNodes->item($i);
							if ($element->nodeType == XML_ELEMENT_NODE) {
								$entry = $element->nodeName;
								$value = $node_entry->nodeValue;
								if ($element->hasChildNodes()) {
									if ($element->firstChild->nodeType == XML_CDATA_SECTION_NODE) {
										$value = $element->firstChild->nodeValue;
									}
								}
								# The regex will change '_a' to 'A' or '_1' to '1'
								$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($entry));
								if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
									if (substr($entry, -2) == "Id") {
										$value = IntegerTableEncoder::decodeInt($value);
									}
									$this->{'set' . ucfirst($entry)}($value);
								}
							}
		
						} // End for
					}
				} // End if ($arg0-nodeType)
			} else if (get_class($arg0) == "SimpleXMLElement") {
				$sxml_array = array();
				$this->recurseXML($arg0, $sxml_array);
				// Attempt to populate the form
				foreach ($sxml_array as $key => $value) {
					if (is_array($value)) {
						/*
						* If this is an array, then we need to add all the elements, so first check for an
						* add***($arg0) function.  If it does not exist, then fallback to a set***($arg0)
						*/
						# The regex will change '_a' to 'A' or '_1' to '1'
						$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
						if (is_callable(array($this, 'add' . ucfirst($entry)),false, $callableName)) {
							foreach ($value as $key2 => $value1) {
								if (substr($entry, -2) == "Id") {
									$value1 = IntegerTableEncoder::decodeInt($value1);	
								}
								$this->{'add' . ucfirst($entry)}($value1, $key2);
							}
						} else {
							# The regex will change '_a' to 'A' or '_1' to '1'
							$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
							if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
								if (substr($entry, -2) == "Id") {
									$value = IntegerTableEncoder::decodeInt($value);
								}
								$this->{'set' . ucfirst($entry)}($value);
							}
						}
					} else {
						# The regex will change '_a' to 'A' or '_1' to '1'
						$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
						if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
							if (substr($entry, -2) == "Id") {
								$value = IntegerTableEncoder::decodeInt($value);
							}
							$this->{'set' . ucfirst($entry)}($value);
						}
					}
				}
			} else {
				# Treat the argument as an object an copy any getters to the appropriate setters
				$methods = get_class_methods($arg0);
				foreach ($methods as $method) {
					# Only iterate the getters
					if (strpos($method,"get") === 0) {
						$method_name = substr($method,3);
						# if this form has a setter that matches this getter (i.e. setId() would match getId()), then set it
						if (is_callable(array($this, 'set' . $method_name),false, $callableName)) {
							$this->{'set' . $method_name}($arg0->{$method}());
						} 
					} 
				} // End foreach
			} // End if (get_class($arg0) == "DOMNode")
		} else if (is_string($arg0)) {
			$sxml = new SimpleXMLElement($arg0);
			$sxml_array = array();
			$this->recurseXML($sxml, $sxml_array);
			// Attempt to populate the form
			foreach ($sxml_array as $key => $value) {
				if (is_array($value)) {
					/*
					* If this is an array, then we need to add all the elements, so first check for an
					* add***($arg0) function.  If it does not exist, then fallback to a set***($arg0)
					*/
					# The regex will change '_a' to 'A' or '_1' to '1'
					$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
					if (is_callable(array($this, 'add' . ucfirst($entry)),false, $callableName)) {
						foreach ($value as $key2 => $value1) {
							if (substr($entry, -2) == "Id") {
								$value1 = IntegerTableEncoder::decodeInt($value1);	
							}
							$this->{'add' . ucfirst($entry)}($value1, $key2);
						}
					} else {
						# The regex will change '_a' to 'A' or '_1' to '1'
						$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
						if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
							if (substr($entry, -2) == "Id") {
								$value = IntegerTableEncoder::decodeInt($value);
							}
							$this->{'set' . ucfirst($entry)}($value);
						}
					}
				} else {
					# The regex will change '_a' to 'A' or '_1' to '1'
					$entry = preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",strtolower($key));
					if (is_callable(array($this, 'set' . ucfirst($entry)),false, $callableName)) {
						if (substr($entry, -2) == "Id") {
							$value = IntegerTableEncoder::decodeInt($value);
						}
						$this->{'set' . ucfirst($entry)}($value);
					}
				}
			}
		}// End is_array($arg0)
		
		$this->is_populating = false;
		$this->setRegisterModifiedColumns(true);
	}

	/**
	 * Recurses the SimpleXMLElement object and returns an associative array
	 * @param SimpleXMLObject $xml
	 * @param array $vals
	 * @param string $parent
	 * @return integer
	 */
	private function recurseXML($xml, &$vals, $parent="")
	{
		$child_count = 0;
		foreach ($xml as $key => $value) {
			$child_count++;    
			$k = ($parent == "") ? (string)$key : $parent;
			if ($this->recurseXML($value, $vals, $k) == 0) { // no children, aka "leaf node"
				// if the key is the same as the parent, then don't use an array
				if (trim($k) == trim((string)$key)) {
					$vals[$k] = (string)$value;
				} else {
	      			$vals[$k][(string)$key] = (string)$value;
				}
			}	         
		}
		return $child_count;
	}
	
	/**
	 * Returns whether or not object is populating.
	 *	Used for GenericForm::__call
	 *
	 * @return	boolean
	 *
	 * @author	Mark Hobson
	 */
	protected function isPopulating ()
	{
		return $this->is_populating;
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
		if ($this->getRegisterModifiedColumns()) {
			$tmp_array = $this->getModifiedColumns();
			$tmp_array[] = $arg0;
			$this->setModifiedColumns($tmp_array);
		}
	}
	
	/**
	 * Returns if this form has any modified columns
	 * @return boolean
	 */
	function isModified() {
		return (count($this->getModifiedColumns()) > 0);	
	}
	
	/**
	 * Returns the register_modified_columns
	 * @return boolean
	 */
	function getRegisterModifiedColumns() {
		if (is_null($this->register_modified_columns)) {
			$this->register_modified_columns = false;
		}
		return $this->register_modified_columns;
	}
	
	/**
	 * Sets the register_modified_columns
	 * @param boolean
	 */
	function setRegisterModifiedColumns($arg0) {
		$this->register_modified_columns = $arg0;
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
	public final function getContext() {
		return Controller::getInstance()->getContext();
	}
}
?>

<?php
/**
 * This class extends the PreparedStatement class in order to add functionality for using key names.
 * @author Mark Hobson
 */
class KeyBasedPreparedStatement extends PreparedStatement {
	
	private $form;
	
	/**
	 * Returns the prepared statement with the values replaced.  The statement will be
	 * escaped for any string that was passed in.  The internal string is not modified
	 * during this process, so it can be used again and again with different values.
	 * @return string
	 */
	function getPreparedStatement($con = null) {
		$retVal = $this->statement;
		
		if(isset($this->form)) {
			$keys = array();
			preg_match_all("/<<(.*)>>/",$retVal,$keys);
			
			foreach($keys[1] as $key) {
				if (array_key_exists($key, $this->values)) continue;
				
				$funcName = "get" . ucfirst(preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",$key));
				if(method_exists($this->form,$funcName)) {
					$ret_val = $this->form->$funcName();
					if (is_float($ret_val)) {
						$this->setFloat($key, $ret_val);
					} else if (is_numeric($ret_val)) {
						$this->setInt($key, $ret_val);
					} else if ($ret_val == "now") {
						$this->setTimestamp($key, $ret_val);
					} else if (is_null($ret_val)) {
						$this->setNull($key);
					} else { 
						$this->setString($key, $ret_val);
					}
					
				}
			}
		}

		// Loop Through And Replace <<$name>> with $value
		foreach ($this->values as $name => $value) {
			$new_value = $value;
			if (is_array($new_value)) {
				if (array_key_exists("value", $new_value)) {
					if (array_key_exists("type", $new_value)) {
						$escaped_value = $this->getEscapedValue($new_value["value"], $new_value["type"]);
					} else {
						$escaped_value = $this->getEscapedValue($new_value["value"], self::TYPE_STRING);
					}
				} else {
					$escaped_value = $this->getEscapedValue($new_value, self::TYPE_STRING);
				}
			} else {
				$escaped_value = $this->getEscapedValue($new_value, self::TYPE_STRING);
			}

			$escaped_value = str_replace("$", "\\$", $escaped_value);

			$retVal = str_replace("<<" . $name . ">>", $escaped_value, $retVal);

		}

		return $retVal;
	}
	
	public function setForm($arg0) {
		$this->form = $arg0;
	}
}
?>
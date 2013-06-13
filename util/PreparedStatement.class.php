<?php
/**
 * PreparedStatement allows an SQL statement to be passed in, have values set for variables,
 * and then return the statement ready to be used in a query.  It will take care of delimiting
 * items and converting objects (i.e. datetime to a datetime format).
 *
 * @version $Id$
 * @copyright 2005
 */
class PreparedStatement extends MojaviObject {

	protected $statement;
	private $con;
	protected $values = array();

	const TYPE_STRING = 1;
	const TYPE_FLOAT = 2;
	const TYPE_INTEGER = 3;
	const TYPE_LONG = 4;
	const TYPE_DATE = 5;
	const TYPE_TIMESTAMP = 6;
	const TYPE_TIME = 7;
	const TYPE_NULL = 8;
	const TYPE_BARE_STRING = 9;
	const TYPE_UNESCAPED_STRING = 10;
	const TYPE_BOOLEAN = 11;
	const TYPE_ARRAY = 12;
	const TYPE_BINARY_STRING = 13;

	/**
	 * Default constructor.  Takes an SQL statement as the single argument.
	 */
	function __construct($arg0 = '') {
		$this->statement = $arg0;
	}
	
	/**
	 * Returns the con
	 * @return resource
	 */
	function getConnection() {
		if (is_null($this->con)) {
			$this->con = Controller::getInstance()->getContext()->getDatabaseConnection('default');
		}
		return $this->con;
	}
	/**
	 * Sets the con
	 * @param resource
	 */
	function setConnection($arg0) {
		$this->con = $arg0;
		return $this;
	}

	/**
	 * Prepares an SQL string so that you can use the set methods to set values in it.
	 * Set methods are called in order of the question marks (?) in the statement that
	 * you want to prepare.  Each call to a setXXX() method will add the value to an
	 * internal array which is returned when you call getPrepare();
	 * @param string $str - String you want to prepare.  All replaceable values should be
	 * delimited by a question mark.
	 */
	function setPreparedStatement($str) {
		$this->statement = $str;
	}

	/**
	 * Returns the prepared statement with the values replaced.  The statement will be
	 * escaped for any string that was passed in.  The internal string is not modified
	 * during this process, so it can be used again and again with different values.
	 * @return string
	 */
	function getPreparedStatement($con = null) {
		$retVal = "";
		$i = (array_key_exists(0,$this->values) ? 0 : 1);

		// Split the statement by question marks
		$stmt = explode('?', $this->statement);

		/*
		* Now go through the statement and add the values in between each array element, since each
		* array element was separated by a question mark
		*/
		foreach ($stmt as $arg1) {
			$retVal .= $arg1;
			if (array_key_exists($i, $this->values)) {
				$new_value = $this->values[$i];
				if (is_array($new_value)) {
					if (array_key_exists("value", $new_value)) {
						if (array_key_exists("type", $new_value)) {
							$retVal .= $this->getEscapedValue($new_value["value"], $new_value["type"]);
						} else {
							$retVal .= $this->getEscapedValue($new_value["value"], self::TYPE_STRING);
						}
					} else {
						$retVal .= $this->getEscapedValue($new_value, self::TYPE_STRING);
					}
				} else {
					$retVal .= $this->getEscapedValue($new_value, self::TYPE_STRING);
				}
			}
			$i++;
		}

		return $retVal;
	}
	
	/**
	 * Returns the statement with parameters replaced.  Useful for debugging.
	 * @return string
	 */
	function getDebugQueryString() {
		return $this->getPreparedStatement();	
	}

	/**
	 * Set the $arg0-th question mark to an integer
	 * @param integer $arg0 - Index to replace
	 * @param integer $arg1 - integer value to use
	 */
	function setInt($arg0, $arg1 = null) {
		if (!is_null($arg1)) {
			$arg1 = preg_replace("/[^0-9\.\-]/", "", $arg1);
			if (settype($arg1,"int")) {
				$this->addValue(intval($arg1), $arg0, self::TYPE_INTEGER);
			}
		} else {
			$arg0 = preg_replace("/[^0-9\.\-]/", "", $arg0);
			if (settype($arg0,"int")) {
				$this->addValue(intval($arg0), null, self::TYPE_INTEGER);
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to an integer
	 * @param integer $arg0 - Index to replace
	 * @param long $arg1 - long value to use
	 */
	function setLong($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			$arg1 = preg_replace("/[^0-9\.\-]/", "", $arg1);
			if (settype($arg1,"int")) {
				$this->addValue($arg1, $arg0, self::TYPE_LONG);
			}
		} else {
			$arg0 = preg_replace("/[^0-9\.\-]/", "", $arg0);
			if (settype($arg0,"int")) {
				$this->addValue($arg0, null, self::TYPE_LONG);
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to an string
	 * @param integer $arg0 - Index to replace
	 * @param string $arg1 - string value to use.  It will be escaped automatically
	 */
	function setString($arg0, $arg1 = null) {
		if (!is_null($arg1)) {
			if (settype($arg1,"string")) {
				$replaceQM = "/\?/i";

				$tmpStr = $arg1;
				#$tmpStr = preg_replace($replaceQM,"&#63;",$arg1);

				if (get_magic_quotes_gpc()) {
					$tmpStr = stripslashes($tmpStr);
				}
				#$tmpStr = mysql_escape_string($tmpStr);
				$this->addValue($tmpStr, $arg0, self::TYPE_STRING);
			}
		} else {
			if (settype($arg0,"string")) {
				$replaceQM = "/\?/i";

				$tmpStr = $arg0;
				#$tmpStr = preg_replace($replaceQM,"&#63;",$arg1);

				if (get_magic_quotes_gpc()) {
					$tmpStr = stripslashes($tmpStr);
				}
				#$tmpStr = mysql_escape_string($tmpStr);

				$this->addValue($tmpStr, null, self::TYPE_STRING);
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to an unescaped string.  This is useful for when
	 * you want to pass in a column name and don't want it enclosed in quotes.
	 * @param integer $arg0 - Index to replace
	 * @param string $arg1 - string value to use.
	 */
	function setBinaryString($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			$tmpStr = $arg1;
			$this->addValue($tmpStr, $arg0, self::TYPE_BINARY_STRING);
		} else {
			$tmpStr = $arg0;
			$this->addValue($tmpStr, null, self::TYPE_BINARY_STRING);
		}
	}

	/**
	 * Set the $arg0-th question mark to an unescaped string.  This is useful for when
	 * you want to pass in a column name and don't want it enclosed in quotes.
	 * @param integer $arg0 - Index to replace
	 * @param string $arg1 - string value to use.
	 */
	function setUnescapedString($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			$this->addValue($arg1, $arg0, self::TYPE_UNESCAPED_STRING);
		} else {
			$this->addValue($arg0, null, self::TYPE_UNESCAPED_STRING);
		}
	}

	/**
	 * Set the $arg0-th question mark to an unescaped string.  This is useful for when
	 * you want to pass in a column name and don't want it enclosed in quotes.
	 * @param integer $arg0 - Index to replace
	 * @param string $arg1 - string value to use.
	 */
	function setBareString($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			$this->addValue($arg1, $arg0, self::TYPE_BARE_STRING);
		} else {
			$this->addValue($arg0, null, self::TYPE_BARE_STRING);
		}
	}

	/**
	 * Set the $arg0-th question mark to an float
	 * @param integer $arg0 - Index to replace
	 * @param float $arg1 - float value to use
	 */
	function setFloat($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			$arg1 = preg_replace("/[^0-9\.\-]/", "", $arg1);
			if (settype($arg1,"float")) {
				$this->addValue(floatval($arg1), $arg0, self::TYPE_FLOAT);
			}
		} else {
			$arg0 = preg_replace("/[^0-9\.\-]/", "", $arg0);
			if (settype($arg0,"float")) {
				$this->addValue(floatval($arg0), null, self::TYPE_FLOAT);
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to an null
	 * @param integer $arg0 - Index to replace
	 */
	function setNull($arg0 = null) {
		$this->addValue("null", $arg0, self::TYPE_NULL);
	}

	/**
	 * Set the $arg0-th question mark to a boolean
	 * @param integer $arg0 - Index to replace
	 * @param boolean $arg1 - boolean value to use
	 */
	function setBoolean($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			if (settype($arg1,"boolean")) {
				$this->addValue($arg1, $arg0, self::TYPE_BOOLEAN);
			}
		} else {
			if (settype($arg0,"boolean")) {
				$this->addValue($arg0, null, self::TYPE_BOOLEAN);
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to a timestamp.  It does this by parsing the
	 * input string, then applying a format to it.  If the input argument cannot be
	 * parsed, the mysql function `current_timestamp` is used instead.
	 * @param integer $arg0 - Index to replace
	 * @param boolean $arg1 - boolean value to use
	 */
	function setTimestamp($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			if (settype($arg1,"string")) {
				if (($timestamp = strtotime($arg1)) === -1) {
					$this->addValue("current_timestamp", $arg0, self::TYPE_TIMESTAMP);
				} else {
					$this->addValue(date("Y-m-d H:i:s", $timestamp), $arg0, self::TYPE_TIMESTAMP);
				}
			}
		} else {
			if (settype($arg0,"string")) {
				if (($timestamp = strtotime($arg0)) === -1) {
					$this->addValue("current_timestamp", null, self::TYPE_TIMESTAMP);
				} else {
					$this->addValue(date("Y-m-d H:i:s", $timestamp), null, self::TYPE_TIMESTAMP);
				}
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to a date.  It does this by parsing the
	 * input string, then applying a format to it.  If the input argument cannot be
	 * parsed, the mysql function `current_date` is used instead.  Note that setting a date will NOT add a time to
	 * the format.
	 * @param integer $arg0 - Index to replace
	 * @param boolean $arg1 - boolean value to use
	 */
	function setDate($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			if (settype($arg1,"string")) {
				if ($arg1 == "") {
					$this->addValue("current_date", $arg0, self::TYPE_DATE);
				} else {
					if (($timestamp = strtotime($arg1)) === -1) {
						$this->addValue("current_date", $arg0, self::TYPE_DATE);
					} else {
						$this->addValue(date("Y-m-d", $timestamp), $arg0, self::TYPE_DATE);
					}
				}
			}
		} else {
			if (settype($arg0,"string")) {
				if ($arg0 == "") {
					$this->addValue("current_date", null, self::TYPE_DATE);
				} else {
					if (($timestamp = strtotime($arg0)) === -1) {
						$this->addValue("current_date", null, self::TYPE_DATE);
					} else {
						$this->addValue(date("Y-m-d", $timestamp), null, self::TYPE_DATE);
					}
				}
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to a time.  It does this by parsing the
	 * input string, then applying a format to it.  If the input argument cannot be
	 * parsed, the mysql function `current_time` is used instead.  Note that setting a date will NOT add a time to
	 * the format.
	 * @param integer $arg0 - Index to replace
	 * @param boolean $arg1 - boolean value to use
	 */
	function setTime($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			if (settype($arg1,"string")) {
				if (($timestamp = strtotime($arg1)) === -1) {
					$this->addValue("current_time", $arg0, self::TYPE_TIME);
				} else {
					$this->addValue(date("H:i:s", $timestamp), $arg0, self::TYPE_TIME);
				}
			}
		} else {
			if (settype($arg0,"string")) {
				if (($timestamp = strtotime($arg0)) === -1) {
					$this->addValue("current_time", null, self::TYPE_TIME);
				} else {
					$this->addValue(date("H:i:s", $timestamp), null, self::TYPE_TIME);
				}
			}
		}
	}

	/**
	 * Set the $arg0-th question mark to a year.  It does this by parsing the
	 * input string, then applying a format to it.  If the input argument cannot be
	 * parsed, the mysql function `year(current_time)` is used instead.  Note that setting a date will NOT add a time to
	 * the format.
	 * @param integer $arg0 - Index to replace
	 * @param boolean $arg1 - boolean value to use
	 */
	function setYear ($arg0,$arg1 = null) {
		if (!is_null($arg1)) {
			if (settype($arg1,"string")) {
				if (($timestamp = strtotime($arg1)) === -1) {
					$this->addValue("year(current_time)", $arg0, self::TYPE_TIME);
				} else {
					$this->addValue(date("Y", $timestamp), $arg0, self::TYPE_TIME);
				}
			}
		} else {
			if (settype($arg0,"string")) {
				if (($timestamp = strtotime($arg0)) === -1) {
					$this->addValue("year(current_time)", null, self::TYPE_TIME);
				} else {
					$this->addValue(date("Y", $timestamp), null, self::TYPE_TIME);
				}
			}
		}
	}

	/**
	 * Sets an array using the implode function
	 *
	 * @param integer $arg0
	 * @param array $arg1
	 */
	function setArray($arg0, $arg1 = null) {
		if(!is_null($arg1)) {
			$this->addValue($arg1, $arg0, self::TYPE_ARRAY);
		} else {
			$this->addValue($arg0, null, self::TYPE_ARRAY);
		}
	}

	/**
	 * Adds a value to the internal value array.  Also sets the type, which is useful
	 * for determining whether to escape the value with mysql_escape_string or not.
	 * @param string $value
	 * @param integer $type
	 */
	protected function addValue($value, $index = null, $type = 0) {
		if (!is_array($this->values)) {
			$this->values = array();
		}
		$new_value = array("value" => $value, "type" => $type);
		if (is_null($index)) {
			$this->values[] = $new_value;
		} else {
			$this->values[$index] = $new_value;
		}
	}

	/**
	 * Escapes a string based on it's type
	 * @param value $value
	 * @param type $type
	 * @return string
	 */
	protected function getEscapedValue($value, $type) {
		$ret_val = "";
		switch ($type) {
			case self::TYPE_FLOAT:
			case self::TYPE_INTEGER:
			case self::TYPE_LONG:
			case self::TYPE_NULL:
			case self::TYPE_BARE_STRING:
				return $value;
				break;
			case self::TYPE_BOOLEAN:
			case self::TYPE_DATE:
			case self::TYPE_TIMESTAMP:
			case self::TYPE_TIME:
			case self::TYPE_UNESCAPED_STRING:
				$ret_val = "'" . $value . "'";
				break;
			case self::TYPE_BINARY_STRING:
			case self::TYPE_STRING:
				$ret_val = "'" . mysql_real_escape_string($value, $this->getConnection()) . "'";
				break;
			case self::TYPE_ARRAY:
				if (is_array($value)) {
					$arr = $value;
					foreach ($arr as $key => $val) {
						$arr[$key] = mysql_real_escape_string($val, $this->getConnection());
					}
					$ret_val = "'" . implode("','", $arr) . "'";
				} else {
					$arr = "''";
				}
				break;
			default:
				$ret_val = "'" . mysql_real_escape_string($value, $this->getConnection()) . "'";
				break;

		}
		return $ret_val;

	}
}
?>
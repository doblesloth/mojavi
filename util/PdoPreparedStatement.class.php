<?php
/**
 * This class extends the PreparedStatement class in order to add functionality for using key names.
 * @author Mark Hobson
 */
class PdoPreparedStatement extends PreparedStatement {
	
	private $form;
	
	/**
	 * Returns the prepared statement with the values replaced.  The statement will be
	 * escaped for any string that was passed in.  The internal string is not modified
	 * during this process, so it can be used again and again with different values.
	 * @return string
	 */
	function getPreparedStatement($con = null) {
		$retVal = $this->statement;
		
		foreach ($this->values as $name => $value) {
			if ($value['type'] == self::TYPE_BARE_STRING) {
				$retVal = str_replace("<<" . $name . ">>", $value['value'], $retVal);
			} else if ($value['type'] == self::TYPE_UNESCAPED_STRING) {
				$retVal = str_replace("<<" . $name . ">>", $value['value'], $retVal);
			} else if ($value['type'] == self::TYPE_ARRAY) {
				$retVal = str_replace("<<" . $name . ">>", "'" . implode("','", $value['value']) . "'", $retVal);
			} else {
				if (strpos($retVal, "<<" . $name . ">>") !== false) {
					$retVal = str_replace("<<" . $name . ">>", ":" . $name, $retVal);	
				} else {
					unset($this->values[$name]);
				}
			}	
		}
		
		/* @var $dbh PDOStatement */
		if (is_null($con)) {
			$con = Controller::getInstance()->getContext()->getDatabaseConnection('default');
		}
		
		if (is_null($con)) {
			LoggerManager::error(__METHOD__ . " :: " . var_export($con, true));
			throw new Exception('Cannot instantiate PDO object with query: ' . $retVal);
		}
		$dbh = $con->prepare($retVal);
		foreach ($this->values as $name => $value) {
			if ($value['type'] == self::TYPE_FLOAT) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_INT);
			} else if ($value['type'] == self::TYPE_INTEGER) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_INT);
			} else if ($value['type'] == self::TYPE_LONG) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_INT);
			} else if ($value['type'] == self::TYPE_DATE) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_STR);
			} else if ($value['type'] == self::TYPE_TIMESTAMP) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_STR);
			} else if ($value['type'] == self::TYPE_TIME) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_STR);
			} else if ($value['type'] == self::TYPE_NULL) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_NULL);
			} else if ($value['type'] == self::TYPE_BOOLEAN) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_BOOL);
			} else if ($value['type'] == self::TYPE_BARE_STRING) {
				continue;
			} else if ($value['type'] == self::TYPE_UNESCAPED_STRING) {
				continue;
			} else if ($value['type'] == self::TYPE_ARRAY) {
				continue;
			} else if ($value['type'] == self::TYPE_BINARY_STRING) {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_STR);
			} else {
				$dbh->bindValue(":" . $name, $value["value"], PDO::PARAM_STR);
			}
		}
		
		return $dbh;
	}
	
	/**
	 * Returns the statement with parameters replaced.  Useful for debugging.
	 * @return string
	 */
	function getDebugQueryString() {
		$retVal = $this->statement;
		
		foreach ($this->values as $name => $value) {
			if ($value['type'] == self::TYPE_BARE_STRING) {
				$retVal = str_replace("<<" . $name . ">>", $value['value'], $retVal);
			} else if ($value['type'] == self::TYPE_UNESCAPED_STRING) {
				$retVal = str_replace("<<" . $name . ">>", $value['value'], $retVal);
			} else if ($value['type'] == self::TYPE_ARRAY) {
				$retVal = str_replace("<<" . $name . ">>", "'" . implode("','", $value['value']) . "'", $retVal);
			} else {
				if (strpos($retVal, "<<" . $name . ">>") !== false) {
					$retVal = str_replace("<<" . $name . ">>", ":" . $name, $retVal);	
				} else {
					unset($this->values[$name]);
				}
			}	
		}
		
		// Loop Through And Replace <<$name>> with $value
		foreach ($this->values as $name => $value) {
			if ($value['type'] == self::TYPE_FLOAT) {
				$retVal = str_replace(":" . $name, $value["value"], $retVal);
			} else if ($value['type'] == self::TYPE_INTEGER) {
				$retVal = str_replace(":" . $name, $value["value"], $retVal);
			} else if ($value['type'] == self::TYPE_LONG) {
				$retVal = str_replace(":" . $name, $value["value"], $retVal);
			} else if ($value['type'] == self::TYPE_DATE) {
				$retVal = str_replace(":" . $name, "'" . str_replace("'", "\'", $value["value"]) . "'", $retVal);
			} else if ($value['type'] == self::TYPE_TIMESTAMP) {
				$retVal = str_replace(":" . $name, "'" . str_replace("'", "\'", $value["value"]) . "'", $retVal);
			} else if ($value['type'] == self::TYPE_TIME) {
				$retVal = str_replace(":" . $name, "'" . str_replace("'", "\'", $value["value"]) . "'", $retVal);
			} else if ($value['type'] == self::TYPE_NULL) {
				$retVal = str_replace(":" . $name, $value["value"], $retVal);
			} else if ($value['type'] == self::TYPE_BOOLEAN) {
				$retVal = str_replace(":" . $name, $value["value"], $retVal);
			} else if ($value['type'] == self::TYPE_BARE_STRING) {
				continue;
			} else if ($value['type'] == self::TYPE_UNESCAPED_STRING) {
				continue;
			} else if ($value['type'] == self::TYPE_ARRAY) {
				continue;
			} else if ($value['type'] == self::TYPE_BINARY_STRING) {
				$retVal = str_replace(":" . $name, "'" . str_replace("'", "\'", $value["value"]) . "'", $retVal);
			} else {
				$retVal = str_replace(":" . $name, "'" . str_replace("'", "\'", $value["value"]) . "'", $retVal);
			}

		}

		return $retVal;	
	}
	
	public function setForm($arg0) {
		$this->form = $arg0;
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
				$ret_val = $value;
				break;
			case self::TYPE_ARRAY:
				if (is_array($value)) {
					$arr = $value;
					foreach ($arr as $key => $val) {
						$arr[$key] = $val;
					}
					$ret_val = "'" . implode("','", $arr) . "'";
				} else {
					$arr = "''";
				}
				break;
			default:
				$ret_val = $value;
				break;

		}
		return $ret_val;

	}
}
?>
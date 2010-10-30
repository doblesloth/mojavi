<?php
	/**
	 * Contains functions to work with a PHP-generated error
	 * @author Mark Hobson
	 */
	class BasicPHPErrorForm extends DateRangeForm {

		private $code_error_id;
		private $system_id;
		private $message;
		private $line_number;
		private $error_level;
		private $error_message;
		private $filename;

		private $count;
		private $system_details;

		/**
		 * Returns the code_error_id field.
		 * @return integer
		 */
		function getCodeErrorId() {
			if (is_null($this->code_error_id)) {
				$this->code_error_id = 0;
			}
			return $this->code_error_id;
		}
		/**
		 * Sets the code_error_id field.
		 * @param integer $arg0
		 */
		function setCodeErrorId($arg0) {
			$this->code_error_id = $arg0;
			return $this;
		}

		/**
		 * Returns the system_id field.
		 * @return integer
		 */
		function getSystemId() {
			if (is_null($this->system_id)) {
				$this->system_id = 0;
			}
			return $this->system_id;
		}
		/**
		 * Sets the system_id field.
		 * @param integer $arg0
		 */
		function setSystemId($arg0) {
			$this->system_id = $arg0;
			return $this;
		}

		/**
		 * Returns the message
		 * @return string
		 */
		function getMessage() {
			if (is_null($this->message) && !$this->repopulate()) {
				$this->message = "";
			}
			return $this->message;
		}

		/**
		 * Sets the message
		 * @param string
		 */
		function setMessage($arg0) {
			$this->parseError($arg0);
			$this->message = $arg0;
			return $this;
		}

		/**
		 * Sets the message
		 * @param string
		 */
		function setMsg($arg0) {
			$this->setMessage($arg0);
			return $this;
		}

		/**
		 * Parses the error message into it's individual parts
		 * @return boolean
		 */
		function parseError($msg) {
			$matches = array();
			if (preg_match("/([\s\S]*?): ([\s\S]*) in ([\s\S]*) on line ([0-9]*)/", $msg, $matches)) {
				if (array_key_exists(1, $matches)) {
					$this->setErrorLevel($matches[1]);
				}
				if (array_key_exists(2, $matches)) {
					$this->setErrorMessage($matches[2]);
				}
				if (array_key_exists(3, $matches)) {
					$this->setFilename($matches[3]);
				}
				if (array_key_exists(4, $matches)) {
					$this->setLineNumber($matches[4]);
				}
			}
			return $this;
		}

		/**
		 * Returns the filename
		 * @return string
		 */
		function getFilename() {
			if (is_null($this->filename) && !$this->repopulate()) {
				$this->filename = "";
			}
			return $this->filename;
		}

		/**
		 * Sets the filename
		 * @param string
		 */
		function setFilename($arg0) {
			$this->filename = $arg0;
			return $this;
		}

		/**
		 * Returns the error_message
		 * @return string
		 */
		function getErrorMessage() {
			if (is_null($this->error_message) && !$this->repopulate()) {
				$this->error_message = "";
			}
			return $this->error_message;
		}

		/**
		 * Sets the error_message
		 * @param string
		 */
		function setErrorMessage($arg0) {
			$this->error_message = $arg0;
			return $this;
		}

		/**
		 * Returns the error_level
		 * @return string
		 */
		function getErrorLevel() {
			if (is_null($this->error_level) && !$this->repopulate()) {
				$this->error_level = "";
			}
			return $this->error_level;
		}

		/**
		 * Sets the error_level
		 * @param string
		 */
		function setErrorLevel($arg0) {
			$this->error_level = $arg0;
			return $this;
		}

		/**
		 * Returns the line_number
		 * @return integer
		 */
		function getLineNumber() {
			if (is_null($this->line_number) && !$this->repopulate()) {
				$this->line_number = 0;
			}
			return $this->line_number;
		}

		/**
		 * Sets the line_number
		 * @param integer
		 */
		function setLineNumber($arg0) {
			$this->line_number = $arg0;
			return $this;
		}

		/**
		 * Returns the count field.
		 * @return integer
		 */
		function getCount() {
			if (is_null($this->count)) {
				$this->count = 0;
			}
			return $this->count;
		}
		/**
		 * Sets the count field.
		 * @param integer $arg0
		 */
		function setCount($arg0) {
			$this->count = $arg0;
			return $this;
		}
	}

?>

<?php
	/**
	* BasicForm is the base class for ALL forms used.  It has a single function set (Id).  Every
	* table should have an auto_increment field called id for conformity.  BasicForm has support
	* for the Errors object and populate(Array).  Subclasses will be auto-populated as long as they
	* contain getters and setters that match the criteria of the populate() method.
	*
	* BasicForm can be instantiated with an errors object for ease of use.
	**/
class Form extends MojaviForm {

		const VALIDATION_LEVEL_FULL = 1;
		
		protected $id;
		protected $created_time;
		protected $is_deleted;
		protected $deleted_time;
		protected $modified;
		private $shell;
		private $forward;
		private $name;
		private $validation_level;
		private $override_account_id;
		
		/*   VARS FOR REPOPULATION   */
		private $populated;
		private $repopulated;
		private $module_name;
		private $model_name;

		/**
		 * returns the id.  If your dataset is setup so that the primary key (id) is named, such as
		 * customer_id, then this function should be overridden to alias that function.  I.e.:
		 * function getId() {
		 * 		return $this->getCustomerId();
		 * }
		 * @return string
		 */
		function getId() {
		    if (is_null($this->id)) {
		        $this->id = 0;
		    }
		    return $this->id;
		}

		/**
		 * Returns the encoded id.  This encodes the id with the IntegerEncoder class.
		 * @return string
		 */
		function getEncodedId() {
			if (MO_DEBUG) {
				return $this->getId();
			} else {
				return IntegerTableEncoder::encodeInt($this->getId());
			}
		}

		/**
		 * sets the id.  If your dataset is setup so that the primary key (id) is named, such as
		 * customer_id, then this function should be overridden to alias that function.  I.e.:
		 * function setId($arg0) {
		 * 		$this->setCustomerId($arg0);
		 * }
		 * @param string $arg0
		 */
		function setId($arg0) {
			if (is_numeric($arg0)) {
			    $this->id = $arg0;
			} else if (!is_array($arg0)) {
				$this->id = IntegerTableEncoder::decodeInt($arg0);
			}
			return $this;
		}		
		
		/**
		 * Returns the validation_level
		 * @return integer
		 */
		function getValidationLevel() {
			if (is_null($this->validation_level)) {
				$this->validation_level = self::VALIDATION_LEVEL_FULL;
			}
			return $this->validation_level;
		}
		
		/**
		 * Sets the validation_level
		 * @param integer
		 */
		function setValidationLevel($arg0) {
			$this->validation_level = $arg0;
			return $this;
		}
		
		/**
		 * Returns the created field.
		 * @return string
		 */
		function getCreated() {
			return date("m/d/Y", strtotime($this->getCreatedTime()));
		}
		
		/**
		 * Alias for getIsDeleted
		 * @return boolean
		 */
		function IsDeleted() {
			return $this->getIsDeleted();
		}
		
		/**
		 * Returns the is_deleted
		 * @return boolean
		 */
		function getIsDeleted() {
			if (is_null($this->is_deleted)) {
				$this->is_deleted = false;
			}
			return $this->is_deleted;
		}
		
		/**
		 * Sets the is_deleted
		 * @param boolean
		 */
		function setIsDeleted($arg0) {
			$this->is_deleted = $arg0;
			return $this;
		}
		
		/**
		 * Returns the deleted_time
		 * @return string
		 */
		function getDeletedTime() {
			if (is_null($this->deleted_time)) {
				$this->deleted_time = "0000-00-00";
			}
			return $this->deleted_time;
		}
		
		/**
		 * Sets the deleted_time
		 * @param string
		 */
		function setDeletedTime($arg0) {
			$this->deleted_time = $arg0;
			return $this;
		}
		
		/**
		 * Returns the created_time field.
		 * @return string
		 */
		function getCreatedTime() {
			if (is_null($this->created_time)) {
				$this->created_time = date("Y-m-d H:i:s");
			}
			return $this->created_time;
		}
		/**
		 * Sets the created_time field.
		 * @param string $arg0
		 */
		function setCreatedTime($arg0) {
			$this->created_time = $arg0;
			return $this;
		}
		
		/**
		 * Returns the modified field.
		 * @return string
		 */
		function getModified() {
			if (is_null($this->modified)) {
				$this->modified = date("Y-m-d H:i:s");
			}
			return $this->modified;
		}
		/**
		 * Sets the modified field.
		 * @param string $arg0
		 */
		function setModified($arg0) {
			$this->modified = $arg0;
			return $this;
		}
		
		/**
		 * Returns the forward
		 * @return string
		 */
		function getForward() {
			if (is_null($this->forward)) {
				$this->forward = "";
			}
			return $this->forward;
		}
		
		/**
		 * Sets the forward
		 * @param string
		 */
		function setForward($arg0) {
			$this->forward = $arg0;
			return $this;
		}
		
		/**
		 * Returns the shell
		 * @return string
		 */
		function getShell() {
			if (is_null($this->shell)) {
				$this->shell = "index";
			}
			return $this->shell;
		}
		
		/**
		 * Sets the shell
		 * @param string
		 */
		function setShell($arg0) {
			$this->shell = $arg0;
			return $this;
		}
		
		/**
		 * Returns the override_account_id
		 * @return boolean
		 */
		function getOverrideAccountId() {
			if (is_null($this->override_account_id)) {
				$this->override_account_id = false;
			}
			return $this->override_account_id;
		}
		
		/**
		 * Sets the override_account_id
		 * @param boolean
		 */
		function setOverrideAccountId($arg0) {
			$this->override_account_id = $arg0;
			return $this;
		}

		/**
		* Attempts to validate this form.  If any errors occur, they are
		* populated in the internal errors object.
		* @return boolean - true if validation succeeds
		*/
		function validate() {
			return true;
		}

		/**
		* Resets a form.  This is mostly used with certain form elements (like a checkbox).
		* If a checkbox is checked, it is passed in the request, if it is not checked, then
		* nothing is passed.  By resetting a checkbox property to false here, then every
		* request it is set to false, UNLESS a value is passed in - which is the way it's
		* supposed to work.
		* @return boolean - true if validation succeeds
		*/
		function reset() {
			return true;
		}
		
		/**
		 * Sets the populated property
		 * @param integer $arg0
		 */
		function setPopulated($arg0) {
			$this->populated = $arg0;
			return $this;	
		}
	}
?>
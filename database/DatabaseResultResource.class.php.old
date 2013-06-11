<?php
/**
 * DatabaseResultResource
 * This Class Is Meant To Be Setup With A Blank Form And A MySQL Result Resource.
 * The Purpose Is To Not Store All Returned Rows In An Array, But To Loop Through The Result Resource One At A Time.
 * Usage:
 *    $db_result_resource = new DatabaseResultResource();
 *    $db_result_resource->setForm($blank_form);
 *    $db_result_resource->setResultResource($mysql_result);
 *    ...
 *    while($form = $db_result_resource->getNextRow()) {
 *       ...
 *    }
 * 
 * Alternately you can use the object like an array in a foreach statement:
 * 	  $db_result_resource = new DatabaseResultResource();
 * 	  $db_result_resource->setForm($blank_form);
 * 	  $db_result_resource->setResultResource($mysql_result);
 *    ...
 *    foreach($db_result_resource as $key => $form) {
 *       ...
 *    }
 */
class DatabaseResultResource implements Iterator, Countable, ArrayAccess {
	/*   Main Vars   */
	private $current_row;
	private $form;
	private $result_resource;
	private $row_iteration;

	/******************\
	*   MAIN METHODS   *
	\******************/
	/**
	 * Returns a form containing the next row of data.
	 * @return object containing next row of data
	 */
	function getNextRow() {
		$ret_val = false;
		// Make Sure Resource Is Valid
		if(is_resource($this->getResultResource())) {
			// Pull Out Assoc Array
			if($row = mysql_fetch_assoc($this->getResultResource())) {
				$form = $this->getForm();
				// Make Sure $form is an object
				if(is_object($form)) {
					// Clone Form And Populate
					$ret_val = clone $form;
					$ret_val->populate($row, false);
					if(is_null($this->getRowIteration())) {
						$this->setRowIteration(0);
					} else {
						$this->setRowIteration($this->getRowIteration() + 1);
					}
				}
			}
		}
		$this->setCurrentRow($ret_val);
		return $ret_val;
	}

	/**
	 * Retrieves the number of rows in the result resource.
	 * @return int
	 */
	function getCount() {
		$ret_val = 0;
		if(is_resource($this->getResultResource())) {
			$ret_val = mysql_num_rows($this->getResultResource());
		}
		return $ret_val;
	}

	/**
	 * Retrieves the Errors object.
	 * @return Errors
	 */
	function getErrors() {
		return Controller::getInstance()->getContext()->getErrors();
	}

	/**
	 * Returns an array of all the forms in this result set.
	 * If a keyFunction is given, then the returned array will be indexed by the return value of 
	 * that function for each form.  If not, the array will be numerically indexed starting at 0.
	 *
	 * @param string $keyFunction
	 * @return array
	 */
	public function getArray($keyFunction = null){

		$returnVal = array();
		foreach($this as $key => $form) {

			if(method_exists($form,$keyFunction)) {
				$returnVal[$form->$keyFunction()]=$form;
			} else {
				$returnVal[]=$form;
			}

		}
		return $returnVal;
	}

	/*************************\
	*   GETTERS AND SETTERS   *
	\*************************/
	/**
	 * Returns the current_row field.
	 * @return object
	 */
	function getCurrentRow() {
		$form = $this->getForm();
		if(!(is_object($this->current_row) && $this->current_row instanceof $form)) {
			$this->getNextRow();
		}
		return $this->current_row;
	}
	/**
	 * Sets the current_row field.
	 * @param object $arg0
	 */
	function setCurrentRow($arg0) {
		$this->current_row = $arg0;
	}

	/**
	 * Returns the form field.
	 * @return object
	 */
	function getForm() {
		return $this->form;
	}
	/**
	 * Sets the form field.
	 * @param object $arg0
	 */
	function setForm($arg0) {
		$this->form = $arg0;
	}

	/**
	 * Returns the result_resource field.
	 * @return database result resource
	 */
	function getResultResource() {
		return $this->result_resource;
	}
	/**
	 * Sets the result_resource field.
	 * @param database result resource $arg0
	 */
	function setResultResource($arg0) {
		$this->result_resource = $arg0;
	}

	/**
	 * Returns the row_iteration field.
	 * @return int
	 */
	function getRowIteration() {
		return $this->row_iteration;
	}
	/**
	 * Sets the row_iteration field.
	 * @param int $arg0
	 */
	function setRowIteration($arg0) {
		$this->row_iteration = $arg0;
	}

	/**********************\
	*   ITERATOR METHODS   *
	\**********************/
	/**
	 * Rewinds the mysql result resource
	 */
	public function rewind() {
		if($this->getCount() > 0) {
			mysql_data_seek($this->getResultResource(), 0);
		}
		$this->setCurrentRow(null);
		$this->setRowIteration(null);
	}
	/**
	 * Retrieves the current row
	 * @return object
	 */
	public function current() {
		return $this->getCurrentRow();
	}
	/**
	 * Retrieves the key for the current row
	 * @return int
	 */
	public function key() {
		return $this->getRowIteration();
	}
	/**
	 * Retrieves the next row
	 * @return object
	 */
	public function next() {
		return $this->getNextRow();
	}
	/**
	 * Returns true if the current row is valid
	 * @return bool
	 */
	public function valid() {
		$form = $this->getForm();
		$ret_val = ( is_object($this->getCurrentRow()) && $this->getCurrentRow() instanceof $form );
		return $ret_val;
	}

	/**********************\
	*   COUNTABLE METHOD   *
	\**********************/
	/**
	 * Returns the count.
	 * @return int
	 */
	public function count() {
		return $this->getCount();
	}

	/***************************\
	*    ARRAY ACCESS METHODS   *
	\***************************/

	/**
	 * returns true or false if the given offset exists
	 *
	 * @param int $offset
	 * @return bool
	 */
	function offsetExists($offset){

		if(mysql_num_rows($this->getResultResource()) > $offset){
			return TRUE;
		} else {
			return FALSE;
		}

	}

	function offsetGet($offset){
		$retval = false;
		if($this->offsetExists($offset)) {
			$form = $this->getForm();
			if(is_object($form)) {
				$retval = clone $form;
				$rs = $this->getResultResource();
				mysql_data_seek($rs, $offset);
				$row = mysql_fetch_assoc($rs);
				$retval->populate($row);
				$current = $this->getRowIteration();
				if (is_null($current)) {
					$current = 0;
				}
				mysql_data_seek($rs, $current);
			}
		}
		return $retval;
	}

	function offsetSet($offset, $value){

	}

	/**
	* empty function to comply with the requirements of the interface
	*
	* @param unknown_type $offset
	*/
	function offsetUnset($offset){

	}
}
?>

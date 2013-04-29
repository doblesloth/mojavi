<?php
/**
 * BasicModel is the base class for all Models.  It is only an abstract class but will
 * reinforce the need of basic functions (add, delete, update, count, query).  Some
 * subclasses may want to add additional functions like performSearch(Form arg0) and
 * performQueryAllByAccount(Form arg0).
 *
 * All the functions should take a Form (or subclass of Form) as an argument for
 * conformity.
 **/
abstract class BasicFieldModel extends Model {
	private $errors;

	const FULL = '*';

	function __construct($arg0 = null) {
		// Nothing to do here
	}

	/**
	 * Abstract method used to perform a query for an individual record
	 * @param Form $arg0
	 * @return Form
	 */
	abstract function performQuery(Form $arg0, $fields = '');

	/**
	 * Abstract method to perform an insert into of a record
	 * @param Form $arg0
	 * @return integer
	 */
	abstract function performInsert(Form $arg0);

	/**
	 * Abstract method to perform an update of a record
	 * @param Form $arg0
	 * @return integer
	 */
	abstract function performUpdate(Form $arg0);

	/**
	 * Abstract method to perform a deletion of a record
	 * @param Form $arg0
	 * @return integer
	 */
	abstract function performDelete(Form $arg0);

	/**
	 * Abstract method to perform a query of all the records.  A PageListForm should
	 * normally be passed into so that a limit can be performed
	 * @param Form $arg0
	 * @return array
	 */
	abstract function performQueryAll(Form $arg0, $fields = '');

	/**
	 * Abstract method to perform a count of all the records.  A PageListForm should
	 * normally be passed into so that a limit can be performed.
	 * @param Form $arg0
	 * @return integer
	 */
	abstract function performCountAll(Form $arg0);

	/**
	 * Abstract method to repopulate the passed in form.
	 * @param Form $arg0
	 */
	abstract function repopulate(Form $arg0);

	/**
	 * Returns the errors object.  Normally you want to setup an error object beforehand and pass
	 * it to all the forms and models that you use so that you can collect all the errors
	 * @return Errors
	 */
	function getErrors() {
		return $this->getContext()->getErrors();
	}
	
	/**
	* Retrieves the currently logged in user details
	*/
	public function getUserDetails() {
		if (defined("MO_USER_NAMESPACE")) {
			return $this->getContext()->getUser()->getAttribute(MO_USER_NAMESPACE);
		} else {
			return $this->getContext()->getUser()->getAttribute("userForm");
		}
	}
}
?>
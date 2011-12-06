<?php
/**
 * BasicReportModel is the base class for all Report Models.  It doesn't contain the same abstract 
 * methods that BasicModel has because reports should use their own functions to get the data that 
 * they need in the most efficient way possible.
 *
 * All the functions should take a BasicForm (or subclass of BasicForm) as an argument for
 * conformity.
 */
abstract class BasicReportModel extends Model {

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
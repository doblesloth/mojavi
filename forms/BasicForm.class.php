<?php 
	/**
	* BasicForm is the base class for ALL forms used.  It has a single function set (Id).  Every 
	* table should have an auto_increment field called id for conformity.  BasicForm has support 
	* for the Errors object and populate(Array).  Subclasses will be auto-populated as long as they 
	* contain getters and setters that match the criteria of the populate() method.
	* 
	* BasicForm can be instantiated with an errors object for ease of use. 
	**/
	class BasicForm extends CommonForm {
	
	}
?>

<?php

/**
 * BasicCountryStateForm is used to store country information.
 *
 * @version $Id$
 * @copyright 2005 
 **/

class BasicCountryStateForm extends BasicForm {

	/**
	 * State Name
	 * @var string
	 */
	private $name;
	/**
	 * State Abbreviation
	 * @var string
	 */
	private $abbreviation;
	/**
	 * Country Code State Belongs To
	 * @var string
	 */
	private $country;
	/**
	 * Active - Whether State is active or not
	 * @var bool
	 */
	private $active;
	
	/*************************\
	*   GETTERS AND SETTERS   *
	\*************************/
	/**
	 * Returns the name field.
	 * @return string
	 */
	function getName() {
		if (is_null($this->name)) {
			$this->name = "";
		}
		return $this->name;
	}
	/**
	 * Sets the name field.
	 * @param string $arg0
	 */
	function setName($arg0) {
		$this->name = $arg0;
	}
	
	/**
	 * Returns the abbreviation field.
	 * @return string
	 */
	function getAbbreviation() {
		if (is_null($this->abbreviation)) {
			$this->abbreviation = "";
		}
		return $this->abbreviation;
	}
	/**
	 * Sets the abbreviation field.
	 * @param string $arg0
	 */
	function setAbbreviation($arg0) {
		$this->abbreviation = $arg0;
	}
	
	/**
	 * Returns the country field.
	 * @return string
	 */
	function getCountry() {
		if (is_null($this->country)) {
			$this->country = "";
		}
		return $this->country;
	}
	/**
	 * Sets the country field.
	 * @param string $arg0
	 */
	function setCountry($arg0) {
		$this->country = $arg0;
	}
	
	/**
	 * Returns the active field.
	 * @return bool
	 */
	function getActive() {
		if (is_null($this->active)) {
			$this->active = true;
		}
		return $this->active;
	}
	/**
	 * Sets the active field.
	 * @param mixed $arg0
	 */
	function setActive($arg0) {
		if(is_bool($arg0)) {
			$this->active = $arg0;
		} elseif($arg0 == "1") {
			$this->active = true;
		} elseif($arg0 == "0") {
			$this->active = false;
		}
	}
	
	/**
	 * Populates this form.
	 * Accepts an array or an object of type DOMElement
	 * @param mixed $arg0
	 */
	function populate($arg0) {
		if(is_array($arg0)) {
			if (array_key_exists('name',$arg0)) {
				$this->setName($arg0['name']);
			}
			if (array_key_exists('abbreviation',$arg0)) {
				$this->setAbbreviation($arg0['abbreviation']);
			}
			if (array_key_exists('country',$arg0)) {
				$this->setCountry($arg0['country']);
			}
			if (array_key_exists('active',$arg0)) {
				$this->setActive($arg0['active']);
			}
		} elseif($arg0 instanceof DOMElement) {
			$this->setName($arg0->getAttribute('name'));
			$this->setAbbreviation($arg0->getAttribute('abbreviation'));
			$this->setCountry($arg0->getAttribute('country'));
			$this->setActive($arg0->getAttribute('active'));
		}
	}

}

?>
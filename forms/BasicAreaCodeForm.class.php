<?php

/**
 * AreacodeForm is used to interface with areacodes.
 *
 * @version $Id$
 * @copyright 2005
 **/


 class BasicAreaCodeForm extends BasicForm {

	private $code;
	private $state;
	private $active;
	
	/**
	 * Returns the active
	 * @return boolean
	 */
	function isActive() {
		if (is_null($this->active)) {
			$this->active = false;
		}
		return $this->active;
	}
	
	/**
	 * Sets the active
	 * @param boolean
	 */
	function setActive($arg0) {
		$this->active = $arg0;
	}
	
	/**
	 * Returns the state
	 * @return string
	 */
	function getState() {
		if (is_null($this->state)) {
			$this->state = "";
		}
		return $this->state;
	}
	
	/**
	 * Sets the state
	 * @param string
	 */
	function setState($arg0) {
		$this->state = $arg0;
	}
	
	/**
	 * Returns the code
	 * @return string
	 */
	function getCode() {
		if (is_null($this->code)) {
			$this->code = "";
		}
		return $this->code;
	}
	
	/**
	 * Sets the code
	 * @param string
	 */
	function setCode($arg0) {
		$this->code = $arg0;
	}
}
?>
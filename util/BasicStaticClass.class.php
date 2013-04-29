<?php
class BasicStaticClass {
	/*   MAIN VARS   */
	
	/*************************\
	*   GETTERS AND SETTERS   *
	\*************************/	
	/**
	 * Returns the context field.
	 * @return Context
	 */
	static function getContext() {
		return Controller::getInstance()->getContext();
	}
	/**
	 * Sets the context field.
	 * @param Context $arg0
	 */
	static function setContext($arg0) {
	}
}
?>
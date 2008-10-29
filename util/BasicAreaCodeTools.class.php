<?php

/**
 * AreacodeXMLModel is used to interface with the list of available Areacodes.  Currently it can run
 * off a database or an xml stream.
 *
 * @version $Id$
 * @copyright 2005
 **/

class BasicAreaCodeTools {
 	
 	/**
 	 * Contains the dom object.
 	 * @var DOMDocument
 	 */
	private $dom;
	
	private static $instance = null;

	/**
	 * Retrieves the dom object containing the xml.  Uses MO_APP_DIR/util/country-defs.xml by default.  If 
	 * MO_LIB_DIR/xml/country-defs.xml exists, it will use that file instead.
	 * @return DOMDocument
	 */
	function getDom() {
		if(is_null($this->dom)) {
			$dom = new DOMDocument();
			$xml_filename = MO_APP_DIR . "/util/areacode-defs.xml";
			if (file_exists(MO_LIB_DIR . "/xml/areacode-defs.xml")) {
				$xml_filename = MO_LIB_DIR . "/xml/areacode-defs.xml";	
			}
			
			if(!$dom->load($xml_filename)) {
				$this->getErrors()->addError("error", new Error("Couldn't parse Country XML."));
			} elseif(!$dom->validate()) {
				$this->getErrors()->addError("error", new Error("Error Validating XML."));
			}
			$this->setDom($dom);
		}
		return $this->dom;
	}
	
	/**
	 * Returns an instance of the BasicCountryStateTools object
	 * @return BasicCountryStateTools
	 */
	static function getInstance() {
		if (isset(self::$instance)) {
            return self::$instance;
        } else {
        	self::$instance = new BasicAreaCodeTools();
        	return self::$instance;
        }
	}
	
	/**
	 * Sets the dom object.
	 * @param DOMDocument $dom
	 */
	function setDom($dom) {
		$this->dom = $dom;
	}

	/**
	* Returns a BasicAreaCodeForm if an appropriate abbreviation is passed in
	* @return BasicAreaCodeForm
	*/
	static function getAreaCode($arg0) {
		$area_codes = self::getAreacodes();
		/* @var $area_code BasicAreaCodeForm */
		foreach ($area_codes as $area_code) {
			if ($area_code->getCode() == $arg0) {
				return $area_code;
			}
		}
		return false;
	}
	
	/**
	* Returns a BasicAreaCodeForm if an appropriate abbreviation is passed in
	* @return boolean
	*/
	static function validateAreaCode($arg0) {
		$area_codes = self::getAreacodes();
		/* @var $area_code BasicAreaCodeForm */
		foreach ($area_codes as $area_code) {
			if ($area_code->getCode() == $arg0->getCode()) {
				if ($area_code->getState() == $arg0->getState()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	* Abstract method to perform a query of all the records.  A PageListForm should
	* normally be passed into so that a limit can be performed
	* @param Form $arg0
	* @return array
	*/
	static function getAreacodes() {
		$retVal = array();
		$area_codes = self::getInstance()->getDom()->getElementsByTagName('areacode');
		foreach($area_codes as $area_code) {
			$area_code_form = new BasicAreaCodeForm();
			$area_code_form->populate($area_code);
			$retVal[] = $area_code_form;
		}
		return $retVal;
	}
 }

?>
<?php
/**
 * CountryModel is used to interface with the list of available states.  Currently it runs off an xml stream.
 *
 * @version 1.0.0
 * @copyright 2005 
 **/
 class BasicCountryStateTools {
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
			$xml_filename = MO_APP_DIR . "/util/country-defs.xml";
			if (file_exists(MO_LIB_DIR . "/xml/country-defs.xml")) {
				$xml_filename = MO_LIB_DIR . "/xml/country-defs.xml";	
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
        	self::$instance = new BasicCountryStateTools();
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
	 * Retrieves an array of CountryForms from the xml for countries that are active.
	 * @return array
	 */
	static function getActiveCountries() {
		$retVal = Array();
		$countries = self::getInstance()->getDom()->getElementsByTagName('country');
		foreach($countries as $country) {
			if($country->getAttribute('active') == "true") {
				$country_form = new BasicCountryStateForm();
				$country_form->populate($country);
				$retVal[] = $country_form;
			}
		}
		return $retVal;
	}
	/**
	 * Retrieves an array of CountryForms from the xml for all countries.
	 * @return array
	 */
	static function getAllCountries() {
		$retVal = Array();
		$countries = self::getInstance()->getDom()->getElementsByTagName('country');
		foreach($countries as $country) {
			$country_form = new BasicCountryStateForm();
			$country_form->populate($country);
			$retVal[] = $country_form;
		}
		return $retVal;
	}
	/**
	 * Retrieves the CountryForm for the country identified by $id
	 * @param string $id
	 * @return CountryForm
	 */
	static function getCountryById($id) {
		$retVal = new BasicCountryStateForm();
		$country = self::getInstance()->getDom()->getElementById($id);
		if(!is_null($country) && $country instanceof DOMElement) {
			$retVal->populate($country);
		}
		return $retVal;
	}
	/**
	 * Retrieves an array of active States/Provinces underneath the country identified by $id
	 * @param string $id
	 * @return array
	 */
	static function getActiveStatesByCountryId($id) {
		$retVal = Array();
		$country = self::getInstance()->getDom()->getElementById($id);
		if(!is_null($country) && $country instanceof DOMElement) {
			$states = $country->getElementsByTagName('state');
			foreach($states as $state) {
				if($state->getAttribute('active') == "true") {
					$state_form = new BasicCountryStateForm();
					$state_form->populate($state);
					$retVal[] = $state_form;
				}
			}
		}
		return $retVal;
	}
	/**
	 * Retrieves an array of all States/Provinces underneath the country identified by $id
	 * @param string $id
	 * @return array
	 */
	static function getAllStatesByCountryId($id) {
		$retVal = Array();
		$country = self::getInstance()->getDom()->getElementById($id);
		if(!is_null($country) && $country instanceof DOMElement) {
			$states = $country->getElementsByTagName('state');
			foreach($states as $state) {
				$state_form = new BasicCountryStateForm();
				$state_form->populate($state);
				$retVal[] = $state_form;
			}
		}
		return $retVal;
	}
 }

?>
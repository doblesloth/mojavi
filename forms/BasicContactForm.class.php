<?php
	/**
	* BasicContactForm is the base class for any class that needs to use contact information (firstname,
	* lastname, address info, phone, etc).  It extends BasicForm, so an id can be used.  It supports
	* two address fields and a created field.
	**/
	class BasicContactForm extends BasicForm {
		private $firstname;
		private $lastname;
		private $address;
		private $address2;
		private $city;
		private $state;
		private $zipcode;
		private $country;
		private $phone;
		private $email;
		private $fax;
		private $mobile;
		
		private $area_code;
		private $phone_prefix;
		private $phone_suffix;

		/**
		 * returns the mobile
		 * @return string
		 */
		function getMobile() {
		    if (is_null($this->mobile)) {
		        $this->mobile = "";
		    }
		    return $this->mobile;
		}

		/**
		 * sets the mobile
		 * @param string $arg0
		 */
		function setMobile($arg0) {
		    $this->mobile = $arg0;
		}

		/**
		* Returns the fullname (first + last)
		* @return string
		*/
		function getFullname() {
			return $this->getFirstname() . " " . $this->getLastname();
		}

		/**
		* Returns the Id for this object
		* @return string
		*/
		function getFirstname() {
			if (is_null($this->firstname)) {
				$this->firstname= "";
			}
			return($this->firstname);
		}

		/**
		* Sets the Id for this object
		* @param string $arg0
		*/
		function setFirstname($arg0) {
			$this->firstname=$arg0;
		}

		/**
		* Returns the lastname for this object
		* @return string
		*/
		function getLastname() {
			if (is_null($this->lastname)) {
				$this->lastname= "";
			}
			return($this->lastname);
		}

		/**
		* Sets the lastname for this object
		* @param string $arg0
		*/
		function setLastname($arg0) {
			$this->lastname=$arg0;
		}

		/**
		* Returns the address for this object
		* @return string
		*/
		function getAddress() {
			if (is_null($this->address)) {
				$this->address= "";
			}
			return($this->address);
		}

		/**
		* Sets the address for this object
		* @param string $arg0
		*/
		function setAddress($arg0) {
			$this->address=$arg0;
		}

		/**
		* Returns the secondary address for this object
		* @return string
		*/
		function getAddress2() {
			if (is_null($this->address2)) {
				$this->address2= "";
			}
			return($this->address2);
		}

		/**
		* Sets the secondary address for this object
		* @param string $arg0
		*/
		function setAddress2($arg0) {
			$this->address2=$arg0;
		}

		/**
		* Returns the city for this object
		* @return string
		*/
		function getCity() {
			if (is_null($this->city)) {
				$this->city= "";
			}
			return($this->city);
		}

		/**
		* Sets the city for this object
		* @param string $arg0
		*/
		function setCity($arg0) {
			$this->city=$arg0;
		}

		/**
		* Returns the state for this object
		* @return string
		*/
		function getState() {
			if (is_null($this->state)) {
				$this->state= "";
			}
			return($this->state);
		}

		/**
		* Sets the state for this object
		* @param string $arg0
		*/
		function setState($arg0) {
			$this->state=$arg0;
		}

		/**
		* Returns the zipcode for this object
		* @return string
		*/
		function getZipcode() {
			if (is_null($this->zipcode)) {
				$this->zipcode= "";
			}
			return($this->zipcode);
		}

		/**
		* Sets the zipcode for this object
		* @param string $arg0
		*/
		function setZipcode($arg0) {
			$this->zipcode=$arg0;
		}

		/**
		* Returns the country for this object
		* @return string
		*/
		function getCountry() {
			if (is_null($this->country)) {
				$this->country= "";
			}
			return($this->country);
		}

		/**
		* Sets the country for this object
		* @param string $arg0
		*/
		function setCountry($arg0) {
			$this->country=$arg0;
		}

		/**
		* Returns the phone for this object
		* @return string
		*/
		function getPhone() {
			if (is_null($this->phone)) {
				$this->phone= "";
			}
			if ($this->phone == "" && $this->getAreaCode() != "") {
				$this->phone = $this->getAreaCode() . $this->getPhonePrefix() . $this->getPhoneSuffix();
			}
			return $this->phone;
		}

		/**
		* returns the stripped phone number
		* @return string
		*/
		function getStrippedPhone() {
			return $this->getPhonePrefix() . $this->getPhoneSuffix();
		}

		/**
		* returns the stripped phone number
		* @return string
		*/
		function getStrippedAreacodePhone() {
			return $this->getAreaCode() . $this->getPhonePrefix() . $this->getPhoneSuffix();
		}

		/**
		* Sets the phone for this object
		* @param string $arg0
		*/
		function setPhone($arg0) {
			// Replace all non-numeric characters
			$arg0 = preg_replace("/[^\d]/", "",  $arg0);
			// Strip the '1' if it exists at the beginning
			if (strpos($arg0, "1") === 0) {
				$arg0 = substr($arg0, 1);
			}

			if (strlen($arg0) > 7) {
				$this->setAreaCode(substr($arg0, 0, 3));
				$this->setPhonePrefix(substr($arg0, 3, 3));
				$this->setPhoneSuffix(substr($arg0, 6));
			} else {
				$this->setPhonePrefix(substr($arg0, 0, 3));
				$this->setPhoneSuffix(substr($arg0, 3));
			}

			$this->phone=$arg0;
		}

		/**
		* Returns the email for this object
		* @return string
		*/
		function getEmail() {
			if (is_null($this->email)) {
				$this->email= "";
			}
			return($this->email);
		}

		/**
		* Sets the email for this object
		* @param string $arg0;
		*/
		function setEmail($arg0) {
			$this->email=$arg0;
		}

		/**
		* Returns the fax for this object
		* @return string
		*/
		function getFax() {
			if (is_null($this->fax)) {
				$this->fax= "";
			}
			return($this->fax);
		}

		/**
		* Sets the fax for this object
		* @param string $arg0
		*/
		function setFax($arg0) {
			$this->fax=$arg0;
		}
		
		/**
		 * Returns the phoneSuffix
		 * @return string
		 */
		function getPhoneSuffix() {
			if (is_null($this->phone_suffix)) {
				$this->phone_suffix = "";
			}
			return $this->phone_suffix;
		}

		/**
		 * Sets the phoneSuffix
		 * @param string
		 */
		function setPhoneSuffix($arg0) {
			$this->phone_suffix = $arg0;
		}

		/**
		 * Returns the phonePrefix
		 * @return string
		 */
		function getPhonePrefix() {
			if (is_null($this->phone_prefix)) {
				$this->phone_prefix = "";
			}
			return $this->phone_prefix;
		}

		/**
		 * Sets the phonePrefix
		 * @param string
		 */
		function setPhonePrefix($arg0) {
			$this->phone_prefix = $arg0;
		}

		/**
		 * Returns the areaCode
		 * @return string
		 */
		function getAreaCode() {
			if (is_null($this->area_code)) {
				$this->area_code = "";
			}
			return $this->area_code;
		}

		/**
		 * Sets the areaCode
		 * @param string
		 */
		function setAreaCode($arg0) {
			$this->area_code = $arg0;
		}

		/**
		* Attempts to validate this form.  If any errors occur, they are
		* populated in the internal errors object.
		* @return boolean - true if validation succeeds
		*/
		function validate() {
			parent::validate();
			$regex_alpha = "/[a-zA-Z'-\. ]+/";
			$regex_alphanumeric = "/[0-9a-zA-Z'\-#&\. ]+/";
			$regex_numeric = "/[0-9\-()\.]+/";
			
			if (strlen($this->getFirstname()) == 0) {
				$this->getErrors()->addError("firstname", new Error("You must enter a firstname to proceed."));
			}
			if (strlen($this->getLastname()) == 0) {
				$this->getErrors()->addError("lastname", new Error("You must enter a lastname to proceed."));
			}
			if (strlen($this->getEmail()) == 0) {
				$this->getErrors()->addError("email", new Error("You must enter an email address to proceed."));
			} else {
				if (!preg_match("/^[-a-zA-Z0-9!#$%&'*+\/=?^_`{|}~]+(\.[-a-zA-Z0-9!#$%&'*+\/=?^_`{|}~]+)*@(([a-zA-Z0-9]([-a-zA-Z0-9]*[a-zA-Z0-9]+)?){1,63}\.)+([a-zA-Z0-9]([-a-zA-Z0-9]*[a-zA-Z0-9]+)?){2,63}$/", $this->getEmail())) {
					$this->getErrors()->addError("email", new Error("You must enter a valid email address to proceed."));
				}
			}
			if (strlen($this->getPhone()) == 0) {
				$this->getErrors()->addError("phone", new Error("You must enter a phone number to proceed."));
			} else {
				if (!preg_match($regex_numeric, $this->getPhone())) {
					$this->getErrors()->addError("phone", new Error("Your phone number can only contain numbers."));
				} else if (strpos($this->getAreaCode(), "1") === 0) {
					$this->getErrors()->addError("phone", new Error("Your area code cannot begin with a 1, please check it and try again."));
				}	
			}
			if (strlen($this->getAddress()) == 0) {
				$this->getErrors()->addError("address", new Error("You must enter an address to proceed."));
			}
			if (strlen($this->getCity()) == 0) {
				$this->getErrors()->addError("city", new Error("You must enter a city to proceed."));
			}
			if (strlen($this->getState()) == 0) {
				$this->getErrors()->addError("state", new Error("You must enter a state to proceed."));
			}
			if (strlen($this->getZipcode()) == 0) {
				$this->getErrors()->addError("zipcode", new Error("You must enter a zipcode to proceed."));
			}
			if (strlen($this->getState()) == 0) {
			     $this->getErrors()->addError("state", new Error("You must choose a state"));
			}
			if (strlen($this->getCountry()) == 0) {
			     $this->getErrors()->addError("country", new Error("You must choose a country"));
			}
		}

		/**
		* Returns the formatted address.  This will return different strings depending on
		* whether the client is international or not.
		* @return string
		*/
		function getFormattedAddress() {
			$retVal = "";
			if ($this->isInternational()) {
				if ($this->getCountry() == "CA" || $this->getCountry() == "IE" || $this->getCountry() == "UK" || $this->getCountry() == "AU") {
					$retVal = $this->getAddress();
					$retVal .= "\n";
					if (strlen($this->getAddress2()) > 0) {
						$retVal .= $this->getAddress2();
						$retVal .= "\n";
					}
					$retVal .= $this->getCity() . ", " . $this->getState() . " " . $this->getZipcode();
					$retVal .= "\n";
					$retVal .= $this->getCountry();
				} else {
					$countries = BasicCountryStateTools::getActiveCountries();
					$countryName = $this->getCountry();
					foreach ($countries as $country) {
						if ($country->getAbbreviation() == $this->getCountry()) {
							$countryName = $country->getName();
							break;
						}
					}

					$retVal = $this->getAddress();
					$retVal .= "\n";
					if (strlen($this->getAddress2()) > 0) {
						$retVal .= $this->getAddress2();
						$retVal .= "\n";
					}
					$retVal .= $this->getCity() . ", " . $countryName . " " . $this->getZipcode();
				}
			} else {
				$retVal = $this->getAddress();
				$retVal .= "\n";
				if (strlen($this->getAddress2()) > 0) {
					$retVal .= $this->getAddress2();
					$retVal .= "\n";
				}
				$retVal .= $this->getCity() . ", " . $this->getState() . " " . $this->getZipcode();
				$retVal .= "\n";
				$retVal .= $this->getCountry();
			}
			return $retVal;
		}

		/**
		* Returns the formatted address.  This will return different strings depending on
		* whether the client is international or not.
		* @return string
		*/
		function getHTMLFormattedAddress() {
			return nl2br($this->getFormattedAddress());
		}

		/**
		* Returns if this client is an international client or not
		* @return boolean
		*/
		function isInternational() {
			if ($this->getCountry() == "US") {
				return false;
			} else {
				return true;
			}
		}

	}
?>

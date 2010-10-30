<?php

/**
 * DateRangeForm takes care of formatting a date range (start and end dates).  It is
 * useful for reports.
 *
 * @author Mark Hobson
 * @copyright 2005
 **/

class DateRangeForm extends PageListForm {

	const DATE_FORMAT_MDY = "m/d/Y";
	const DATE_FORMAT_FULL = "m/d/Y g:i:s a";
	const DATE_FORMAT_TIME = "g:i:s a";
	const DATE_FORMAT_MYSQL = "Y-m-d";

	private $startDate;
	private $startTime;
	private $endDate;
	private $endTime;
	private $dateFormat;
	private $noEnd;

	/**
	 * returns the noEnd
	 * @return boolean
	 */
	function isNoEnd() {
	    if (is_null($this->noEnd)) {
	        $this->noEnd = false;
	    }
	    return $this->noEnd;
	}

	/**
	 * sets the noEnd
	 * @param boolean $arg0
	 */
	function setNoEnd($arg0) {
	    $this->noEnd = $arg0;
	    return $this;
	}

	/**
	* Returns the formatted start date based on the values in getStartDate() and getDateFormat().
	* @return string
	*/
	function getFormattedStartDate() {
		return date($this->getDateFormat(), strtotime($this->getStartDate()));
	}

	/**
	* Returns the formatted start date based on the values in getStartDate() and getDateFormat().
	* @return string
	*/
	function getFormattedStartDateOnly() {
		return date(self::DATE_FORMAT_MDY, strtotime($this->getStartDate()));
	}

	/**
	* Returns the formatted start date based on the values in getStartDate() and getDateFormat().
	* @return string
	*/
	function getFormattedEndDateOnly() {
		return date(self::DATE_FORMAT_MDY, strtotime($this->getEndDate()));
	}

	/**
	* Returns the formatted start date based on the values in getStartDate() and getDateFormat().
	* @return string
	*/
	function getFormattedStartTime() {
		return date(self::DATE_FORMAT_TIME, strtotime($this->getStartDate()));
	}

	/**
	* Returns the formatted end date based on the values in getStartDate() and getDateFormat().
	* @return string
	*/
	function getFormattedEndTime() {
		return date(self::DATE_FORMAT_TIME, strtotime($this->getEndDate()));
	}

	/**
	* Returns the start date
	* @return string
	*/
	function getStartDate() {
		if (is_null($this->startDate)) {
			$this->startDate = "now";
		}
		if (strlen($this->startDate) == 0) {
			$this->startDate = "now";
		}
		return $this->startDate;
	}

	/**
	* Sets the start date
	* @param string $arg0
	*/
	function setStartDate($arg0) {
		$this->startDate = $arg0;
		return $this;
	}

	/**
	* Returns the start time
	* @return string
	*/
	function getStartTime() {
		if (is_null($this->startTime)) {
			$this->startTime = "now";
		}
		if (strlen($this->startTime) == 0) {
			$this->startTime = "now";
		}
		return $this->startTime;
	}

	/**
	* Sets the start time
	* @param string $arg0
	*/
	function setStartTime($arg0) {
		$this->startTime = $arg0;
		$this->setStartDate(date(self::DATE_FORMAT_MDY, strtotime($this->getStartDate())) . " " . date(self::DATE_FORMAT_TIME, strtotime($this->getStartTime())));
		return $this;
	}

	/**
	* Returns the formatted end date based on the values in getEndDate() and getDateFormat().
	* @return string
	*/
	function getFormattedEndDate() {
		return date($this->getDateFormat(), strtotime($this->getEndDate()));
	}

	/**
	* Returns the end date
	* @return string
	*/
	function getEndDate() {
		if (is_null($this->endDate)) {
			$this->endDate = "now";
		}
		if (strlen($this->endDate) == 0) {
			$this->endDate = "now";
		}
		return $this->endDate;
	}

	/**
	* Sets the end date
	* @param string $arg0
	*/
	function setEndDate($arg0) {
		$this->endDate = $arg0;
		return $this;
	}

	/**
	* Returns the end time
	* @return string
	*/
	function getEndTime() {
		if (is_null($this->endTime)) {
			$this->endTime = "now";
		}
		if (strlen($this->endTime) == 0) {
			$this->endTime = "now";
		}
		return $this->endTime;
	}

	/**
	* Sets the end time
	* @param string $arg0
	*/
	function setEndTime($arg0) {
		$this->endTime = $arg0;
		$this->setEndDate(date(self::DATE_FORMAT_MDY, strtotime($this->getEndDate())) . " " . date(self::DATE_FORMAT_TIME, strtotime($this->getEndTime())));
		return $this;
	}

	/**
	* Returns the date format
	* @return string
	*/
	function getDateFormat() {
		if (is_null($this->dateFormat)) {
			$this->dateFormat = DateRangeForm::DATE_FORMAT_MDY;
		}
		return $this->dateFormat;
	}

	/**
	* Sets the date format
	* @param string $arg0
	*/
	function setDateFormat($arg0) {
		$this->dateFormat = $arg0;
		return $this;
	}

	/**
	* Validates the input
	* @return void
	*/
	function validate() {
		parent::validate();
		if (!$this->isNoEnd()) {
			if (strtotime($this->getStartDate()) > strtotime($this->getEndDate())) {
				$this->getErrors()->addError("start_date", new Error("The start date must be before the end date."));
			}
		}
	}
}

?>
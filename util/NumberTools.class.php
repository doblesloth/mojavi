<?php
/**
 * NumberTools
 * Contains methods for number manipulation
 */
class NumberTools {
	/**
	 * stripNonNumericChars
	 * removes all characters besides all numerals, hyphen (-), and period (.)
	 * @param mixed $num
	 * @return unknown
	 */
	static function stripNonNumericChars($num) {
		return preg_replace("/[^0-9\-\.]/", "", $num);
	}
	
	/**
	 * Returns the ordinal suffix to use for the given number
	 * @param integer
	 * @return string
	 */
	static function getOrdinal($num) {
		$suffix = "";
		if (is_numeric($num)) {
			if(substr($num, -2, 2) == 11 || substr($num, -2, 2) == 12 || substr($num, -2, 2) == 13){
		        $suffix = "th";
		    } else if (substr($num, -1, 1) == 1){
		        $suffix = "st";
		    } else if (substr($num, -1, 1) == 2){
		        $suffix = "nd";
		    } else if (substr($num, -1, 1) == 3){
		        $suffix = "rd";
		    } else {
		        $suffix = "th";
		    }	
		}
		return $suffix;
	}
}
?>
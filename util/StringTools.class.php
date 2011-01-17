<?php
/**
 * StringTools contains static methods for manipulating strings.
 *
 * @version $Id$
 * @copyright 2005
 */
class StringTools {
	
	const CONSOLE_COLOR_RED = 1;
	const CONSOLE_COLOR_GREEN = 2;
	const CONSOLE_COLOR_YELLOW = 3;
	const CONSOLE_COLOR_BLUE = 4;
	const CONSOLE_COLOR_PURPLE = 5;
	const CONSOLE_COLOR_CYAN = 6;
	const CONSOLE_COLOR_WHITE = 7;
	
	/**
	 * Removes characters that could potentially cause query problems and/or injection.
	 * Removes ;, ", \r, and \n
	 *
	 * @param string $text
	 * @return string
	 */
	static function removeBadMySQLChars($text) {
		$retVal = preg_replace('/[;"\r\n]/', "", $text);
		return $retVal;
	}

	/**
	 * Performs a debug backtrace that can be sent to the error log easily
	 * @param array $backtrace
	 * @return string
	 */
	static function getDebugBacktraceForLogs($backtrace = null) {
		$retVal = '';
		if(is_null($backtrace)) {
			$backtrace = debug_backtrace();
		}
		while(($arr_elmnt = array_shift($backtrace)) !== null) {
			if($retVal == '') {
				$retVal .= "\n\tfrom ";
			} else {
				$retVal .= "\n\tat ";
			}
			$retVal .= $arr_elmnt['function'] . "() called at [" . basename($arr_elmnt['file']) . ":" . $arr_elmnt['line'] . "]";
		}
		return $retVal;
	}

	/**
	 * Truncates the string making sure it does not exceed the character length including the trailing
	 * The trailing is only included if the string is truncated.
	 * @param string $str
	 * @param int $char_len
	 * @param string $trailing
	 * @return string
	 */
	static function truncate($str, $char_len = 30, $trailing = '...') {
		$ret_val = '';
		if(strlen($str) > $char_len) {
			$char_len -= strlen($trailing);
			$ret_val = substr($str, 0, $char_len) . $trailing;
		} else {
			$ret_val = $str;
		}
		return $ret_val;
	}

	/**
	 * Returns the Human Readable equivalent for filesizes
	 * @param integer $size
	 * @return string
	 */
	static function getHumanReadable($size){
		$i=0;
		$iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
		while (($size/1024)>1) {
			$size=$size/1024;
			$i++;
		}
		return substr($size,0,strpos($size,'.') + 4) . $iec[$i];
	}
	
	/**
	 * Returns the days from a seconds argument
	 * @param integer $seconds
	 * @param boolean $remove_days
	 * @return integer
	 */
	static function getDays($seconds){
		return floor($seconds / 86400);
	}
	
	/**
	 * Returns the hours from a seconds argument
	 * @param integer $seconds
	 * @param boolean $remove_days
	 * @return integer
	 */
	static function getHours($seconds, $remove_days = true){
		if ($remove_days) {
			$new_seconds = $seconds - (86400 * floor($seconds / 86400));
			return floor($new_seconds / 3600);
		} else {
			return floor($seconds / 3600);
		}
	}
	
	/**
	 * Returns the minutes from a seconds argument
	 * @param integer $seconds
	 * @param boolean $remove_hours
	 * @return integer
	 */
	static function getMinutes($seconds, $remove_hours = true){
		if ($remove_hours) {
			$new_seconds = $seconds - (3600 * floor($seconds / 3600));
			return floor($new_seconds / 60);
		} else {
			return floor($seconds / 60);
		}
	}
	
	/**
	 * Returns the seconds from a seconds argument
	 * @param integer $size
	 * @param boolean $remove_minutes
	 * @return integer
	 */
	static function getSeconds($seconds, $remove_minutes = true) {
		if ($remove_minutes) {
			$new_seconds = $seconds - (60 * floor($seconds / 60));
			return $new_seconds;
		} else {
			return $seconds;	
		}
	}
	
	/**
	 * Returns a formatted phone number as %d (%d) %d-%d
	 * @param string $phone 
	 * @param string $separator
	 * @param string $area_code_separator
	 * @return string
	 */
	static function formatPhone($phone) {
		// For phone formatting the format is usually (3 chars) 3 chars-4 chars, so for simplicity sake
		// we'll split the string as (3 chars) 3 chars-3 chars + 1 char.
		if ($phone != "") {
			// Since the last 4 digits should be together, shift off the last digit and we'll add it to the last triplet.
			$suffix = substr($phone, -1);
			// Now to split the string, we reverse the string, take off the first character (from above line) and split it 
			// by 3 character chunks (any remaining characters will be at the front - such as a 1).
			$phone_pieces = str_split(substr(strrev($phone), 1), 3);
			// And add the last character to the first triplet (remember that the phone pieces contains a backwards phone number
			$phone_pieces[0] = $suffix . $phone_pieces[0];
			// Now loop through the array and reverse (strrev) each element (so that it's in the right order) and build the format string
			$format = "";
			foreach ($phone_pieces as $key => $phone_piece) {
				if ($key == 0) {
					$format = "%s" . $format;
				} else if ($key == 1) {
					$format = "%s-" . $format;	
				} else if ($key == 2) {
					$format = "(%s) " . $format;
				} else if ($key == count($phone_pieces) - 1 && $key > 2) {
					$format = "+%s " . $format;
				} else {
					$format = "%s " . $format;
				}
				$phone_pieces[$key] = strrev($phone_pieces[$key]);
			}
			// Now we just have to reverse the array so that the 1st triplet (4 characters) is at the end
			$phone_pieces = array_reverse($phone_pieces);
			// And finally output the formatted string.
			return vsprintf($format, $phone_pieces);
		} else {
			return $phone;	
		}
	}
	
	/**
	 * underscored string to make into camelcase, first letter is left alone.
	 *
	 * @param underscored string $key
	 * @return camel cased string
	 */
	static function camelCase($key) {
		return preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",$key);
	}
	
	/**
	 * Converts a string to a console colored string
	 * @return string
	 */
	static function consoleColor($str = '', $color = 0) {
		switch ($color) {
			case self::CONSOLE_COLOR_RED:
				return "\33[01;31m" . $str . "\033[0m";
			case self::CONSOLE_COLOR_GREEN:
				return "\33[01;32m" . $str . "\033[0m";
			case self::CONSOLE_COLOR_YELLOW:
				return "\33[01;33m" . $str . "\033[0m";
			case self::CONSOLE_COLOR_BLUE:
				return "\33[01;34m" . $str . "\033[0m";
			case self::CONSOLE_COLOR_WHITE:
				return "\33[01;37m" . $str . "\033[0m";
			case self::CONSOLE_COLOR_PURPLE:
				return "\33[01;35m" . $str . "\033[0m";
			case self::CONSOLE_COLOR_CYAN:
				return "\33[01;36m" . $str . "\033[0m";
			default:
				return $str;
		}
	}
	
	/**
	 * Outputs text to the console with a set width and colored status message
	 * @param string $line
	 * @param string $status
	 * @param integer $color
	 * @param integer $width
	 * @param integer $status_width
	 * @return string
	 */
	static function consoleWrite($line = '', $status = '', $color = 0, $new_line = false, $width = 50, $status_width = 11, $do_not_echo = false) {
		$ret_val = $line;
		$status_width = ($status_width > strlen($status)) ? $status_width : strlen($status);
		if ($status !== null) {
			$ret_val .= str_repeat('.', $width - strlen($line));
			$ret_val .= '[ ' . self::consoleColor(str_pad($status, $status_width, ' ', STR_PAD_LEFT), $color) . ' ]';
		}
		if ($do_not_echo) {
			return $ret_val;
		} else {
			echo $ret_val;
			if (!$new_line) {
				echo str_repeat("\010", $width + $status_width + 4);	
			} else {
				echo "\n";	
			}
			return $ret_val;	
		}
	}
	
	/**
	 * Converts an array to an XML document
	 * @param array $data
	 * @return string
	 */
	static function arrayToXml($data, $rootNodeName = 'data', $xml = null,$parentNodeName = null) {
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set ('zend.ze1_compatibility_mode', 0);
		}
		
		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}
		
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = $parentNodeName;
			}
			
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z]/i', '', $key);
			
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recursive call.
				StringTools::arrayToXml($value, $rootNodeName, $node, $key);
			} else if (is_object($value)) {
				$xml->addChild($key,$value);
			}
			else 
			{
				// add single node.
                $value = htmlentities($value);
				$xml->addChild($key,$value);
			}
			
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}
	
	/**
	 * Returns the string in between two strings
	 * @return string
	 */
	static function getStringBetween($string, $start, $end){
		$string = " " . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return "";
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len); 
	}
}
?>
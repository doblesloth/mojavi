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
	
	static private $_word_list;
	
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
		return preg_replace_callback("/_([a-zA-Z0-9])/", function($matches) { return strtoupper($matches[1]); }, strtolower($key));
		#return preg_replace("/_([a-zA-Z0-9])/e","strtoupper('\\1')",$key);
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
	 * Converts a string to a console colored string
	 * @return string
	 */
	static function stripColor($str = '') {
		$str = str_replace("\33[01;31m", "", $str);
		$str = str_replace("\33[01;32m", "", $str);
		$str = str_replace("\33[01;33m", "", $str);
		$str = str_replace("\33[01;34m", "", $str);
		$str = str_replace("\33[01;37m", "", $str);
		$str = str_replace("\33[01;35m", "", $str);
		$str = str_replace("\33[01;36m", "", $str);
		$str = str_replace("\033[0m", "", $str);
		return $str;
	}
	
	/**
	 * Converts a string to a console colored string
	 * @return string
	 */
	static function consoleToHtmlColor($str = '') {
		$str_buffer = '';
		$str_lines = explode("\n", trim($str));
		foreach ($str_lines as $key => $str_line) {
			if (strpos($str_line, "\010") === false) {
				// If this is a full line with no backspace characters, then append it
				$str_buffer .= $str_line . "\n";
			} else if ($key == (count($str_lines) - 1)) {
				// If this is the last line, then append it as is (after stripping extra backspaces off it)
				$str_line = trim($str_line, "\x00..\x1F");
				$str_line = substr($str_line, strrpos($str_line, "\010"));
				$str_buffer .= $str_line . "\n";
			} else {
				// If this line has backspace characters, then go back on the line and clear out those characters
				$str_line = substr($str_line, strrpos($str_line, "\010"));
				$str_line = str_replace("\010", '', $str_line);
				$str_buffer .= $str_line . "\n";
			}
		}
		$str_buffer = str_replace(" ", '&nbsp;', $str_buffer);
		$str_buffer = str_replace("\33[01;31m", '<span style="color:red;">', $str_buffer);
		$str_buffer = str_replace("\33[01;32m", '<span style="color:green;">', $str_buffer);
		$str_buffer = str_replace("\33[01;33m", '<span style="color:yellow;">', $str_buffer);
		$str_buffer = str_replace("\33[01;34m", '<span style="color:blue;">', $str_buffer);
		$str_buffer = str_replace("\33[01;37m", '<span style="color:black;">', $str_buffer);
		$str_buffer = str_replace("\33[01;35m", '<span style="color:purple;">', $str_buffer);
		$str_buffer = str_replace("\33[01;36m", '<span style="color:cyan;">', $str_buffer);
		$str_buffer = str_replace("\033[0m", '</span>', $str_buffer);
		
		$str_buffer = str_replace("[01;31m", '<span style="color:red;">', $str_buffer);
		$str_buffer = str_replace("[01;32m", '<span style="color:green;">', $str_buffer);
		$str_buffer = str_replace("[01;33m", '<span style="color:yellow;">', $str_buffer);
		$str_buffer = str_replace("[01;34m", '<span style="color:blue;">', $str_buffer);
		$str_buffer = str_replace("[01;37m", '<span style="color:black;">', $str_buffer);
		$str_buffer = str_replace("[01;35m", '<span style="color:purple;">', $str_buffer);
		$str_buffer = str_replace("[01;36m", '<span style="color:cyan;">', $str_buffer);
		$str_buffer = str_replace("[0m", '</span>', $str_buffer);
		
		return $str_buffer;
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
		
//		if (trim(shell_exec('echo $TERM')) == '') {
//			$screen_width_cmd = 'tput -T xterm cols';	
//		} else {
//			$screen_width_cmd = 'tput -T xterm cols';
//		}
		
		$orig_screen_width = 80;// intval(trim(shell_exec($screen_width_cmd)));
				
//		if (intval($orig_screen_width) == 0) { $orig_screen_width = 140; }
		$screen_width = $orig_screen_width;
		if ($screen_width < 70) { $screen_width = 70; }
		$line_width = strlen($line);
		$status_width = strlen('[ ' . $status . ' ]');
		$status_width = ($status_width > strlen($status)) ? $status_width : strlen($status);
		$dot_width = $screen_width - $line_width - $status_width;
		
		
		if ($status !== null) {
			if ($dot_width > 0) {
				$ret_val .= str_repeat('.', $dot_width);
			}
			$ret_val .= '[ ' . self::consoleColor($status, $color) . ' ]';
		}
		if ($do_not_echo) {
			return $ret_val;
		} else {
			if (!$new_line) {
				echo $ret_val;
				if ($screen_width > 0 && $screen_width > ($line_width + $status_width)) {
					echo str_repeat("\010", $screen_width);
				} else {
					echo str_repeat("\010", ($line_width + $status_width));
				}
			} else {
				echo $ret_val;
				if ($orig_screen_width - strlen(self::stripColor($ret_val)) > 0) {
					echo str_repeat(" ", ($orig_screen_width - strlen(self::stripColor($ret_val))));
				}
				echo "\n";	
			}
			return $ret_val;	
		}
	}
	
	/**
	 * Prompts the user for a question on the console.
	 * @param string $question
	 * @param string $default
	 * @param boolean $auto_respond
	 * @return string
	 */
	public static function consolePrompt($question, $default = '', $auto_respond = false) {
		if ($auto_respond) { return $default; }
		$response = '';
		if (($fp = fopen("php://stdin", "r")) !== false) {
			echo $question . ( $default != '' ? " [" . $default . "] " : " " );
			$tmp_response = fgets($fp);
			if (preg_match("/(\r|\r\n|\n)/", $tmp_response)) {
				$response .= preg_replace("/(\r|\r\n|\n)/", "", $tmp_response);
			} else {
				$response .= $tmp_response;
			}
			
			if($response == '' && $default != '') {
				$response = $default;
			}
			fclose($fp);
		} else {
			throw new Exception('Cannot read from stdin');
		}
		return $response;	
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
	
	/**
	 * Base64 and URL encodes a string
	 * @param string $str
	 * @return string
	 */
	static function base64_url_encode($str) {
		return strtr(base64_encode($str), '+/=', '-_.');
	}
	
	/**
	 * Base64 and URL decodes a string
	 * @param string $str
	 * @return string
	 */
	static function base64_url_decode($str) {
		return base64_decode(strtr($str, '-_.', '+/='));
	}
	
	/**
	 * Returns a random words
	 * @return string
	 */
	static function getRandomWords($num_words = 1, $library_file = null) {
		if (is_null($library_file)) {
			$library_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'random.dict';
		}
		if (!is_array(self::$_word_list)) {
			self::$_word_list = array();
		}
		if (file_exists($library_file) && is_readable($library_file)) {
			if (count(self::$_word_list) == 0) {
				self::$_word_list = file($library_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			}
			shuffle(self::$_word_list);
			if ($num_words < count(self::$_word_list)) {
				return array_slice(self::$_word_list, 0, $num_words);
			} else {
				return self::$_word_list;
			}
		}
		return "";		
	}
	
	/**
	 * Returns a random word
	 * @return string
	 */
	static function getRandomWord($library_file = null) {
		if (is_null($library_file)) {
			$library_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'random.dict';
		}
		if (!is_array(self::$_word_list)) {
			self::$_word_list = array();
		}
		if (file_exists($library_file) && is_readable($library_file)) {
			if (count(self::$_word_list) == 0) {
				self::$_word_list = file($library_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				shuffle(self::$_word_list);
			}
			return array_shift(self::$_word_list);
		}
		return "";		
	}
	
	/**
	 * Encodes a string as ISO-8895-1 or UTF-8 using Quoted-Printable or Base64 encodings
	 * @param string $scheme - Either Q for Quoted-Printable or B for Base64
	 * @param integer $line_length
	 * @param string $break_lines - Either \n or ""
	 * @param string $encoding - Either UTF-8 or ISO-8895-1
	 * @param string $arg0
	 * @return string
	 */
	static function encodeString($arg0, $scheme = "Q", $encoding = "ISO-8859-1", $line_length = 76, $break_lines = "\n") {
		$subject_parts = preg_split('//',$arg0,-1);
		$subject_array = array();
		foreach($subject_parts as $char) {
			$subject_array[] = $char;
			if (bin2hex($char) == '20') {
				$rand = rand(1,18);
				for ($x = 0; $x < $rand; $x++) {
					$subject_array[] = chr(10);
				}	
			}
		}
		$padded_string = implode("", $subject_array);
		$preferences = array(
			"line-length" => $line_length,
			"line-break-chars" => $break_lines,
			"scheme" => $scheme
		);
		if (strtoupper($encoding) == 'UTF-8') {
			$preferences["input-charset"] = 'ISO-8859-1';
			$preferences["output-charset"] = $encoding;
		} else {
			$preferences["input-charset"] = 'UTF-8';
			$preferences["output-charset"] = $encoding;
		}
		$ret_val = @iconv_mime_encode("Subject", $padded_string, $preferences);
		return substr($ret_val, strlen("Subject: "));
	}
	
	/**
	 * ISO sizes a string to ISO-8895-1
	 * @param string $arg0
	 * @return string
	 */
	static function isoSize($arg0) {
		return self::encodeString($arg0, "B", "ISO-8859-1//TRANSLIT", 1024, "");
	}
	
	/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @return array (depending on second parameter. Returns False if invalid hex color value)
	 */                                                                                                
	static function hex2RGB($hexStr) {
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array('red' => 0, 'green' => 0, 'blue' => 0);
		if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		}
		return $rgbArray;
	}
}
?>
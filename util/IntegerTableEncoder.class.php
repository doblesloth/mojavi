<?php

/**
 * IntegerTableEncoder is used to secure ids.  Basically, an integer is 
 * passed in and it is encoded against an internal encryption table.  
 * Then the integer is encrypted to an 8-character string.  This class 
 * can be altered to support an n-character string, but usually it should 
 * be a base-2 number (2,4,8,16-characters).
 * 
 * Since the database stores the raw integer, there's no problem with 
 * losing a key, and multiple encryption algorithms can be used (or 
 * multiple encoding tables) to further secure the data.
 *
 * @version $Id$
 * @copyright 2005 
 **/
 
 class IntegerTableEncoder extends MojaviObject {
 
 	private static $bitDepth = 4;
	private static $table = array(
		array( 'z', 'c', 'b', 'm', 'l', 'j', 'g', 'd', 'a', 'p', 'i', 'y', 'r', 'w', '2', '5' ),
        array( '1', '3', '7', 'q', 'e', 't', 'u', 'o', 'k', 'h', 'f', 's', 'x', 'v', 'n', '2' ),
        array( 'x', 'v', 'n', 'k', 'j', 'h', 'f', 'd', 's', 'q', 'w', 'e', 'y', 'i', '5', '9' ),
        array( '1', '4', '5', '6', '9', 'p', 'o', 'i', 'u', 'r', 'e', 'w', 'a', 's', 'g', 'j' ),
        array( 'm', 'n', 'b', 'v', 'c', 'x', 'z', 'a', 's', 'd', 'g', 'j', 'k', 'p', 'o', '4' ),
        array( '4', '1', '2', '6', '8', '9', '0', 'w', 'q', 'y', 't', 'o', 'i', 'l', 'h', 's' ),
        array( 'b', 'n', 'm', 'x', 'c', 'v', 'a', 'd', 'g', 'f', 'k', 'j', 'o', 'y', 't', '6' ),
        array( '9', '6', '3', '1', 'q', 'e', 't', 'u', 'w', 'r', 'y', 'i', 'l', 'h', 'd', 'v' )
	);
 
 	/**
	* Encodes an integer to an encoded string that can be decoded
	*/
 	static function encodeInt($arg0) {
		$retVal = "";
		try {
			settype($arg0, "integer");
			$i = $arg0;
			$j = pow(IntegerTableEncoder::$bitDepth, 2) - 1;
			for ($k = 0; $k< (32/IntegerTableEncoder::$bitDepth); $k++) {
				$l = $i & $j;
				$i >>= IntegerTableEncoder::$bitDepth;
				$retVal .= IntegerTableEncoder::$table[$k][$l];	
			}
		} catch (Exception $e) {
			print "error: " . $e;
		}
		return $retVal;
	}
 
 	/**
	* Retrieves the index of the character by iterating through the 
	* internal table
	*/
 	private static function getDecodedIndex($i, $c) {
		$j = pow(IntegerTableEncoder::$bitDepth, 2) - 1;
		for ($k=0;$k<=$j;$k++) {
			if (IntegerTableEncoder::$table[$i][$k] == $c) {
				return $k;
			}
		}
		return -1;
	}
 	
	/**
	* Decodes an encoded string and returns the index of it
	*/
	static function decodeInt($arg0) {
		$retVal = 0;
		if(is_numeric($arg0)) {
			return $arg0;
		}
		try {
			if (is_string($arg0)) {
				$char_array = str_split($arg0);
				$i = 0;
				$j = pow(IntegerTableEncoder::$bitDepth, 2) - 1;
				for ($k = ((32/IntegerTableEncoder::$bitDepth)-1); $k >= 0; $k--) {
					if ($k < count($char_array)) {
						$c = $char_array[$k];
						$l = IntegerTableEncoder::getDecodedIndex($k,$c);
						if ($l == -1) {
							return $arg0;
						}
						$retVal <<= IntegerTableEncoder::$bitDepth;
						$retVal |= $l;
					} else {
						$retVal = $arg0;
						break;
					}
				}
			}
		} catch (Exception $e) {
			print "error: " . $e;
		}
		return $retVal;
	}
 
 }

?>
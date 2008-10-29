<?php
/**
 * Escapes the passed in string for display on the browser.
 * Essentially, this function aliases the PHP function htmlentities()
 * We decided to implement this in a programmer's meeting on 5/30/06
 * @param string $string
 * @return string
 */
function he($string) {
	return htmlentities($string, ENT_QUOTES);
}
?>
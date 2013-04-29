<?php

/**
 * This library is a basic implementation of CURL capabilities.
 *
 * ==================================== USAGE ====================================
 * Use one of the CURL request methods:
 *
 * CURL::get($url);
 * CURL::post($url, $vars); vars is a urlencoded string in query string format.
 *
 * Your request will return the response text.
 *
 * @author	Mark Hobson
 */
class CURL
{

	// +------------------------------------------------------------------------+
	// | CONSTANTS																|
	// +------------------------------------------------------------------------+

	const
		REQUEST_METHOD_GET	= 'GET',
		REQUEST_METHOD_POST	= 'POST';

	// +------------------------------------------------------------------------+
	// | PRIVATE VARIABLES														|
	// +------------------------------------------------------------------------+
	private static $ch;
	private static $persist;

	// +------------------------------------------------------------------------+
	// | PUBLIC METHODS															|
	// +------------------------------------------------------------------------+

	/**
	 * Return the curl object handle
	 * @return curl_handle
	 */
	public static function getResource() {
		# Return the curl object
		return self::$ch;
	}

	/**
	 * Set the curl object handle
	 * @param handle $arg0
	 */
	public static function setResource($arg0) {
		# Set the curl object
		self::$ch = $arg0;
	}

	// -------------------------------------------------------------------------

	/**
	 * Get the curl info
	 * @param int $opt
	 * @return mixed
	 */
	public static function getInfo($opt=null) {
		return curl_getinfo(self::$ch, $opt);
	}

	// -------------------------------------------------------------------------

	/**
	 * Run a get request
	 * @param string $url
	 * @param array $args
	 * @return string
	 */
	public static function get($url, $args = null)
	{
		# Process and return the GET request
		return self::doRequest(self::REQUEST_METHOD_GET, $url, null, $args);
	}

	// -------------------------------------------------------------------------

	/**
	 * Run a post request
	 * @param string $url
	 * @param string $vars
	 * @param array $args
	 * @return string
	 */
	public static function post($url, $vars, $args = null)
	{
		# Process and return the post request
		return self::doRequest(self::REQUEST_METHOD_POST, $url, $vars, $args);
	}

	// -------------------------------------------------------------------------

	/**
	 * Make the curl connection persistant
	 * @param boolean $persist
	 */
	public static function persist($persist)
	{
		# Toggle the persistance of the connection
		self::$persist = $persist;
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieve whether the CURL connection is running persistantly
	 * @return boolean
	 */
	public static function isPersistant() {
		return self::$persist;
	}

	// -------------------------------------------------------------------------

	/**
	 * Close a persistant curl connection
	 */
	public static function close()
	{

		# Close a persistant connection
		if (!is_null(self::$ch) && self::$persist) {
			curl_close(self::$ch);
			self::$ch = null;
		}

	}

	// +------------------------------------------------------------------------+
	// | PRIVATE METHODS														|
	// +------------------------------------------------------------------------+

	/**
	 * Run a curl request
	 * @param string $method
	 * @param string $url
	 * @param string $vars
	 * @param array $args
	 * @return string
	 */
	private static function doRequest($method, $url, $vars = null, $args = null)
	{
		# Make it so that the script never exits on a curl request
		set_time_limit(0);

		# If our curl object is null, initialize it
		if(is_null(self::$ch)) {
			self::$ch = curl_init();
		}

		# Define the user agent
		$useragent = 'Mozilla/4.0(compatible; MSIE 6.0; Windows 98; IE5.x/Winxx/EZN/xx; .NET CLR 1.1.4322)';
		if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] != '') {
			$useragent = $_SERVER['HTTP_USER_AGENT'];
		}

		# Set the default curl options (commas are backwards for easy comment out debugging)
		$cookie_file = "/tmp/" . session_id() . '_cookie';

		$options = array(
			 CURLOPT_URL => $url
			,CURLOPT_HEADER => 1
			,CURLOPT_USERAGENT => $useragent
			,CURLOPT_FOLLOWLOCATION => 1
			,CURLOPT_RETURNTRANSFER => 1
			,CURLOPT_COOKIEFILE => $cookie_file
			,CURLOPT_COOKIEJAR => $cookie_file
			,CURLOPT_SSL_VERIFYPEER => false
			,CURLOPT_SSL_VERIFYHOST => false
			,CURLOPT_CONNECTTIMEOUT => 30
		);
		curl_setopt_array(self::$ch, $options);

		# If we're posting, set the post information
		if(self::REQUEST_METHOD_POST == $method) {
			curl_setopt(self::$ch, CURLOPT_POST, 1);
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $vars);
		}
		# If we're not, make sure it knows we are using the get
		else {
			curl_setopt(self::$ch, CURLOPT_HTTPGET, 1);
		}

		# If we have additional arguments we want to add in, do so
		if (!is_null($args)) {
			foreach ($args as $opt=>$val) {
				curl_setopt(self::$ch, $opt, $val);
			}
		}

		# Run the curl request
		$retval = curl_exec(self::$ch);
		# If it failed, set the retval to the error
		if (!$retval) {
			$retval = curl_error(self::$ch);
		}

		# If our connection is not persistant
		if(is_null(self::$persist) || !self::$persist) {
			# Close and nullify it
			curl_close(self::$ch);
			self::$ch = null;
		}

		# Return our results
		return $retval;

	}

	// -------------------------------------------------------------------------

}

?>

<?php
/**
* Multiple Curl Handlers
* @author Jorge Hebrard ( jorge.hebrard@gmail.com )
**/
class MultiCurl extends MojaviObject {
	
	static private $listenerList;
    
	private $callback;
	
	public function __construct($url, $key = null) {
		$new = &self::$listenerList[];
		$new['url'] = $url;
		if (!is_null($key)) {
			$new['key'] = $key;	
		} else {
			$new['key'] = $url;
		}
		$this->callback = &$new;
	}
    
	/**
	 * Callbacks needs 3 parameters: $url, $html (data of the url), and $lag (execution time)
	 */
	public function addListener($callback) {
		$this->callback['callback'] = $callback;
	}
    
	/**
	 * curl_setopt() wrapper. Enjoy!
	 */
	public function setOpt($key,$value) {
		$this->callback['opt'][$key] = $value;
	}
	
	/**
	 * Request all the created curlNode objects, and invoke associated callbacks.
	 */
	static public function request(){
        // create the multiple cURL handle
		$mh = curl_multi_init();
        
        $running = null;
        
        # Setup all curl handles
        # Loop through each created curlNode object.
        foreach (self::$listenerList as &$listener){
            $url = $listener['url'];
            $current = &$ch[];
            
            # Init curl and set default options.
            # This can be improved by creating
            $current = curl_init();

            curl_setopt($current, CURLOPT_URL, $url);
            # Since we don't want to display multiple pages in a single php file, do we?
            curl_setopt($current, CURLOPT_HEADER, 0);
            curl_setopt($current, CURLOPT_RETURNTRANSFER, 1);
            # Set defined options, set through curlNode->setOpt();
			if (isset($listener['opt'])){
				foreach($listener['opt'] as $key => $value){
					curl_setopt($current, $key, $value);
				}
			}

			curl_multi_add_handle($mh, $current);
            
			$listener['handle'] = $current;
			$listener['start'] = microtime(1);
		} unset($listener);

		# Main loop execution
		do {
			# Exec until there's no more data in this iteration.
			# This function has a bug, it
			while(($execrun = curl_multi_exec($mh, $running)) == CURLM_CALL_MULTI_PERFORM);
			if ($execrun != CURLM_OK) { break; } # This should never happen. Optional line.
            
			# Get information about the handle that just finished the work.
			while($done = curl_multi_info_read($mh)) {
				# Call the associated listener
 				foreach (self::$listenerList as $listener){
					# Strict compare handles.
					if ($listener['handle'] === $done['handle']) {
						# Get content
						$html = curl_multi_getcontent($done['handle']);
						# Call the callback.
						call_user_func($listener['callback'], $listener['key'], $html, (microtime(1)-$listener['start']));
						# Remove unnecesary handle (optional, script works without it).
						curl_multi_remove_handle($mh, $done['handle']);
						curl_close($listener['handle']);
						unset($listener);
						break;
					}
				}
                
			}
			# Required, or else we would end up with a endless loop.
			# Without it, even when the connections are over, this script keeps running.
			if (!$running) break;
            
			# I don't know what these lines do, but they are required for the script to work.
			while (($res = curl_multi_select($mh)) === 0);
			if ($res === false) break; # Select error, should never happen.
		} while (true);

		# Finish out our script ;)
		curl_multi_close($mh);
    }
}
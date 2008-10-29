 <?php
 /**
 * ZipUtils contains methods to create and stream zip files.
 * 
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * @access  public 
 *
 * @author Chris Roberts 
 * @since 09/17/2007 5:02 pm 
 */


 class ZipUtils
 {

 	// +------------------------------------------------------------------------+
 	// | CONSTANTS																|
 	// +------------------------------------------------------------------------+

 	/**
     * Array to store compressed data
     *
     * @var  array    $datasec
     */
 	var $datasec      = array();

 	/**
     * Central directory
     *
     * @var  array    $ctrl_dir
     */
 	var $ctrl_dir     = array();

 	/**
     * End of central directory record
     *
     * @var  string   $eof_ctrl_dir
     */
 	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

 	/**
     * Last offset position
     *
     * @var  integer  $old_offset
     */
 	var $old_offset   = 0;

 	// +------------------------------------------------------------------------+
 	// | PUBLIC METHODS															|
 	// +------------------------------------------------------------------------+

 	/**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param  integer  the current Unix timestamp
     *
     * @return integer  the current date in a four byte DOS format
     *
     * @access private
     */
 	function unix2DosTime($unixtime = 0) {
 		$timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

 		if ($timearray['year'] < 1980) {
 			$timearray['year']    = 1980;
 			$timearray['mon']     = 1;
 			$timearray['mday']    = 1;
 			$timearray['hours']   = 0;
 			$timearray['minutes'] = 0;
 			$timearray['seconds'] = 0;
 		} // end if

 		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
 		($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
 	} // end of the 'unix2DosTime()' method


 	/**
     * Adds "file" to archive
     *
     * @param  string   file contents
     * @param  string   name of the file in the archive (may contains the path)
     * @param  integer  the current timestamp
     *
     * @access public
     */
 	function addFile($data, $name, $time = 0)
 	{
 		$name     = str_replace('\\', '/', $name);

 		$dtime    = dechex($this->unix2DosTime($time));
 		$hexdtime = '\x' . $dtime[6] . $dtime[7]
 		. '\x' . $dtime[4] . $dtime[5]
 		. '\x' . $dtime[2] . $dtime[3]
 		. '\x' . $dtime[0] . $dtime[1];
 		eval('$hexdtime = "' . $hexdtime . '";');

 		$fr   = "\x50\x4b\x03\x04";
 		$fr   .= "\x14\x00";            // ver needed to extract
 		$fr   .= "\x00\x00";            // gen purpose bit flag
 		$fr   .= "\x08\x00";            // compression method
 		$fr   .= $hexdtime;             // last mod time and date

 		// "local file header" segment
 		$unc_len = strlen($data);
 		$crc     = crc32($data);
 		$zdata   = gzcompress($data);
 		$zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
 		$c_len   = strlen($zdata);
 		$fr      .= pack('V', $crc);             // crc32
 		$fr      .= pack('V', $c_len);           // compressed filesize
 		$fr      .= pack('V', $unc_len);         // uncompressed filesize
 		$fr      .= pack('v', strlen($name));    // length of filename
 		$fr      .= pack('v', 0);                // extra field length
 		$fr      .= $name;

 		// "file data" segment
 		$fr .= $zdata;

 		// "data descriptor" segment (optional but necessary if archive is not
 		// served as file)
 		// nijel(2004-10-19): this seems not to be needed at all and causes
 		// problems in some cases (bug #1037737)
 		//$fr .= pack('V', $crc);                 // crc32
 		//$fr .= pack('V', $c_len);               // compressed filesize
 		//$fr .= pack('V', $unc_len);             // uncompressed filesize

 		// add this entry to array
 		$this -> datasec[] = $fr;

 		// now add to central directory record
 		$cdrec = "\x50\x4b\x01\x02";
 		$cdrec .= "\x00\x00";                // version made by
 		$cdrec .= "\x14\x00";                // version needed to extract
 		$cdrec .= "\x00\x00";                // gen purpose bit flag
 		$cdrec .= "\x08\x00";                // compression method
 		$cdrec .= $hexdtime;                 // last mod time & date
 		$cdrec .= pack('V', $crc);           // crc32
 		$cdrec .= pack('V', $c_len);         // compressed filesize
 		$cdrec .= pack('V', $unc_len);       // uncompressed filesize
 		$cdrec .= pack('v', strlen($name)); // length of filename
 		$cdrec .= pack('v', 0);             // extra field length
 		$cdrec .= pack('v', 0);             // file comment length
 		$cdrec .= pack('v', 0);             // disk number start
 		$cdrec .= pack('v', 0);             // internal file attributes
 		$cdrec .= pack('V', 32);            // external file attributes - 'archive' bit set

 		$cdrec .= pack('V', $this -> old_offset); // relative offset of local header
 		$this -> old_offset += strlen($fr);

 		$cdrec .= $name;

 		// optional extra field, file comment goes here
 		// save to central directory
 		$this -> ctrl_dir[] = $cdrec;
 	} // end of the 'addFile()' method


 	/**
     * Dumps out file
     *
     * @return  string  the zipped file
     *
     * @access public
     */
 	function file()
 	{
 		$data    = implode('', $this -> datasec);
 		$ctrldir = implode('', $this -> ctrl_dir);

 		return
 		$data .
 		$ctrldir .
 		$this -> eof_ctrl_dir .
 		pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
 		pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
 		pack('V', strlen($ctrldir)) .           // size of central dir
 		pack('V', strlen($data)) .              // offset to start of central dir
 		"\x00\x00";                             // .zip file comment length
 	} // end of the 'file()' method

	// +------------------------------------------------------------------------+
	// | HELPER METHODS															|
	// +------------------------------------------------------------------------+

 	/**
 	* Returns a new zipfile object
 	* @return zipfile-object
 	*/
 	function getNew(){
 		// form is posted, handle it
 		$zipfile = new zipfile();
 		return $zipfile;
 	}

 	/**
 	* Adds a file to a zip
 	* @param $pathtofile path to be added to the zip file
 	* @param $zipfile zipfile object
	* @param $file the name of to add to the zip can be differnt from path file name
 	* @return bool success
 	*/
 	function addLocalFile($pathtofile,$file, $zipfile){
 		$f_tmp = @fopen( $pathtofile, 'r');
 		if($f_tmp){
 			$dump_buffer=fread( $f_tmp, filesize($pathtofile));
 			$zipfile -> addFile($dump_buffer, $file);
 			fclose( $f_tmp );
 			$retVal=true;
 		} else {
 			$retVal=false;
 			error_log("ERROR Getting file ".$pathtofile,1);
 		}
 	}
 	
 	/**
 	* Adds a Binary string as a file to a zip file
 	* @param $string string to add to the zip file
 	* @param $string_file_name file name for string is to be added as
 	* @param $zipfile zipfile object
 	*/
 	function addBinaryString($string, $string_file_name, $zipfile){
 		// generate _settings into zip file
 		$zipfile ->addFile( $string,  $string_file_name);
 	}

 	/**
 	* Adds a ascii string as a file to a zip file
 	* @param $string string to add to the zip file
 	* @param $string_file_name file name for string is to be added as
 	* @param $zipfile zipfile object
 	* 
 	*/
 	function addString($string, $string_file_name, $zipfile){
 		// generate _settings into zip file
 		$zipfile ->addFile( stripcslashes( $string ),  $string_file_name);
 	}

 	/**
 	* Streams a zip file from memory
 	* @param $zipfile zipfile object 
 	* @param $zip_file_name name to pass to the browser
 	* @return bool success
 	*/
 	function streamMem($zip_file_name,$zipfile){
 		$dump_buffer = $zipfile ->file();
 		// response zip archive to browser:
 		header('Pragma: public');
 		header('Content-type: application/zip');
 		header('Content-length: ' . strlen($dump_buffer));
 		header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');

 		return true;
 	}

 	/**
 	* streams a zip file from memory and saves file to disk
 	* @param $zipfile zipfile object 
 	* @param $zip_file_name name to pass to the browser
 	* @param $zip_path path/filename to save the file to
 	* @return bool success
 	*/
 	function streamAndSaveFile($zip_file_name,$zip_path,$zipfile){
 		$dump_buffer = $zipfile ->file();
 		// write the file to disk:
 		$file_pointer = fopen('$zip_path', 'w');
 		if($file_pointer){
 			fwrite( $file_pointer, $dump_buffer, strlen($dump_buffer) );
 			fclose( $file_pointer );
 		}
 		// response zip archive to browser:
 		header('Pragma: public');
 		header('Content-type: application/zip');
 		header('Content-length: ' . strlen($dump_buffer));
 		header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');
 		return true;
 	}
 	
	/**
 	* Saves a zip file from memory to disk
 	* @param $zipfile zipfile object 
 	* @param $zip_path path/filename to save the file to
 	* @return bool success
 	*/
 	function saveFile($zip_path,$zipfile){
 		$dump_buffer = $zipfile ->file();
 		// write the file to disk:
 		$file_pointer = fopen('$zip_path', 'w');
 		if($file_pointer){
 			fwrite( $file_pointer, $dump_buffer, strlen($dump_buffer) );
 			fclose( $file_pointer );
 		}
 		
 		return true;
 	}
 	
	/**
 	* Streams a zip file from local filesystem
 	* @param $zipfile zipfile object 
 	* @param $zip_file_name name to pass to the browser
 	* @return bool success
 	*/
 	function streamFile($zip_file_name,$zipfile){
 		$f_tmp = @fopen( $zip_file_name, 'r');
 		if($f_tmp){
 			$dump_buffer=fread( $f_tmp, filesize($zip_file_name));
 			// response zip archive to browser:
 			header('Pragma: public');
 			header('Content-type: application/zip');
 			header('Content-length: ' . strlen($dump_buffer));
 			header('Content-Disposition: attachment; filename="'.$zip_file_name.'"');
 			fclose( $f_tmp );
 			$retVal=true;
 		} else {
 			$retVal=false;
 			error_log("ERROR Getting file ".$zip_file_name,1);
 		}

 		return true;
 	}
 } 
?>

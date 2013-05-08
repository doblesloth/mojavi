<?php
/**
 * Provides services to manage libraries loaded in a dao/lib folder
 * @author Mark Hobson
 */
class LibraryForm extends CommonForm {
	
	protected $name;
	protected $class_name;
	protected $file_name;
	protected $folder_name;
	
	/**
	 * Returns the name
	 * @return string
	 */
	function getName() {
		if (is_null($this->name)) {
			$this->name = '';
		}
		return $this->name;
	}
	/**
	 * Sets the name
	 * @param string
	 */
	function setName($arg0) {
		$this->name = $arg0;
		return $this;
	}
	
	/**
	 * Returns the class_name
	 * @return string
	 */
	function getClassName() {
		if (is_null($this->class_name)) {
			$this->class_name = '';
		}
		return $this->class_name;
	}
	/**
	 * Sets the class_name
	 * @param string
	 */
	function setClassName($arg0) {
		$this->class_name = $arg0;
		return $this;
	}
	
	/**
	 * Returns the file_name
	 * @return string
	 */
	function getFileName() {
		if (is_null($this->file_name)) {
			$this->file_name = '';
		}
		return $this->file_name;
	}
	/**
	 * Sets the file_name
	 * @param string
	 */
	function setFileName($arg0) {
		$this->file_name = $arg0;
		return $this;
	}
	
	/**
	 * Returns the folder_name
	 * @return string
	 */
	function getFolderName() {
		if (is_null($this->folder_name)) {
			$this->folder_name = '';
		}
		return $this->folder_name;
	}
	/**
	 * Sets the folder_name
	 * @param string
	 */
	function setFolderName($arg0) {
		$this->folder_name = $arg0;
		return $this;
	}
	
	/**
	 * Queries all the files
	 * @return array
	 */
	function queryAll() {
		$ret_val = array();
		if (file_exists($this->getFolderName()) && is_dir($this->getFolderName())) {
			$files = scandir($this->getFolderName());
			foreach ($files as $file) {
				if (strpos($file, '.') === 0) { continue; }
				if (substr($file, -10) != '.class.php') { continue; }
				$library = new LibraryForm();
				$library->setFileName($file);
				$library->setFolderName($this->getFolderName());
				
				$class_name = str_replace('/lib/', '_', str_replace(MO_WEBAPP_DIR . '/lib/dao/', '', $this->getFolderName())) . '_' . str_replace('.class.php', '', $file);
				
				$library->setClassName($class_name);
				$library->setName(str_replace('.class.php', '', $file));
				$ret_val[] = $library;
			}
		}
		return $ret_val;
	}
	
	/**
	 * Queries all the files
	 * @return array
	 */
	function query() {
		$results = $this->queryAll();
		foreach ($results as $result) {
			if ($result->getName() == $this->getName()) {
				return $result;
			}
		}
		return new LibraryForm();
	}
}
?>
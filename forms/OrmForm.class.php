<?php
/**
 * OrmForm contains methods to make forms behave more like an ORM with save, update, delete, etc functions
 * built into it.  
 * @author Mark Hobson
 */
class OrmForm extends DateRangeForm {
	
	/**
	 * Checks if the given record exists in the database
	 * @return boolean
	 */
	function exists() {
		// For right now, just check that the id is > 0
		return ($this->getId() > 0);	
	}
	
	/**
	 * Queries a single record from the database given a primary key
	 * @return Form
	 */
	function query() {
		$model = $this->getModel();
		if (is_object($model)) {
			$result = $model->performQuery($this);
			$this->populate($result);
			return $result;
		}
		return $this; 
	}
	
	/**
	 * Queries all the records from the database with available pagination
	 * @return DatabaseResultResource
	 */
	function queryAll() {
		$model = $this->getModel();
		if (is_object($model)) {
			$resultset = $model->performQueryAll($this);
			return $resultset;
		}
		return array(); 
	}
	
	/**
	 * Deletes a single record from the database given a primary key
	 * @return integer
	 */
	function delete() {
		$model = $this->getModel();
		if (is_object($model)) {
			$rows_affected = $model->performDelete($this);
			return $rows_affected;
		}
		return false;		 
	}
	
	/**
	 * Inserts a single record from the database given a primary key
	 * @return integer
	 */
	function insert() {
		$model = $this->getModel();
		if (is_object($model)) {
			$insert_id = $model->performInsert($this);
			return $insert_id;
		} 
		return false;
	}
	
	/**
	 * Updates a single record from the database given a primary key
	 * @return integer
	 */
	function update() {
		$model = $this->getModel();
		if (is_object($model)) {
			$rows_affected = $model->performUpdate($this);
			return $rows_affected;
		}
		return false;		 
	}
	
	/**
	 * Returns the model to use for ORM features
	 * @return Model
	 */
	function getModel() {
		$class_name = get_class($this);
		$class_name = str_replace('_Form_', '_Model_', $class_name);
		return new $class_name();	
	}
}
?>
<?php
class Help extends BasicStaticClass {
	/*   MAIN VARS   */
	static $tip_model;
	
	/******************\
	*   MAIN METHODS   *
	\******************/
	/**
	 * Retrieve Tip From Database
	 *
	 * @param unknown_type $name
	 * @return unknown
	 */
	static function getTip($name="")
	{
		self::$tip_model = self::getContext()->getController()->getModel("Admin","HelpTip",new Errors());
		$tipForm = self::getContext()->getController()->getForm('Admin','HelpTip',new Errors());
		$tipForm->setName($name);
		$tipForm = self::$tip_model->performQueryByName($tipForm);
		return $tipForm->getTip();
	}
	
	static function drawHelpIcon($header, $contents = null) {
		if(is_null($contents)) {
			$contents = $header;
			$header = 'Help Tip';
		}
		include MO_MODULE_DIR . "/Default/templates/help_icon.php";
	}
	
	static function drawInfoIcon($header, $contents = null) {
		if(is_null($contents)) {
			$contents = $header;
			$header = 'Info';
		}
		include MO_MODULE_DIR . "/Default/templates/info_icon.php";
	}
	
	/*************************\
	*   GETTERS AND SETTERS   *
	\*************************/
	/**
	 * Retrieve Help Tip Model
	 * @return HelpTipModel
	 */
	static function getTipModel() {
		if(is_null(self::$tip_model)) {
			self::$tip_model = self::getContext()->getController()->getModel("Admin", "HelpTip", new Errors());
		}
		return self::$tip_model;
	}
	
	/**
	 * Sets the Help Tip Model
	 * @param HelpTipModel $model
	 */
	static function setTipModel(HelpTipModel $model) {
		self::$tip_model = $model;
	}
}
?>
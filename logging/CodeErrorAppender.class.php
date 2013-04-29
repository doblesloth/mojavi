<?php
/**
 * Write Error messages to the code_error db table
 * @author Mark Hobson
 */
class CodeErrorAppender extends Appender
{

	protected $web_application_id;
	
	/**
	 * Initialize the CodeErrorAppender.
	 * 
	 * @param array An array of parameters.
	 * 
	 * @return void
	 * 
	 */
	public function initialize($params)
	{
		if (isset($params['webapp'])) {
			$this->setWebApplicationId($params['webapp']);
		}
	}
	
	/**
	 * Returns the web_application_id
	 * @return integer
	 */
	function getWebApplicationId() {
		if (is_null($this->web_application_id)) {
			$this->web_application_id = 0;
		}
		return $this->web_application_id;
	}
	
	/**
	 * Sets the web_application_id
	 * @param integer
	 */
	function setWebApplicationId($arg0) {
		$this->web_application_id = $arg0;
	}
			
	/**
	 * Execute the shutdown procedure.
	 * return void
	 */
	public function shutdown()
	{
	}

   /**
	 * Write a Message to the db table.
	 * @param Message
	 * 
	 * @throws <b>LoggingException</b> if no Layout is set or the file
	 *         cannot be written.
	 * 
	 * @return void
	 */
	public function write($message)
	{
		if ($layout = $this->getLayout() === null) {
			throw new LoggingException('No Layout set');
		}

		$str = sprintf("%s\n", $this->getLayout()->format($message));
		
		/* @var $code_error_form CodeErrorForm */
		$code_error_form = Controller::getInstance()->getContext()->getController()->getForm("Error","CodeError");
		/* @var $code_error_model CodeErrorModel */
		$code_error_model = Controller::getInstance()->getContext()->getController()->getModel("Error","CodeError");
		$code_error_form->setMessage($message);
		$code_error_form->setWebApplicationId($this->getWebApplicationId());
		$code_error_model->performInsert($code_error_form);
	}

}

?>
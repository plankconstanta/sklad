<?php
class Email_SerialNumber extends Email_Sald {
	protected $mark = 'serial_number';
	protected $subject = 'Серийный номер к антивирусу Dr.Web';
		
	public function __construct($params=array())
	{
		$this->params = $params;
		$this->oTemplate = $this->createTemplate();
	}
	
	public function createTemplate()
	{
		$oTemplate = Email_Template_Factory::create(Email_Type::SERNUM, $this->params);	
		return $oTemplate;
	}
	
	public function headers()
	{
		$headers = parent::headers();
		$headers .= "Content-type: text/plain; charset=utf-8";
		return $headers;
	}
}

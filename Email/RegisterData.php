<?php
class Email_RegisterData extends Email_Sald {
	protected $mark = 'account_data';
	protected $subject = 'Регистрационные данные';
		
	public function __construct($params=array())
	{
		$this->params = $params;
		$this->oTemplate = $this->createTemplate();
	}
	
	public function createTemplate()
	{
		$oTemplate = Email_Template_Factory::create(Email_Type::REGISTER, $this->params);	
		return $oTemplate;
	}
	
	public function headers()
	{
		$headers = parent::headers();
		$headers .= "Content-type: text/plain; charset=utf-8";
		return $headers;
	}
}

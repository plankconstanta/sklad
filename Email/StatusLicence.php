<?php
class Email_StatusLicence extends Email_Sald {
	protected $mark = 'license_status';
	protected $subject = 'Уведомление о состоянии лицензии';
		
	public function __construct($params=array())
	{
		$this->params = $params;
		$this->oTemplate = $this->createTemplate();
	}
	
	public function createTemplate()
	{
		$oTemplate = Email_Template_Factory::create(Email_Type::STATUS_LICENCE, $this->params);	
		return $oTemplate;
	}
	
	public function headers()
	{
		$headers = parent::headers();
		$headers .= "Content-type: text/plain; charset=utf-8";
		return $headers;
	}
}

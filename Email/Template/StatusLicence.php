<?php
class Email_Template_StatusLicence extends Email_Template_Abstract {
	static protected $NAME = 'status_licence.tmpl';
	
	public function __construct($params=array())
	{		
		$this->params = $params;
	}
}
<?php
class Email_Template_RegisterData extends Email_Template_Abstract {
	static protected $NAME = 'register.tmpl';
	
	public function __construct($params=array())
	{		
		$this->params = $params;
	}
}
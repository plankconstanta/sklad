<?php
class Email_Template_SerialNumber extends Email_Template_Abstract {
	static protected $NAME = 'sernum.tmpl';
	
	public function __construct($params=array())
	{		
		$this->params = $params;
	}
}
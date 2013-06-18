<?php
class Email_Template_BillAvailable extends Email_Template_Abstract {
	static protected $NAME = 'bill_available.tmpl';
	
	public function __construct($params=array())
	{		
		$this->params = $params;
	}
}

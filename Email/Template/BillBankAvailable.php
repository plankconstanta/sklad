<?php
class Email_Template_BillBankAvailable extends Email_Template_Abstract {
	static protected $NAME = 'bill_bank_available.tmpl';
	
	public function __construct($params=array())
	{		
		$this->params = $params;
	}
}

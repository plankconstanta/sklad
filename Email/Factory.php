<?php
class Email_Factory {
	public static function create($type, $params=array(), $attach=array())
	{		
		switch($type) {
			case Email_Type::BILL_AVAILABLE:				
			case Email_Type::BILL_BANK_AVAILABLE:				
				return new Email_BillAvailable($type, $params, $attach);
				break;
			case Email_Type::REGISTER:
				return new Email_RegisterData($params);
				break;	
			case Email_Type::SERNUM:
				return new Email_SerialNumber($params);
				break;		
			case Email_Type::STATUS_LICENCE:
				return new Email_StatusLicence($params);
				break;			
		}			
		
		return null;
	}
}

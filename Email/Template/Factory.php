<?php
class Email_Template_Factory {
	const CLASS_PREFIX = 'Email_Template_';
	
	public function __construct()
	{}
	
	public static function create($type, $params) 
	{
		$class = self::CLASS_PREFIX;
		$class .= $type;
		if (class_exists($class)) {						
			$obj = new $class($params);
			return $obj;
		} else { 
			return null; 
		}
	}
}

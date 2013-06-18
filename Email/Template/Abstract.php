<?php

abstract class Email_Template_Abstract {	
	protected static $NAME = 'default.tmpl';
	
	protected $params;		
	protected $template;	
	
	public function __construct()
	{}
	
	public function template()
	{
		return self::generate($this->path(), $this->params);
	}
	
	public function params($value = array())
	{
		if (!empty($value)) {
			$this->params = $value;
		}
		return $this->params;
		
	}
	
	public static function path()
	{
		return $_SERVER['DOCUMENT_ROOT']."/app/lib/Email/Template/src/".static::$NAME;
	}
	
	public static function generate($path, $params)
	{
		$template = '';
		if (file_exists($path)) {
			$template = file_get_contents($path);
			if (!empty($params)) {
				$keys = $values = array();
				foreach ($params as $key=>$value) {
					$keys[] = '%' . $key . '%';
					$values[] = $value;
				}
			}			
			$template = str_replace($keys, $values, $template);			
		}
		return $template;
	}
}

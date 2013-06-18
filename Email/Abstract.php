<?php
abstract class Email_Abstract {
	protected $subject;	
	protected $message;		
	protected $headers;
	
	public function __construct()
	{}
	
	public function message($value = '')
	{
		if (!empty($value)) {
			$this->message = $value;
		}
		return $this->message;
	}
	
	public function headers($value = '')
	{
		if (!empty($value)) {
			$this->headers = $value;
		}
		return $this->headers;
	}
	
	public function subject($value = '')
	{
		if (!empty($value)) {
			$this->subject = $value;
		}
		return $this->subject;
	}
}

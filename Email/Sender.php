<?php

class Email_Sender {
	protected $arEmailTo;
	protected $oEmail;

	public function __construct(array $arEmailTo, Email_Abstract $oEmail)
	{
		$this->oEmail = $oEmail;
		$this->arEmailTo = $arEmailTo;;
	}
	
	private function __send($email, $subject, $message, $headers)
	{
		return mail($email, $subject, $message, $headers);
	}
	
	public function send()
	{
		if (!empty($this->arEmailTo)) {
			$headers = $this->oEmail->headers();
			$subject = $this->mime_header_encode($this->oEmail->subject(), 'utf-8', 'utf-8');
			$message = $this->oEmail->message();
						
			foreach($this->arEmailTo as $emailTo) {
				$this->__send($emailTo, $subject, $message, $headers);
			}
			
			return true;
		}
		return false;
	}

	private function mime_header_encode($str, $data_charset, $send_charset) {
		if($data_charset != $send_charset) {
			$str = iconv($data_charset, $send_charset, $str);
		}
		return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
	}  
}

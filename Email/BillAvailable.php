<?php
class Email_BillAvailable extends Email_Sald {
	protected $mark = 'bill_available';
	protected $subject = 'Счет';
	protected $params;
	protected $subType;
	protected $attach;
	protected $boundary = '';
	
	public function __construct($subType, $params=array(), $attach=array())
	{
		$this->params = $params;
		$this->subType = $subType;
		$this->attach = $attach;
		$this->boundary = $this->generateBoundary();		
		$this->oTemplate = $this->createTemplate();
	}
	
	protected function generateBoundary()
	{
		$semi_rand = md5(time());
		return "==Multipart_Boundary_x{$semi_rand}x";							
	}

	public function createTemplate()
	{
		$oTemplate = Email_Template_Factory::create($this->subType, $this->params);	
		return $oTemplate;
	}
	
	public function headers()
	{
		$headers = parent::headers();
		$headers .= "MIME-Version: 1.0\n" . "Content-Type: multipart/mixed;" . " boundary=\"{$this->boundary}\"";
		return $headers;
	}
		
	public function message()
	{
		$text = parent::message();

		if (empty($this->attach)) {
			return $text;
		}
				
        // multipart boundary
        $message = "--{$this->boundary}\n" . "Content-Type: text/plain; charset=\"utf-8\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" . $text . "\n\n";

        // preparing attachments
        foreach($this->attach as $file){
            if(is_file($file)){
                $message .= "--{$this->boundary}\n";
                $fp = fopen($file,"rb");
                $data = fread($fp, filesize($file));
                fclose($fp);
                $data = base64_encode($data);
                $message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n" .
                "Content-Description: ".basename($file)."\n" .
                "Content-Disposition: attachment;\n" . " filename=\"".basename($file)."\"; size=".filesize($file).";\n" .
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
        $message .= "--{$this->boundary}--";
        
        return $message;
	}	
}

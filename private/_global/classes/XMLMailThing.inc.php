<?php
/*
Class: XML Mail Thing
*/
class XMLMailThing
{
	private $xml;
	public $emails = array();
	public $templates = array();
	
	/*
	Constructor: XMLMailThing
	Parameters:
		string folder - template folder
		string template_filename - filename of the XML config for templates. Should be inside 'folder'
	*/
	function __construct($folder, $template_filename='templates.xml')
	{	
		$this->template_directory = preg_replace('/\/$/', '', $folder).'/';
		
		if(!($this->xml = simplexml_load_file($this->template_directory.$template_filename)))
			throw new Exception('Encountered invalid XML file. Either file not found or not in the correct format');
		
		foreach($this->xml->emailAddresses->email as $email)
			$this->emails[(string)$email['name']] = (string)$email;
		
		foreach($this->xml->templates->template as $template)
		{
			$this->templates[(string)$template['name']] = new XMLMailThingTemplate(array
			(
				'file'=>(string)$template['file'],
				'subject'=>(string)$template->subject,
				'to'=>(string)$template->to,
				'from'=>(string)$template->from
			));
		}
	}
	
	/*
	
	*/
	function send($name, $data, $to_from='', $extra_headers='')
	{
		if(!isset($this->templates[$name]))
			throw new Exception('Template "'.$name.'" does not exist. Please check your config file.');
		
		$contents = file_get_contents($this->template_directory.$this->templates[$name]->file);
		$contents = @preg_replace('/\[\[(.*?)\]\]/e', '$data["$1"]', $contents);
		
		$subject = $this->templates[$name]->subject;
		$subject = @preg_replace('/\[\[(.*?)\]\]/e', '$data["$1"]', $subject);
		
		if(is_array($to_from))
		{
			if(isset($to_from['to']))
			{
				if(is_array($to_from['to']))
				{
					for($i=0; $i<count($to_from['to']); $i++)
					{
						//this _should_ replace any reference to emails specified in the XML with the actual email.
						if(array_key_exists($to_from['to'][$i], $this->emails))
							$to_from['to'][$i] = $this->emails[$to_from['to'][$i]];
					}
					$to = implode(',', $to_from['to']);
				}
				else
					$to = $to_from['to'];
			}
			
			if(isset($to_from['from']))
				$from = $to_from['from'];
		}
		else
		{			
			if(empty($this->templates[$name]->to) && !empty($this->templates[$name]->from))
			{
				$to = $to_from;
				$from = $this->templates[$name]->from;
			}
			else if(!empty($this->templates[$name]->to) && empty($this->templates[$name]->from))
			{
				$to = $this->templates[$name]->to;
				$from = $to_from;
			}
			else
			{
				$to = $this->templates[$name]->to;
				$from = $this->templates[$name]->from;
			}
		}
		
		if(empty($to))
			throw new Exception('No recipients specified');
		if(empty($from))
			throw new Exception('No sender specified');
			
		if(array_key_exists($to, $this->emails))
			$to = $this->emails[$to];
		if(array_key_exists($from, $this->emails))
			$from = $this->emails[$from];
		
		$headers = 
			'MIME-Version: 1.0' . "\r\n".
			'Content-type: text/html; charset=iso-8859-1' . "\r\n".
			'From: '.$from."\r\n".
			$extra_headers;
		return mail($to, $subject, $contents, $headers);
	}
	
	function html($name, $data)
	{
		$contents = file_get_contents($this->template_directory.$this->templates[$name]->file);
		return @preg_replace('/\[\[(.*?)\]\]/e', '$data["$1"]', $contents);	
	}
}

class XMLMailThingTemplate
{
	public $subject, $to, $from, $file;
	
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
}
?>

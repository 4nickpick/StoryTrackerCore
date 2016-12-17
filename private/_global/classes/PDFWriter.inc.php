<?php
class PDFWriter
{
	const PDFTK_DIR = 'c:\\pdftk\\';
	
	public $fields, $data, $filename;
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	
		$this->getFields();
	}
	
	private function createXFDF($info,$enc='UTF-8')
	{
		$data='<?xml version="1.0" encoding="'.$enc.'"?>'."\n".
			'<xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">'."\n".
			'<fields>'."\n";
		foreach($info as $field => $val)
		{
			$data.='<field name="'.$field.'">'."\n";
			if(is_array($val))
			{
				foreach($val as $opt)
					$data.='<value>'.htmlentities($opt).'</value>'."\n";
			}
			else
			{
				$data.='<value>'.htmlentities($val).'</value>'."\n";
			}
			$data.='</field>'."\n";
		}
		$data.='</fields>'."\n".
			'<ids original="'.md5($this->filename).'" modified="'.time().'" />'."\n".
			'<f href="'.$this->filename.'" />'."\n".
			'</xfdf>'."\n";
		return $data;
	}
	
	function getFields()
	{
		exec(self::PDFTK_DIR.'pdftk.exe '.$this->filename.' dump_data_fields output -', $raw_data);
		//var_dump($raw_data);
		$this->fields = array();
		foreach($raw_data as $field)
		{
			//$lines = explode("\n", $field);
			if(preg_match('/FieldName:\s(.*)$/', $field, $matches)>0)
			{
				if(preg_match('/\.(.+)\.(.+)\[.*?\]$/', $matches[1], $matches2)>0)
				{
					$this->fields[$matches2[2]] = $matches[1];
				}
			}
		}
	}
	
	private function generateXFDF($name_value)
	{
		$fields = array();
		foreach($name_value as $field=>$value)
		{
			$fields[$this->fields[$field]] = $value;
		}
		
		return $this->createXFDF($fields);
	}
	
	function generatePDF($name_value)
	{
		$fdf = $this->generateXFDF($name_value);
		$tmp_file = tempnam(sys_get_temp_dir(),'PDF');
		$tmp_output = tempnam(sys_get_temp_dir(),'PDF');
		file_put_contents($tmp_file, $fdf);
		
		//$cmd = self::PDFTK_DIR.'pdftk '.$this->filename.' fill_form '.$tmp_file.' output - flatten';
		$cmd = self::PDFTK_DIR.'pdftk '.$this->filename.' fill_form '.$tmp_file.' output '.$tmp_output.' flatten dont_ask';
		echo $cmd;
		
		exec($cmd, $raw_data);
		
		unlink($tmp_file);
		//unlink($tmp_output);
		
		return file_get_contents($tmp_output);//implode("\n", $raw_data);
	}
	
	function generateInputHTML()
	{
		foreach($this->fields as $key=>$field)	
			echo '<textarea onmouseup="this.focus()" onfocus="this.select(); this.style.backgroundColor=\'#ffbbbb\'" style="width: 100%; height: 20px">'.htmlentities('<input type="text" name="'.$key.'" />').'</textarea><br />';
	}
}
?>
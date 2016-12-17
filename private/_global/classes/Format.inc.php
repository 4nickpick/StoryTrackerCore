<?php
class Format
{
	public static function money($money)
	{
		return number_format($money, 2, '.', ',');
	}
	
	public static function phone_number($string)
	{
		if(strlen($string)<10)
			return preg_replace('/(\d{3})(\d{4})(.*)/', '$1-$2 $3', $string);
		return preg_replace('/(\d{3})(\d{3})(\d{4})(.*)/', '$1-$2-$3 $4', $string);	
	}
	
	public static function birthdate($string)
	{			
		return preg_replace('/(\d{2})(\d{2})(\d{2})/', '$1/$2/$3', date('m/d/Y', $string));	
	}
	
	public static function email($email)
	{
		if(!preg_match('/^[\w-\.]+@([\w-]+\.)+[\w-]+$/',$email)>0)
			return false;
		return true;
	}
}
?>

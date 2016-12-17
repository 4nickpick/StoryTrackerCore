<?php
class Printer
{
	public static function printString($string)
	{
		echo (htmlentitiesUTF8($string));
	}
	
	public static function printDate($date, $format='m/d/Y')
	{
		echo (date($format, $date));
	}	
	
	public static function printDec($num, $dec='2')
	{
		echo (number_format(doubleval($num), $dec));
	}	
	
	public static function printInt($num, $dec='2')
	{
		echo (number_format(intval($num), $dec));
	}	
	
}

class P
{
	public static function rint($data, $isDate = false, $dateFormat = 'm/d/Y', $dblDec = 2)
	{	
		echo P::sanitize($data, $isDate, $dateFormat, $dblDec);
	}
	
	public static function sanitize($data, $isDate = false, $dateFormat = 'm/d/Y', $dblDec = 2)
	{	
		if ($isDate)
			return date($dateFormat, $data);
		else
		{	
			if (is_int($data))
				return (intval($data));
			else if (is_double($data))
				return number_format(doubleval($data), $dblDec);
			else if(is_string($data))
				return (htmlentitiesUTF8($data));
		}
	}
	
	public static function out($data, $isDate = false, $dateFormat = 'm/d/Y')
	{
		 P::rint($data, $isDate, $dateFormat);
	}
	
}

?>
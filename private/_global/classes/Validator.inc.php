<?php
class Validator
{

	public static function isEmail($email)
	{
		if(!preg_match('/^[\w-\.]+@([\w-]+\.)+[\w-]+$/',$email)>0)
			return false;
		return true;
	}

	public static function isPhone($phone)
	{
		if(!preg_match('/^((\(\d{3}\)?)|(\d{3}))([\s-.\/]?)(\d{3})([\s-.\/]?)(\d{4})$/',trim($phone))>0)
			return false;
		return true;
	}
	
	public static function isURL($url)
	{
		if(!preg_match('/\(?\bhttp:\/\/[A-Za-z0-9\.]+[-A-Za-z0-9+&@#\/%?=~_()|!:,.;]*[-A-Za-z0-9+&@#\/%=~_()|]/', $url)>0)
			return false;
		return true;
	}
	
}
<?php
if(!empty($_SESSION[XSRF::SESSION_VARIABLE]))
	XSRF::load(unserialize($_SESSION[XSRF::SESSION_VARIABLE]));

class XSRF
{
	public static $debug=false;
	public static $keyname='XSRF_key';
	private static $was_valid=false;
	public static $keys=array();
	
	const GENERIC_ERROR='An error has occurred: the form you submitted appears to be invalid. You may have attempted to submit the same information twice. If this problem persists, please log back out and log in again.';
	const SESSION_VARIABLE='XSRF_keys';
	
	static function load($keys)
	{
		if(is_array($keys))
			self::$keys = $keys;
	}
	
	static function valid($consume=false)
	{
		if(self::$debug || self::$was_valid)
			return true;
		
		if(is_array(self::$keys))
		{
			if(($i = array_search(@$_POST[self::$keyname], self::$keys)) !== false)
			{
				if($consume)
					self::consume_key();
				self::$was_valid = true;
				return true;
			}
		}
		
		return false;
	}
	
	static function html()
	{
		return '<input type="hidden" name="'. self::$keyname .'" id="'. self::$keyname .'" value="'. self::get_key() .'" />';
	}
	
	static function get_key()
	{
		$key = randString(6);
		self::$keys[] = $key;
		$_SESSION[self::SESSION_VARIABLE] = serialize(self::$keys);	
		return $key;
	}
	
	static function consume_key()
	{
		if(($i = array_search(@$_POST[self::$keyname], self::$keys)) !== false)
		{
			unset(self::$keys[$i]);
			$_SESSION[self::SESSION_VARIABLE] = serialize(self::$keys);
		}
	}
}


<?php
if(!function_exists('sanitize'))
{
	function sanitize($dirtyTrash)
	{
		if(get_magic_quotes_gpc() == 1)
			return $dirtyTrash;
		return addslashes($dirtyTrash);
	}
}

if(!function_exists('dirtify'))
{
	function dirtify($cleanTrash)
	{
		if(get_magic_quotes_gpc() == 1)
		{
			if(!is_array($cleanTrash))
				return stripslashes($cleanTrash);
			return stripslashes_deep($cleanTrash);
		}
		return $cleanTrash;
	}
}

if(!function_exists('stripslashes_deep'))
{
	function stripslashes_deep($cleanTrash)
	{
		$cleanTrash = is_array($cleanTrash) ?
			array_map('stripslashes_deep', $cleanTrash) :
			stripslashes($cleanTrash);
		
		return $cleanTrash;
	}
}

if(!function_exists('htmlentities_deep'))
{
	function htmlentities_deep($dirtyTrash)
	{
		$dirtyTrash = is_array($dirtyTrash) ?
			array_map('htmlentities_deep', $dirtyTrash) :
			(is_string($dirtyTrash) ?
				htmlentities($dirtyTrash) :
				$dirtyTrash);
		
		return 	$dirtyTrash;
	}
}

if(!function_exists('randString'))
{
	function randString($length)
	{
		$string='';
		for ($i=0; $i<$length; $i++)
		{
			switch(mt_rand(1,3))
			{
				case 1:
					$string .= chr(mt_rand(48, 57));
				break;
				case 2:
					$string .= chr(mt_rand(65, 90));
				break;
				case 3:
					$string .= chr(mt_rand(97, 122));
				break;
			}
		}
		return ($string);
	}
}

if(!function_exists('isEmail'))
{
	function isEmail($email)
	{
		if(!preg_match('/^[\w-\.]+@([\w-]+\.)+[\w-]+$/',$email)>0)
			return false;
		return true;
	}
}

if(!function_exists('isPhone'))
{	
	function isPhone($phone)
	{
		if(!preg_match('/^((\(\d{3}\)?)|(\d{3}))([\s-.\/]?)(\d{3})([\s-.\/]?)(\d{4})$/',$phone)>0)
			return false;
		return true;
	}
}

if(!function_exists('linkURLs'))
{
	function linkURLs($text)
	{
		return preg_replace('/https?:\/\/[^\)<\s\n]*/', '<a href="$0" target="_blank">$0</a>', $text);
	}
}

if(!function_exists('formatMoney'))
{
	function formatMoney($cents)
	{
		return number_format(((int)($cents/100) .'.'. (abs($cents%100)<10 ? '0' : '') . abs($cents%100)),2);
	}
}

if(!function_exists('htmlentitiesUTF8'))
{
	function htmlentitiesUTF8($var)
	{
		return htmlentities($var, ENT_QUOTES, 'UTF-8') ;
	}
}

if(!function_exists('truncate_by_words'))
{
	function truncate_by_words($dirty_trash, $n, $add_ellipses = true)
	{
		if(preg_match('/(\S+\s*){'.intval($n).'}/', $dirty_trash, $matches) > 0)
		{
			$newString = trim($matches[0]);
			if($matches[0] != $dirty_trash && $add_ellipses)
				$newString .= '...';
		}
		else
			$newString = trim($dirty_trash);
		
		return $newString;
	}
}

if(!function_exists('logged_query'))
{
	function logged_query($sql)
	{
		if(class_exists('Tabmin') && !empty(Tabmin::$db))
			$rs = mysql_query($sql, Tabmin::$db);
		else
			$rs = mysql_query($sql);
		if($rs)
		{
			QueryLog::add($sql);
			return $rs;
		}
		else
		{
			trigger_error('MySQL Error: '. mysql_error(), E_USER_ERROR);
			Console::add('Error SQL: '. $sql);
		}
		
		return false;
	}
}

if(!function_exists('seconds_to_time'))
{
	function seconds_to_time($time)
	{
		if(is_numeric($time))
		{
			$value = array
			(
				'years' => 0, 
				'days' => 0, 
				'hours' => 0,
				'minutes' => 0, 
				'seconds' => 0
			);
			if($time >= 31556926)
			{
				$value['years'] = floor($time/31556926);
				$time = ($time % 31556926);
			}
			if($time >= 86400)
			{
				$value['days'] = floor($time/86400);
				$time = ($time % 86400);
			}
			if($time >= 3600)
			{
				$value['hours'] = floor($time/3600);
				$time = ($time % 3600);
			}
			if($time >= 60)
			{
				$value['minutes'] = floor($time/60);
				$time = ($time % 60);
			}
			
			$value['seconds'] = floor($time);
			return $value;
		}
		else
			return false;
	}
}

if(!function_exists('time_to_seconds'))
{
	function time_to_seconds($time)
	{
		$seconds = 0;
		$arr = explode(':', $time);
		Console::add(print_r($arr, true), print_r($time, true));
		$seconds = intval($arr[0]*60*60) + $arr[1]*60;
		if(!empty($arr[2])) //seconds
			$seconds += intval($arr[2]);
		
		return $seconds;
	}
}

if(!function_exists('relativeTime'))
{
	function relativeTime($time = false, $limit = 86400, $format = 'g:i A M jS') 
	{
		if (empty($time) || (!is_string($time) && !is_numeric($time))) 
			$time = time();
		elseif (is_string($time)) 
			$time = strtotime($time);
		
		$now = time();
		$relative = '';
		
		if ($time === $now) 
			$relative = 'now';
		elseif ($time > $now) 
			$relative = 'in the future';
		else 
		{
			$diff = $now - $time;
			
			if ($diff >= $limit) 
				$relative = date($format, $time);
			elseif ($diff < 60)
				$relative = 'less than one minute ago';
			elseif (($minutes = ceil($diff/60)) < 60)
				$relative = $minutes.' minute'.(((int)$minutes === 1) ? '' : 's').' ago';
			else 
			{
				$hours = ceil($diff/3600);
				$relative = 'about '.$hours.' hour'.(((int)$hours === 1) ? '' : 's').' ago';
			}
		}
		return $relative;
	}
}

?>

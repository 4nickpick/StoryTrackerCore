<?php
/*
- 12.01.26
	Added addAJAX method
*/
if(!empty($_SESSION['AlertSet']))
{
	AlertSet::load(unserialize($_SESSION['AlertSet']));
	unset($_SESSION['AlertSet']);
}
else
	AlertSet::clear();

class AlertSet
{
	public static $alerts;
	public static $success=true;
	
	public static function addError($msg)
	{
		self::$alerts['error'][] = $msg;
		self::$success = false;
	}
	
	public static function addWarning($msg)
	{
		self::$alerts['warning'][] = $msg;
		self::$success = false;
	}
	
	public static function addInfo($msg)
	{
		self::$alerts['info'][] = $msg;
	}
	
	public static function addValidation($msg)
	{
		self::$alerts['validation'][] = $msg;
		self::$success = false;
	}
	
	public static function addSuccess($msg)
	{
		self::$alerts['success'][] = $msg;
	}

	public static function addQuestion($msg)
	{
		self::$alerts['question'][] = $msg;
	}

	public static function addAJAX($msg)
	{
		self::$alerts['ajax'][] = $msg;
	}
	
	public static function addMySQLDebug($sql, $error, $line = false, $file = false)
	{
		$i = count(self::$alerts['mysql_debug']);
		self::$alerts['mysql_debug'][$i][] = 'MySQL Error (In file '.$file.' on line '.$line.'): '.$error;
		self::$alerts['mysql_debug'][$i][] = 'SQL: '.$sql;
	}
	
	public static function addDebug($msg)
	{
		self::$alerts['debug'][] = $msg;
	}
	
	public static function save()
	{
		$alertset = array('alerts'=>self::$alerts, 'success'=>self::$success);
		$_SESSION['AlertSet']=serialize($alertset);
	}
	
	public static function load($array)
	{
		self::$alerts = $array['alerts'];
		self::$success = $array['success'];
	}
	
	public static function clear()
	{
		self::$alerts=array(
			'error'=>array(),
			'warning'=>array(),
			'validation'=>array(),
			'info'=>array(),
			'success'=>array(),
			'question'=>array(),
			'mysql_debug'=>array(),
			'debug'=>array(),
			'ajax'=>array()
		);
		self::$success=true;
	}
	
	public static function json()
	{
		return json_encode(self::$alerts);
	}
	
	public static function html()
	{
		$html = '';
		
		foreach(self::$alerts as $key => $item)
		{
			if(count(self::$alerts[$key]) != 0)
			{
				$html .= '<div class="AlertSet_'.$key.'">';
				
				if(count(self::$alerts[$key]) > 1)
				{
					$html .= '<ul>';
					
					foreach(self::$alerts[$key] as $propItem)
					{
						$html .= '<li>';
						$html .= $propItem;
						$html .= '</li>';
					}
					$html .= '</ul>';
				}
				else
				{
					$html .= '<div>'.self::$alerts[$key][0].'</div>';	
				}
				$html .= '</div>';
			}
		}
		
		return $html;
	}
}
?>

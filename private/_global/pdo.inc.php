<?

if(!function_exists('pdologged_query'))
{
	function pdologged_query($sql)
	{
		try
		{
			$rs = Tabmin::$db->query($sql);
			if($rs)
			{
				//$row = $rs->fetch(PDO::FETCH_ASSOC);
				//var_dump($row);
				//die();
				QueryLog::add("pdo:\n\n".$sql);				
				return $rs;
			}
			else
			{
				trigger_error('Programmers do not know what happend, but it is bad.', E_USER_ERROR);
				Console::add('Error SQL: '. $sql);
			}
		}
		catch (PDOException  $ex)
		{
			trigger_error('MySQL Error: '. $ex->getMessage(), E_USER_ERROR);
			Console::add('Error SQL: '. $sql);
		}
		
		
		return false;
	}
}

if(!function_exists('pdologged_preparedQuery'))
{
	function pdologged_preparedQuery($stub, $params)
	{
		$sql = $stub;
		try
		{			
			$rs = Tabmin::$db->prepare($stub);			
			if ($rs)
			{			
				if ($params)
				{
					foreach($params as $key=>&$value)
					{
						
						if (is_int($value))
							$rs->bindParam($key, $value, PDO::PARAM_INT);
						else
							$rs->bindParam($key, $value, PDO::PARAM_STR);
						if (DEBUG)
							$sql = str_replace($key,$value,$sql);
					}
				}				
				$s = $rs->execute();				
				if($s)
				{				
					QueryLog::add("pdo:\n\n".$sql);
					QueryLog::add("pdo Stub:\n\n".$stub);					
					return $rs;
				}
			}
			
		}
		catch (PDOException  $ex)
		{		
			trigger_error('MySQL Error: '. $ex->getMessage(), E_USER_ERROR);
			Console::add('Error SQL: '. $sql);
			return false;
		}						
		trigger_error('Programmers do not know what happend, but it is bad.', E_USER_ERROR);
		Console::add('Error SQL: '. $sql);
		return false;
	}
}


if(!function_exists('pdologged_exec'))
{
	function pdologged_exec($sql)
	{
		try
		{
			$count = Tabmin::$db->exec($sql);
			if($count >= 0)
			{				
				QueryLog::add("pdo-exec:\n\n".$sql);
				return $count;
			}			
		}
		catch (PDOException  $ex)
		{
			trigger_error('MySQL Error: '. $ex->getMessage(), E_USER_ERROR);
			Console::add('Error SQL: '. $sql);
		}
		
		
		return false;
	}
}


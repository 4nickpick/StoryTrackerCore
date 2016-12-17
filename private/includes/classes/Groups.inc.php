<?php
class Groups
{
	public static function loadById($table_name, $id)
	{
		
		$sql=
			'SELECT
				'.$table_name.'_groups.id,
				'.$table_name.'_groups.name,
				'.$table_name.'_groups.priority
			FROM
				' . $table_name . '_groups
			WHERE
				'.$table_name.'_groups.id=:id';
				
		$values[':id'] = $id;
		
		if($rs = pdologged_preparedQuery($sql, $values))
		{
			if($row = $rs->fetch(PDO::FETCH_ASSOC))
			{			
				$group = new Group(array
				(
					'id'=>$row['id'],
					'name'=>$row['name'],
					'priority'=>$row['priority']			
				));
				return $group;
			}			
		}
		return false;
	}
	
	public static function updatePriorities($table_name, $series_id, $data, $moved_element)
	{		
		$sql =
			'UPDATE ' . $table_name . '_groups SET
				priority = :priority
			WHERE
				id = :id
			';
		
		$current_group = Groups::loadById($table_name, @$data[$moved_element]);
		$next_group = Groups::loadById($table_name, @$data[$moved_element-1]);
		$last_group = Groups::loadById($table_name, @$data[$moved_element+1]);
		
		$next_priority = $next_group ? $next_group->get_priority() : 0;
		$last_priority = $last_group ? $last_group->get_priority() : NULL;
		
		if( $last_priority == NULL )
			$new_priority = $next_group->get_priority() + 1001;
		else
			$new_priority = intval(($next_priority + $last_priority) / 2);
		
		if( $new_priority == $next_priority || $new_priority == $last_priority || $new_priority < 2 )
		{			
			//trigger priority cleaning, then reattempt the update
			if( !self::cleanPriorities($table_name, $series_id) )
			{
				AlertSet::addError('Could not clean priorities');
				return false;
			}
			return self::updatePriorities($table_name, $series_id, $data, $moved_element);
		}
		
		$values[':id'] = $current_group->get_id();
		$values[':priority'] = $new_priority;
				
		if($rs = pdologged_preparedQuery($sql, $values) )
		{
			return true;
		}
		
		return false;
	}
	
	public static function cleanPriorities($table_name, $series_id)
	{
		$sql = 'SET @row_number = 1;';
		$rs_row_number = pdologged_query($sql);
		if(!$rs_row_number)
		{
			AlertSet::addError('failed to initialize necessary variable');
			return false;
		}
		
		$sql =
			'
			SELECT 
				@row_number:=@row_number+1 AS row_number, 
				id
			FROM 
				'.$table_name.'_groups
			WHERE 
				series_id = :series_id
			ORDER BY 
				priority';
				
		$values[':series_id'] = $series_id;
		if($rs_select = pdologged_preparedQuery($sql, $values) )
		{
			$sql = '
			UPDATE
				'.$table_name.'_groups
			SET
				priority=:priority
			WHERE
				id = :id';
				
			$rows = $rs_select->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows as $row)
			{
				$values = NULL;
				$values[':id'] = $row['id'];
				$values[':priority'] = $row['row_number']*1000;
				$rs_update = pdologged_preparedQuery($sql, $values);
				
				if( !$rs_update )
				{
					AlertSet::addError('a row update failed');
					return false;
				}
				else
				{
					//AlertSet::addSuccess('row update success: ' . $sql);
				}
			}
			
			return true;
		}
		
		AlertSet::addError('select query failed');
		return false;
		
	}
	
	public static function getMaxPriority($table_name, $series_id)
	{
		$sql = 'SELECT MAX(priority) AS max_priority FROM ' . $table_name . '_groups WHERE series_id=:series_id';
		$values[':series_id']=$series_id;
		
		if( $rs = pdologged_preparedQuery($sql, $values) )
		{
			$row = $rs->fetch(PDO::FETCH_ASSOC);
			return $row['max_priority'];
		}
		else
		{
			return false;
		}
	}	
}

class Group
{
	private $id;
	private $series_id;
	private $name;
	private $fields; //array of Field objects
	private $priority;
	
	function __construct($properties=NULL)
	{	
		if( count($properties) >= 1 )
		{
			foreach($properties as $property=>$value)
			{
				if(property_exists($this, $property))
				{
					$this->{"$property"}=$value;
				}
			}
		}
	}
	
	function get_id()
	{
		return intval($this->id);
	}
	
	function get_series_id()
	{
		return intval($this->series_id);
	}
	
	function get_name()
	{
		return $this->name;
	}
	
	function get_fields()
	{
		return $this->fields;
	}
	
	function get_priority()
	{
		return $this->priority;
	}
	
	function add_field($field)
	{
		if( $field instanceof Field )
			$this->fields[] = $field;
		else
			trigger_error('Attempting to add a non-Field to a Group as a Field.');
	
	}
	
	/*
		Setters
	*/
	
	public function set_name($new_name)
	{
		$this->name = $new_name;
	}
	
	public function add($table_name)
	{
		$max_priority = Groups::getMaxPriority($table_name, $this->series_id);
				
		// We need the max priority from the database to add a new record
		// If there are no records, default to priority increment value
		if( $max_priority === false ) return false;
		if( $max_priority === NULL ) $max_priority = 0;
		
		//new record, step up
		$max_priority += 1000;
	
		$sql =
			'INSERT INTO '.$table_name.'_groups 
			(
				series_id,
				name,
				priority
			)
			VALUES
			(
				:series_id,
				:name,
				:priority
			)
			';
			
		if( $this->name == NULL ) $this->name = 'New Group';
			
		$values = array(
			':series_id'=>$this->series_id,
			':name'=>$this->name,
			':priority'=>$max_priority
		);
		if(pdologged_preparedQuery($sql, $values))
		{
			$this->id=Tabmin::$db->lastInsertId();
			return true;
		}
		
		return false;
	}
	
	public function update($table_name)
	{
		$sql =
			'UPDATE '.$table_name.'_groups SET
				name=:name
			WHERE id=:id';
			
		$values[':id'] = $this->get_id();
		$values[':name'] = $this->name;
		
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}
	
	public function delete($table_name)
	{
		$sql =
			'DELETE 
				FROM '.$table_name.'_groups 
			WHERE id=:id';
			
		$values[':id'] = $this->get_id();
		
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}
}
?>
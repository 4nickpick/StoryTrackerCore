<?php
class Fields
{
	public static function loadById($table_name, $id)
	{
		
		$sql=
			'SELECT
				'.$table_name.'_fields.id,
				'.$table_name.'_fields.groups_id,
				'.$table_name.'_fields.name,
				'.$table_name.'_fields.priority
			FROM
				' . $table_name . '_fields
			WHERE
				'.$table_name.'_fields.id=:id';
				
		$values[':id'] = $id;
		
		if($rs = pdologged_preparedQuery($sql, $values))
		{
			if($row = $rs->fetch(PDO::FETCH_ASSOC))
			{			
				$field = new Field(array
				(
					'id'=>$row['id'],
					'groups_id'=>$row['groups_id'],
					'name'=>$row['name'],
					'priority'=>$row['priority']			
				));
				return $field;
			}			
		}
		return false;
	}
	
	public static function updatePriorities($table_name, $users_id, $groups_id, $data, $moved_element)
	{		
		$sql =
			'UPDATE ' . $table_name . '_fields SET
				groups_id = :groups_id,
				priority = :priority
			WHERE
				id = :id
			';
			
		if( count($data) == 1 )
			$moved_element = 0;
		
		$current_field = Fields::loadById($table_name, @$data[$moved_element]);
		$next_field = Fields::loadById($table_name, @$data[$moved_element-1]);
		$last_field = Fields::loadById($table_name, @$data[$moved_element+1]);
				
		// change moved item's Group if necessary
		$new_groups_id = $groups_id;
				
		//determine Field's new priority
		$next_priority = $next_field ? $next_field->get_priority() : 0;
		$last_priority = $last_field ? $last_field->get_priority() : NULL;
		
		if( $last_priority == NULL && $next_priority == 0 )
			$new_priority = 1001;
		else if( $last_priority == NULL )
			$new_priority = $next_priority + 1001;
		else
			$new_priority = intval(($next_priority + $last_priority) / 2);
				
		if( $new_priority == $next_priority || $new_priority == $last_priority || $new_priority < 2 )
		{			
			//trigger priority cleaning, then reattempt the update
			if( !self::cleanPriorities($table_name, $groups_id) )
			{
				AlertSet::addError('Could not clean priorities');
				return false;
			}
			return self::updatePriorities($table_name, $users_id, $groups_id, $data, $moved_element);
		}
		
		$values[':id'] = $current_field->get_id();
		$values[':groups_id'] = $new_groups_id;
		$values[':priority'] = $new_priority;
				
		if($rs = pdologged_preparedQuery($sql, $values) )
		{
			return true;
		}
		
		return false;
	}
	
	public static function cleanPriorities($table_name, $groups_id)
	{
		$sql = 'SET @row_number = 1;';
		$rs_row_number = pdologged_query($sql);
		if(!$rs_row_number)
		{
			AlertSet::addError('Could not set row number');
			return false;
		}
		
		$sql =
			'
			SELECT 
				@row_number:=@row_number+1 AS row_number, 
				id
			FROM 
				'.$table_name.'_fields
			WHERE 
				groups_id = :groups_id
			ORDER BY 
				priority';
				
		$values[':groups_id'] = $groups_id;
		if($rs_select = pdologged_preparedQuery($sql, $values) )
		{
			$sql = '
			UPDATE
				'.$table_name.'_fields
			SET
				priority=:priority
			WHERE
				id = :id';
				
			while($row = $rs_select->fetch(PDO::FETCH_ASSOC))
			{
				$values = NULL;
				$values[':id'] = $row['id'];
				$values[':priority'] = $row['row_number']*1000;
				$rs_update = pdologged_preparedQuery($sql, $values);
				
				if( !$rs_update )
				{
					AlertSet::addError('Row update failed.');
					return false;
				}
			}
			AlertSet::addError('Priorities are clean.');
			return true;
		}
		
		AlertSet::addError('Select failed.');
		return false;
		
	}
	
	public static function getMaxPriority($table_name, $groups_id)
	{
		$sql = 'SELECT MAX(priority) AS max_priority FROM ' . $table_name . '_fields WHERE groups_id=:groups_id';
		$values[':groups_id']=$groups_id;
		
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

class Field
{
	private $id;
	private $groups_id;
	private $name;
	private $priority;
	private $value;

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
		return $this->id;
	}
	
	function get_groups_id()
	{
		return $this->groups_id;
	}
	
	function get_name()
	{
		return $this->name;
	}
	
	function get_value()
	{
		return $this->value;
	}
	
	function get_priority()
	{
		return $this->priority;
	}
	
	/*
		Setters
	*/
	
	function set_name($new_name)
	{
		$this->name = $new_name;
	}
	
	function set_value($new_value)
	{
		$this->value = $new_value;
	}
	
	public function add($table_name)
	{
		$max_priority = Fields::getMaxPriority($table_name, $this->groups_id);
				
		// We need the max priority from the database to add a new record
		// If there are no records, default to priority increment value
		if( $max_priority === false ) return false;
		if( $max_priority === NULL ) $max_priority = 0;
		
		//new record, step up
		$max_priority += 1000;
	
		$sql =
			'INSERT INTO '.$table_name.'_fields 
			(
				groups_id,
				name,
				priority
			)
			VALUES
			(
				:groups_id,
				:name,
				:priority
			)
			';
			
		if( $this->name == NULL ) $this->name = 'New Field';
			
		$values = array(
			':groups_id'=>$this->groups_id,
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
			'UPDATE '.$table_name.'_fields SET
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
				FROM '.$table_name.'_fields
			WHERE id=:id';
			
		$values[':id'] = $this->get_id();
		
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}
	
}
?>
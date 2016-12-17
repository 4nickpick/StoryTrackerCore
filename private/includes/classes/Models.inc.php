<?php

class Models
{
	public static function load($table_name, $series_id, $tobjects_id=NULL)
	{
		$table_name_groups = $table_name . '_groups';
		$table_name_fields = $table_name . '_fields';
		$table_name_meta = $table_name . '_meta';
		
		$load_with_values = intval($tobjects_id) > 0;
						
		$sql = 
			'SELECT DISTINCT
				'.$table_name_groups.'.id AS groups_id,
				'.$table_name_groups.'.name AS groups_name,
				'.$table_name_groups.'.priority AS groups_priority,

                '.$table_name_fields.'.id AS fields_id,
				'.$table_name_fields.'.name AS fields_name,
				'.$table_name_fields.'.priority AS fields_priority';
			
		if( $load_with_values )
			$sql .= ','.$table_name_meta.'.value AS meta_value';

		$sql .= '
			FROM
				'.$table_name_groups.'
			LEFT JOIN '.$table_name_fields.' ON '.$table_name_fields.'.groups_id = '.$table_name_groups.'.id';
			
		if( $load_with_values )
			$sql .='
			LEFT JOIN '.$table_name_meta.' ON '.$table_name_meta.'.fields_id = '.$table_name_fields.'.id AND '.$table_name_meta.'.'.$table_name.'_id = :tobjects_id';
		
		$sql .= '
			WHERE 
				'.$table_name_groups.'.series_id=:series_id
			ORDER BY 
				'.$table_name_groups.'.priority, '.$table_name_fields.'.priority, '.$table_name_groups.'.id';
						
		$values['series_id'] = $series_id;
		
		if( $load_with_values )
			$values['tobjects_id'] = $tobjects_id;
;
		$groups = array();
		if($rs = pdologged_preparedQuery($sql, $values))
		{
			while($row = $rs->fetch(PDO::FETCH_ASSOC))
			{
				// if group in current records doesn't exist, add it
				if( !isset($groups[$row['groups_id']]) )
				{
					$group = new Group(
						array(
							'id'=>$row['groups_id'],
							'name'=>$row['groups_name'],
							'priority'=>$row['groups_priority']
						)
					);
					$groups[$row['groups_id']] = $group;
				}
				//else just select it. 
				else
					$group = $groups[$row['groups_id']];
				
				
				if( intval($row['fields_id']) > 0 )
				{
					//add the new field to the group
					$field = new Field(
						array(
							'id'=>$row['fields_id'],
							'name'=>$row['fields_name'],
							'priority'=>$row['fields_priority']
						)
					);
					
					if( $load_with_values )
						$field->set_value($row['meta_value']);
						
					$group->add_field($field);
				}
			}
					
			//reset array indices
			$groups = array_values($groups);
			
			$model = 
				new Model(
					array(
						'series_id'=>$series_id,
						'groups'=>$groups
					)	
				);
			
			return $model;
		}
		return false;
	}
	
	public static function updateObject($table_name, $objects_id, $data)
	{
		if( count($data) <= 0 )
			return false;
		
		$sql = '
			INSERT INTO
				' . $table_name . '_meta 
			(
				`fields_id`,
				`' . $table_name . '_id`,
				`value` 
			)
			VALUES
			';
		
			$values = array();
			
			$count = count($data);
			$i = 0;
			foreach($data as $field_id=>$value)
			{
				$sql .= '(
					:fields_id_'.intval($i).',
					:objects_id_'.intval($i).',
					:value_'.intval($i) . 
					')';
					
				if( $i != $count-1 )
					$sql .= ',';
					
				$values[':fields_id_'.intval($i)] = $field_id;
				$values[':objects_id_'.intval($i)] = $objects_id;
				$values[':value_'.intval($i)] = $value;
				
				$i++;
			}
			
		$sql .= ' ON DUPLICATE KEY UPDATE `value`=VALUES(value)';
		
		if($rs = pdologged_preparedQuery($sql, $values))
		{
			return true;
		}

        return false;
	}

    public static function generateDefaultCharacterModel($series_id)
    {
        /* Group */
        $group = new Group(array('name'=>'Biographical Information', 'series_id'=>$series_id));
        if( $group->add('characters') )
        {
            /* Age Field */
            $field_properties = array(
                'name'=>'Age',
                'groups_id'=>$group->get_id()
            );
            $field = new Field($field_properties);
            $field->add('characters');

        }
        else
        {
            return false;
        }

        /* Group */
        $group = new Group(array('name'=>'Physical Description', 'series_id'=>$series_id));
        if( $group->add('characters') )
        {
            /* Gender Field */
            $field_properties = array(
                'name'=>'Gender',
                'groups_id'=>$group->get_id()
            );
            $field = new Field($field_properties);
            $field->add('characters');

            /* Hair Color Field */
            $field_properties = array(
                'name'=>'Hair Color',
                'groups_id'=>$group->get_id()
            );
            $field = new Field($field_properties);
            $field->add('characters');

        }
        else
        {
            return false;
        }

        return true;
    }

    public static function generateDefaultSettingModel($series_id)
    {
        /* Group */
        $group = new Group(array('name'=>'Geographical Information', 'series_id'=>$series_id));
        if( $group->add('settings') )
        {
            /* Age Field */
            $field_properties = array(
                'name'=>'Weather',
                'groups_id'=>$group->get_id()
            );
            $field = new Field($field_properties);
            $field->add('settings');

        }
        else
        {
            return false;
        }

        /* Group */
        $group = new Group(array('name'=>'Cultural Information', 'series_id'=>$series_id));
        if( $group->add('settings') )
        {
            //Language field
            $field_properties = array(
                'name'=>'Language',
                'groups_id'=>$group->get_id()
            );
            $field = new Field($field_properties);
            $field->add('settings');

            //Customs field
            $field_properties = array(
                'name'=>'Customs',
                'groups_id'=>$group->get_id()
            );
            $field = new Field($field_properties);
            $field->add('settings');


        }
        else
        {
            return false;
        }

        return true;
    }
}

class Model
{
	private $groups; //array of Group objects
	private $series_id;
	
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
	
	function get_series_id()
	{
		return $this->series_id;
	}
	
	function get_groups()
	{
		return $this->groups;
	}
	
	
	function dump()
	{
		foreach($this->groups as $group)
		{
			echo '<strong>' . $group->getName() . '</strong><br />';
			foreach($group->getFields() as $field)
			{
				echo '<em>'.$field->getName().'</em><br />';
			}
		}
	}
}
?>
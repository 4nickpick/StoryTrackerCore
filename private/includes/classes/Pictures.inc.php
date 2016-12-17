<?php
class Pictures extends Tobjects
{
	function __construct()
	{
		$this->search_keys = array('pictures.caption');
		$this->table_name = 'pictures';
	}

    public function loadByUntagged($search, $users_id, $order_by='pictures.id DESC')
    {
        $values = array();

        $where = 'pictures.users_id = :users_id AND ';
        $values[':users_id'] = $users_id;
        if( strlen($search) > 0 )
        {
            $where .= 'pictures.caption LIKE :search AND ';
            $values[':search'] = $search;
        }

        $where.= ' pictures_to_characters.characters_id IS NULL
            AND pictures_to_settings.settings_id IS NULL
            AND pictures_to_plot_events.plot_events_id IS NULL';

        return $this->load($where, $values, true, $order_by);
    }

    public function loadByTags($search, $users_id, $character_tags, $settings_tags, $plot_event_tags, $order_by)
    {
        $values = array();

        $where = 'pictures.users_id = :users_id ';
        $values[':users_id'] = $users_id;
        if( strlen($search) > 0 )
        {
            $where .= 'pictures.caption LIKE :search AND ';
            $values[':search'] = $search;
        }

        if( count($character_tags) > 0 )
        {
            $where .= ' AND pictures_to_characters.characters_id IN (';
            foreach ($character_tags as $i=>$id)
            {
                $where.=':characters_id'.$i . ((count($character_tags) - 1) > $i ? ',' : '');
                $values[':characters_id'.$i] = $id;
            }
            $where .= ')';
        }

        if( count($settings_tags) > 0 )
        {
            $where .= ' AND pictures_to_settings.settings_id IN (';
            foreach ($settings_tags as $i=>$id)
            {
                $where.=':settings_id'.$i . ((count($settings_tags) - 1) > $i ? ',' : '');
                $values[':settings_id'.$i] = $id;
            }
            $where .= ')';
        }

        if( count($plot_event_tags) > 0 )
        {
            $where .= ' AND pictures_to_plot_events.plot_events_id IN (';
            foreach ($plot_event_tags as $i=>$id)
            {
                $where.=':plot_events_id'.$i . ((count($plot_event_tags) - 1) > $i ? ',' : '');
                $values[':plot_events_id'.$i] = $id;
            }
            $where .= ')';
        }

        return $this->load($where, $values, true, $order_by);
    }

	protected function load($where='', $values=NULL, $return_array=false, $order_by='', $limit='')
	{
		if($where!='')
			$where='WHERE '.$where;

		if($order_by!='')
			$order_by='ORDER BY '.$order_by;
		
		$sql =
			'SELECT
				pictures.id,
				users_id,
				caption,
				sort_order,
				GROUP_CONCAT(pictures_to_characters.characters_id) AS characters_ids,
				GROUP_CONCAT(pictures_to_settings.settings_id) AS settings_ids,
				GROUP_CONCAT(pictures_to_plot_events.plot_events_id) AS plot_events_ids
			FROM
				pictures
			LEFT JOIN pictures_to_characters ON pictures.id = pictures_to_characters.pictures_id
			LEFT JOIN pictures_to_settings ON pictures.id = pictures_to_settings.pictures_id
			LEFT JOIN pictures_to_plot_events ON pictures.id = pictures_to_plot_events.pictures_id
			'.$where.'
			GROUP BY pictures.id
			'.$order_by.'';

        $this->found_rows=0;
		if($rs = pdologged_preparedQuery($sql, $values))
		{
            $this->found_rows=$rs->rowCount();
			$data=array();
			while($row = $rs->fetch(PDO::FETCH_ASSOC))
			{
				$data[] = new Picture(array
				(
					'id'=>$row['id'],
					'users_id'=>$row['users_id'],
					'caption'=>$row['caption'],
					'sort_order'=>$row['sort_order'],
					'characters_ids'=>$row['characters_ids'],
					'settings_ids'=>$row['settings_ids'],
					'plot_events_ids'=>$row['plot_events_ids']
				));
				
				if(!$return_array)
					return $data[0];
			}
			return $data;
		}
		return false;
	}

	public function loadByStory($story_id)
	{
        $values = array();
        $return_array = true;

        $where='WHERE characters_to_stories.stories_id = :stories_id1';
        $values[':stories_id1'] = $story_id;

        $where .=' OR settings_to_stories.stories_id = :stories_id2';
        $values[':stories_id2'] = $story_id;

        $where .=' OR plot_events_to_stories.stories_id = :stories_id3';
        $values[':stories_id3'] = $story_id;

        $order_by='ORDER BY RAND()';

		$sql =
			'SELECT
				pictures.id,
				users_id,
				caption,
				sort_order
			FROM
				pictures
			LEFT JOIN pictures_to_characters ON pictures.id = pictures_to_characters.pictures_id
			LEFT JOIN pictures_to_settings ON pictures.id = pictures_to_settings.pictures_id
			LEFT JOIN pictures_to_plot_events ON pictures.id = pictures_to_plot_events.pictures_id
			LEFT JOIN characters_to_stories ON characters_to_stories.characters_id = pictures_to_characters.characters_id
			LEFT JOIN settings_to_stories ON settings_to_stories.settings_id = pictures_to_settings.settings_id
			LEFT JOIN plot_events_to_stories ON plot_events_to_stories.plot_events_id = pictures_to_plot_events.plot_events_id
			'.$where.'
			GROUP BY pictures.id
			'.$order_by.'';

        $this->found_rows=0;
		if($rs = pdologged_preparedQuery($sql, $values))
		{
            $this->found_rows=$rs->rowCount();
			$data=array();
			while($row = $rs->fetch(PDO::FETCH_ASSOC))
			{
				$data[] = new Picture(array
				(
					'id'=>$row['id'],
					'users_id'=>$row['users_id'],
					'caption'=>$row['caption'],
					'sort_order'=>$row['sort_order']
				));

				if(!$return_array)
					return $data[0];
			}
			return $data;
		}
		return false;
	}

    public static function getCoverPhotoByTag($table_name, $column_name, $related_objects_id)
    {
        $sql =
            'SELECT
                pictures_id
            FROM
                ' . $table_name . '
            WHERE ' . $column_name . ' = :object_id
            AND cover_photo=1
            LIMIT 1';

        $values[':object_id'] = $related_objects_id;
        if($rs = pdologged_preparedQuery($sql, $values))
        {
            $row = $rs->fetch(PDO::FETCH_ASSOC);
            return $row['pictures_id'];
        };

        return false;
    }
	public static function sort($order)
	{
		if(!is_array($order))
			$order = explode(',', $order);
			
		$sql=
			'UPDATE
				pictures
			SET
				sort_order = CASE ';
		
		for($i=0; $i<count($order); $i++)
		{
			$order[$i]=intval($order[$i]);
			$sql .= '
			  WHEN id='.$order[$i].' THEN '.$i;	
		}
		
		$sql .=
			' END
			WHERE id IN('.implode(',', $order).')';
		
		if(pdologged_exec($sql))
			return true;
		
		return false;
	}

    public static function sortByTag($order, $table_name, $column_name, $objects_id)
	{
		if(!is_array($order))
			$order = explode(',', $order);

		$sql=
			'UPDATE
				'.addslashes($table_name).'
			SET
				priority = CASE ';

		for($i=0; $i<count($order); $i++)
		{
			$order[$i]=intval($order[$i]);
			$sql .= '
			  WHEN pictures_id='.$order[$i].' AND ' . addslashes($column_name) . '=' . intval($objects_id) . ' THEN '.$i;
		}

		$sql .=
			' END
			WHERE pictures_id IN('.implode(',', $order).')';

		if(pdologged_exec($sql))
			return true;

		return false;
	}
}

class Picture extends Tobject
{
	protected $id, $caption, $users_id, $sort_order;
    protected $characters_ids, $settings_ids, $plot_events_ids;
	
	function __construct($properties)
	{
		foreach($properties as $property=>$value)
		{
			if(property_exists($this, $property))
				$this->{"$property"}=$value;
		}
	}
	
	public function get_id()
	{
		return intval($this->id);
	}

    public function set_id($id)
    {
        $this->id = intval($id);
    }

	public function get_users_id()
	{
		return intval($this->users_id);
	}
	
	public function get_caption()
	{
		return ($this->caption);
	}

	public function get_characters_ids()
	{
		return ($this->characters_ids);
	}

	public function get_settings_ids()
	{
		return ($this->settings_ids);
	}

	public function get_plot_events_ids()
	{
		return ($this->plot_events_ids);
	}

    public function get_characters()
    {
        $charactersLoader = new Characters();
        $parameters['pictures_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$this->id);
        return $charactersLoader->loadByParameters($parameters);
    }

    public function get_settings()
    {
        $settingsLoader = new Settings();
        $parameters['pictures_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$this->id);
        return $settingsLoader->loadByParameters($parameters);
    }

    public function get_plot_events()
    {
        $loader = new PlotEvents();
        $parameters['pictures_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$this->id);
        return $loader->loadByParameters($parameters);
    }

	public function add()
	{
		$sql =
			'INSERT INTO pictures
			(
				caption,
				users_id,
				sort_order
				
			)
			SELECT			
				"'.addslashes($this->caption).'",
				'.intval($this->users_id).',
				MAX(sort_order)+1
			FROM pictures';
		if(pdologged_preparedQuery($sql, array()))
		{
			$this->id=Tabmin::$db->lastInsertId();
			return true;
		}
		
		return false;
	}


    public function addRelationship($table_name, $column_name, $related_object_id)
    {
        $sql =
            'INSERT INTO ' . $table_name . '
            (
                pictures_id,
                ' . $column_name . ',
                priority
            )
            VALUES
            (
                :pictures_id,
                :' . $column_name . ',
                (
                    SELECT
                        COALESCE(MAX(priority)+1,0)
                    FROM ' . $table_name . ' AS t1
                    WHERE ' . $column_name. '=:' . $column_name. '2
                )
            )
            ';

        $values = array(
            ':pictures_id'=>$this->id,
            ':' . $column_name=>$related_object_id,
            ':' . $column_name.'2'=>$related_object_id
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            $this->id=Tabmin::$db->lastInsertId();

            return true;
        }

        return false;
    }

    public function removeRelationship($table_name, $column_name, $related_object_id)
    {
        $sql =
            '
            DELETE FROM ' . $table_name . '
            WHERE
                pictures_id = :pictures_id
                AND ' . $column_name . ' = :' . $column_name . ';
            ';

        $values = array(
            ':pictures_id'=>$this->id,
            ':' . $column_name=>$related_object_id
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            return true;
        }

        return false;
    }


    public function setCoverPhoto($table_name, $column_name,  $related_object_id)
    {
        $sql =
            'UPDATE ' . $table_name . ' SET
                cover_photo=0
			WHERE ' . $column_name . '=:object_id';

        $values = array(
            ':object_id'=>$related_object_id
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            $sql =
                'UPDATE ' . $table_name . ' SET
                    cover_photo=1
                WHERE pictures_id=:pictures_id AND ' . $column_name . '=:object_id';

            $values = array(
                ':pictures_id'=>$this->id,
                ':object_id'=>$related_object_id
            );
            if(pdologged_preparedQuery($sql, $values))
            {
                return true;
            }
        }

        return false;
    }


    public function update()
	{
		$sql =
			'UPDATE pictures SET
				caption="'.addslashes($this->caption).'"
			WHERE id='.intval($this->id);

		if(pdologged_exec($sql) !== false)
        {
            return true;
        }
		return false;
	}
	
	public function delete()
	{
		$sql = 
			'DELETE FROM
				picture_files
			WHERE
				pictures_id='.intval($this->id);

        pdologged_exec($sql);


        $sql =
            'DELETE FROM
                pictures
            WHERE
                id='.intval($this->id).'
            LIMIT 1';
        if(pdologged_exec($sql))
        {
            return true;
        }

		return false;
	}
}
?>
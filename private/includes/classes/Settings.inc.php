<?php
class Settings extends Tobjects
{
	function __construct()
	{
		$this->search_keys = array('settings.full_name', 'settings.aliases');
		$this->table_name = 'settings';
	}

    public function loadByRelationship($column_name, $related_record_id, $currentStoryId)
    {
        $parameters[$column_name] = array('type'=>'int', 'condition'=>'=', 'value'=>$related_record_id);
        $parameters['stories_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStoryId);
        return $this->loadByParameters($parameters);
    }

    public function searchLoadBySeries($search, $series_id, $stories_id, $return_array=true, $order_by='', $limit='')
    {
        $parameters['settings.series_id']=array('type'=>'int', 'condition'=>'=', 'value'=>$series_id);
        $parameters['settings_to_stories.stories_id']=array('type'=>'int', 'condition'=>'!=', 'value'=>$stories_id);

        return $this->searchLoadByParameters($search, $parameters, $return_array, $order_by, $limit);
    }

    public function loadById($id)
    {
        global $currentStory;
        if (!$this->table_name || $this->table_name == '')
            trigger_error('Table name is not defined',  E_USER_ERROR);

        $where='settings.id= :id AND settings_to_stories.stories_id=:stories_id';
        $values[':id'] = intval($id);
        $values[':stories_id'] = intval($currentStory->get_id());

        return $this->load($where, $values, false);
    }


    protected function load($where='', $values=NULL, $return_array=false, $order_by='settings_to_stories.priority', $limit='')
	{
		if($where!='')
			$where='WHERE '.$where;
		
		if($order_by!='')
			$order_by='ORDER BY '.$order_by;
		
		$sql_calc_found_rows='';
		if($limit!='')
		{
			$limit='LIMIT '.$limit;
			$sql_calc_found_rows='SQL_CALC_FOUND_ROWS';
		}
		
		$sql=
			'SELECT DISTINCT '.$sql_calc_found_rows.'
				settings.id,
				full_name,
				aliases,
				
				settings_content.content AS content,

				settings_to_stories.stories_id AS stories_id,
				settings_to_stories.priority AS priority,

				stories.series_id AS series_id,
				stories.name AS stories_name,

				series.users_id AS users_id,
				series.name AS series_name

			FROM
				settings
			LEFT JOIN settings_content ON settings_content.settings_id=settings.id
			LEFT JOIN settings_to_stories ON settings_to_stories.settings_id=settings.id
			LEFT JOIN settings_to_plot_events ON settings_to_plot_events.settings_id=settings.id
			LEFT JOIN pictures_to_settings ON pictures_to_settings.settings_id=settings.id
			LEFT JOIN stories ON settings_to_stories.stories_id=stories.id
			LEFT JOIN series ON stories.series_id=series.id
			'.$where.'
			'.$order_by.'
			'.$limit;
		
		$this->found_rows=0;
		$data=array();
		if($rs = pdologged_preparedQuery($sql, $values))
		{
			if(!empty($limit))
			{
				$rs_count = pdologged_query('SELECT FOUND_ROWS()');
				if($row = $rs_count->fetch(PDO::FETCH_NUM))
					$this->found_rows=$row[0];
			}
			else
				$this->found_rows = $rs->rowCount();
			
			while($row = $rs->fetch(PDO::FETCH_ASSOC))
			{			
				$setting = new Setting(array
				(
					'id'=>$row['id'],
					'story'=>array(
                        'id'=>$row['stories_id'],
                        'name'=>$row['stories_name'],
                    ),
					'stories'=>NULL,
					'series_id'=>$row['series_id'],
					'users_id'=>$row['users_id'],
					'full_name'=>$row['full_name'],
					'aliases'=>$row['aliases'],
					'content'=>$row['content'],
					'priority'=>$row['priority']
				));
				
				
				$data[] = $setting;
				
				if(!$return_array)
					return $data[0];
			}			
			
			return $data;
		}
		return false;
	}
	
	public function loadModel($series_id, $object_id=NULL)
	{
		return Models::load($this->table_name, $series_id, $object_id);
	}

    public function getMaxPriority($stories_id)
    {
        $sql = 'SELECT MAX(priority) AS max_priority FROM settings_to_stories WHERE stories_id=:stories_id';
        $values[':stories_id']=$stories_id;

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

    public function updatePriorities($data, $moved_element)
    {
        global $currentStory;
        $sql =
            'UPDATE settings_to_stories SET
				priority = :priority
			WHERE
				stories_id = :stories_id AND
				settings_id = :settings_id
			';

        $setting = $this->loadById(@$data[$moved_element]);
        $values = array(
            'settings_id'=>$setting->get_id(),
            'stories_id'=>$currentStory->get_id()
        );

        return parent::updatePriorities($data, $moved_element, $sql, $values);
    }

    //write and pass the appropriate queries to Tobjects::cleanPriorities
    public function cleanPriorities($settings_id){
        $select_sql = '
            SELECT
                @row_number:=@row_number+1 AS row_number,
                settings_id,
                stories_id
            FROM
                settings_to_stories
            WHERE
                settings_id = :settings_id
            ORDER BY
                priority';

        $select_values[':settings_id'] = $settings_id;

        $update_sql = '
         UPDATE
                settings_to_stories
            SET
                priority=:priority
            WHERE
                settings_id = :settings_id AND
                stories_id = :stories_id
        ';

        $update_keys = array('settings_id', 'stories_id');

        parent::cleanPriorities($settings_id, $select_sql, $select_values, $update_sql, $update_keys);
    }
}

class Setting extends Tobject
{
	protected $id;
	protected $series_id;
	protected $users_id;
	protected $story;
	protected $stories; // used in existing setting search only
	protected $full_name, $aliases;
	protected $model;
	protected $priority;
	protected $content;
	
	function __construct($properties=NULL)
	{
		$table_name = 'settings';
		parent::__construct($table_name,$properties);
		
		$this->set_aliases($this->aliases); //clean aliases properly
		$this->model = Models::load($table_name, $this->series_id, $this->id);

        if( isset($properties['story']) )
            $this->story = new Story($properties['story']);
	}
	
	public function get_id()
	{
		return intval($this->id);
	}

    public function get_story()
    {
        return ($this->story);
    }

    public function get_stories()
    {
        return ($this->stories);
    }

    public function get_users_id()
    {
        return intval($this->users_id);
    }

    public function get_series_id()
    {
        return intval($this->series_id);
    }

	public function get_full_name()
	{
		return ($this->full_name);
	}	
		
	public function get_aliases()
	{
		return ($this->aliases);
	}
	
	public function get_aliases_list()
	{
		return explode(',',$this->aliases);
	}
	
	public function get_model()
	{
		return ($this->model);
	}
	
	public function get_content()
	{
		return ($this->content);
	}

    public function get_priority()
    {
        return ($this->priority);
    }
	
	
	/* Setters */
	
	public function set_aliases($dirty_aliases)
	{
		$aliases_to_clean = explode(',', $dirty_aliases);
		$clean_aliases = array();
		foreach($aliases_to_clean as $dirty_alias)
		{
			$clean_aliases[] = trim(htmlentitiesUTF8($dirty_alias));
		}
		
		$clean_aliases = implode(',', $clean_aliases);
		
		$this->aliases = $clean_aliases;
	}
		
	public function add()
	{
		$sql =
			'INSERT INTO settings
			(
				full_name,
				aliases,
				series_id
			)
			VALUES
			(
				:full_name,
				:aliases,
				:series_id
			)
			';
			
		$values = array(
			':full_name'=>$this->full_name,
			':aliases'=>$this->aliases,
			':series_id'=>$this->series_id
		);
		if(pdologged_preparedQuery($sql, $values))
		{
			$this->id=Tabmin::$db->lastInsertId();

			if( !$this->updateContent() )
				return false;

            if( !$this->addToStory($this->priority) )
                return false;

            return true;
		}
		
		return false;
	}
	
	public function update()
	{
		$sql_simpleFields = $this->generateSimpleUpdateFields(array('content', 'priority', 'stories_id', 'users_id'));
		
		$sql =
			'UPDATE settings SET
				'.$sql_simpleFields->getStub().'
			WHERE id=:id';
		$values = $sql_simpleFields->getValues();
		$values[':id'] = $this->get_id();
		if(pdologged_preparedQuery($sql, $values) !== false)
		{
			if( $this->updateContent() )
				return true;
			else
				return false;
		}
		return false;
	}	
	
	public function updateContent()
	{		
		$sql =
			'INSERT INTO settings_content
			(
				content,
				settings_id
			)
			VALUES
			(
				:content,
				:settings_id
			)
			ON DUPLICATE KEY UPDATE `content`=VALUES(content)';
		$values[':settings_id'] = $this->get_id();
		$values[':content'] = $this->get_content();
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}

    public function addToStory()
    {
        $settingManager = new Settings();
        $max_priority = $settingManager->getMaxPriority($this->get_story()->get_id());

        // We need the max priority from the database to add a new record
        // If there are no records, default to priority increment value
        if( $max_priority === false ) return false;
        if( $max_priority === NULL ) $max_priority = 0;

        //new record, step up
        $max_priority += 1000;

        $sql =
            'INSERT INTO settings_to_stories
            (
                stories_id,
                settings_id,
                priority
            )
            VALUES
            (
                :stories_id,
                :settings_id,
                :priority
            )
            ON DUPLICATE KEY UPDATE `priority`=VALUES(priority)';
        $values[':stories_id'] = $this->get_story()->get_id();
        $values[':settings_id'] = $this->get_id();
        $values[':priority'] = $this->get_priority() ? $this->get_priority() : $max_priority;
        if(pdologged_preparedQuery($sql, $values) !== false)
            return true;
        return false;
    }

    function updatePriority()
    {
        //addToStory functions as update also
        $this->addToStory();
    }

    public function removeFromStory()
    {
        $sql =
            'DELETE FROM settings_to_stories
            WHERE stories_id = :stories_id AND settings_id = :settings_id';
        $values[':stories_id'] = $this->get_story()->get_id();
        $values[':settings_id'] = $this->get_id();

        if(pdologged_preparedQuery($sql, $values) !== false)
            return true;
        return false;
    }

    public function delete()
    {
        global $currentUser;

        if( parent::delete() )
        {
            $sql = 'DELETE FROM settings_to_stories WHERE settings_id=:settings_id';
            $values[':settings_id'] = $this->get_id();
            pdologged_preparedQuery($sql, $values);
        }
        return true;
    }
}
?>
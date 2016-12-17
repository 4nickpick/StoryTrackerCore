<?php
class PlotEvents extends Tobjects
{
	function __construct()
	{
		$this->search_keys = array('plot_events.event', 'plot_events.summary', 'plot_events.outline');
		$this->table_name = 'plot_events';
	}

    public function loadByRelationship($column_name, $related_record_id, $currentStoryId)
    {
        $parameters[$column_name] = array('type'=>'int', 'condition'=>'=', 'value'=>$related_record_id);
        $parameters['stories_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStoryId);
        return $this->loadByParameters($parameters);
    }

    public function loadByCharacter($characters_id)
    {
        $where = 'characters_id=:characters_id';
        $values[':characters_id']=$characters_id;
        return $this->load($where, $values, true);
    }

    public function searchLoadBySeries($search, $series_id, $stories_id, $return_array=true, $order_by='', $limit='')
    {
        $parameters['plot_events.series_id']=array('type'=>'int', 'condition'=>'=', 'value'=>$series_id);
        $parameters['plot_events_to_stories.stories_id']=array('type'=>'int', 'condition'=>'!=', 'value'=>$stories_id);

        return $this->searchLoadByParameters($search, $parameters, $return_array, $order_by, $limit);
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='plot_events_to_stories.priority', $limit='')
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
				plot_events.id,
				event,
				summary,
				outline,

				plot_events_to_stories.stories_id AS stories_id,
				plot_events_to_stories.priority AS priority,

				stories.series_id AS series_id,
				stories.name AS stories_name,

				series.users_id AS users_id,
				series.name AS series_name

			FROM
				plot_events
			LEFT JOIN plot_events_to_stories ON plot_events_to_stories.plot_events_id=plot_events.id
			LEFT JOIN characters_to_plot_events ON characters_to_plot_events.plot_events_id=plot_events.id
			LEFT JOIN settings_to_plot_events ON settings_to_plot_events.plot_events_id=plot_events.id
			LEFT JOIN pictures_to_plot_events ON pictures_to_plot_events.plot_events_id=plot_events.id
			LEFT JOIN stories ON plot_events_to_stories.stories_id=stories.id
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
				$plot_event = new PlotEvent(array
				(
					'id'=>$row['id'],
					'story'=>array(
                        'id'=>$row['stories_id'],
                        'name'=>$row['stories_name'],
                    ),
					'stories'=>NULL,
					'series_id'=>$row['series_id'],
					'users_id'=>$row['users_id'],
					'event'=>$row['event'],
					'summary'=>$row['summary'],
					'outline'=>$row['outline'],
					'priority'=>$row['priority']
				));
				
				
				$data[] = $plot_event;
				
				if(!$return_array)
					return $data[0];
			}			
			
			return $data;
		}
		return false;
	}

    public function getMaxPriority($stories_id)
    {
        $sql = 'SELECT MAX(priority) AS max_priority FROM plot_events_to_stories WHERE stories_id=:stories_id';
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
            'UPDATE plot_events_to_stories SET
				priority = :priority
			WHERE
				stories_id = :stories_id AND
				plot_events_id = :plot_events_id
			';

        $plot_event = $this->loadById(@$data[$moved_element]);
        $values = array(
            'plot_events_id'=>$plot_event->get_id(),
            'stories_id'=>$currentStory->get_id()
        );

        return parent::updatePriorities($data, $moved_element, $sql, $values);
    }

    //write and pass the appropriate queries to Tobjects::cleanPriorities
    public function cleanPriorities($plot_events_id){
        $select_sql = '
            SELECT
                @row_number:=@row_number+1 AS row_number,
                plot_events_id,
                stories_id
            FROM
                plot_events_to_stories
            WHERE
                plot_events_id = :plot_events_id
            ORDER BY
                priority';

        $select_values[':plot_events_id'] = $plot_events_id;

        $update_sql = '
         UPDATE
                plot_events_to_stories
            SET
                priority=:priority
            WHERE
                plot_events_id = :plot_events_id AND
                stories_id = :stories_id
        ';

        $update_keys = array('plot_events_id', 'stories_id');

        parent::cleanPriorities($plot_events_id, $select_sql, $select_values, $update_sql, $update_keys);
    }
}

class PlotEvent extends Tobject
{
	protected $id;
	protected $series_id;
	protected $users_id;
	protected $story;
    protected $characters;
    protected $settings;
	protected $event;
	protected $priority;
	protected $summary, $outline;
	
	function __construct($properties=NULL)
	{
		$table_name = 'plot_events';
		parent::__construct($table_name,$properties);

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

    public function get_characters()
    {
        $charactersLoader = new Characters();
        return $charactersLoader->loadByRelationship('plot_events_id', $this->id, $this->story->get_id());
    }

    public function get_settings()
    {
        $settingsLoader = new Settings();
        return $settingsLoader->loadByRelationship('plot_events_id', $this->id, $this->story->get_id());
    }

    public function get_users_id()
    {
        return intval($this->users_id);
    }

    public function get_series_id()
    {
        return intval($this->series_id);
    }

	public function get_event()
	{
		return ($this->event);
	}

	public function get_summary()
	{
		return ($this->summary);
	}

	public function get_outline()
	{
		return ($this->outline);
	}

    public function get_priority()
    {
        return ($this->priority);
    }
	


	public function add()
	{
		$sql =
			'INSERT INTO plot_events
			(
				event,
				series_id,
				summary,
				outline
			)
			VALUES
			(
				:event,
				:series_id,
				:summary,
				:outline
			)
			';
			
		$values = array(
			':event'=>$this->event,
			':series_id'=>$this->series_id,
			':summary'=>$this->summary,
			':outline'=>$this->outline
		);
		if(pdologged_preparedQuery($sql, $values))
		{
			$this->id=Tabmin::$db->lastInsertId();

            if( !$this->addToStory($this->priority) )
                return false;

            return true;
		}
		
		return false;
	}
	
	public function update()
	{
		$sql_simpleFields = $this->generateSimpleUpdateFields(array('priority', 'stories_id', 'users_id'));
		
		$sql =
			'UPDATE plot_events SET
				'.$sql_simpleFields->getStub().'
			WHERE id=:id';
		$values = $sql_simpleFields->getValues();
		$values[':id'] = $this->get_id();
		if(pdologged_preparedQuery($sql, $values) !== false)
		{
			return true;
		}
		return false;
	}

    public function addRelationship($table_name, $column_name, $related_object_id)
    {
        $sql =
            'INSERT INTO ' . $table_name . '
            (
                plot_events_id,
                ' . $column_name . '
            )
            VALUES
            (
                :event,
                :' . $column_name . '
            )
            ';

        $values = array(
            ':event'=>$this->id,
            ':' . $column_name=>$related_object_id
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
                plot_events_id = :event
                AND ' . $column_name . ' = :' . $column_name . ';
            ';

        $values = array(
            ':event'=>$this->id,
            ':' . $column_name=>$related_object_id
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            return true;
        }

        return false;
    }

    public function addToStory()
    {
        $eventManager = new PlotEvents();
        $max_priority = $eventManager->getMaxPriority($this->get_story()->get_id());

        // We need the max priority from the database to add a new record
        // If there are no records, default to priority increment value
        if( $max_priority === false ) return false;
        if( $max_priority === NULL ) $max_priority = 0;

        //new record, step up
        $max_priority += 1000;

        $sql =
            'INSERT INTO plot_events_to_stories
            (
                stories_id,
                plot_events_id,
                priority
            )
            VALUES
            (
                :stories_id,
                :plot_events_id,
                :priority
            )
            ON DUPLICATE KEY UPDATE `priority`=VALUES(priority)';
        $values[':stories_id'] = $this->get_story()->get_id();
        $values[':plot_events_id'] = $this->get_id();
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
            'DELETE FROM plot_events_to_stories
            WHERE stories_id = :stories_id AND plot_events_id = :plot_events_id';
        $values[':stories_id'] = $this->get_story()->get_id();
        $values[':plot_events_id'] = $this->get_id();

        if(pdologged_preparedQuery($sql, $values) !== false)
            return true;
        return false;
    }

    public function delete()
    {
        global $currentUser;

        if( parent::delete() )
        {
            $sql = 'DELETE FROM plot_events_to_stories WHERE plot_events_id=:plot_events_id';
            $values[':plot_events_id'] = $this->get_id();
            pdologged_preparedQuery($sql, $values);
        }
        return true;
    }
}
?>
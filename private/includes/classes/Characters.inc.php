<?php
class Characters extends Tobjects
{
	function __construct()
	{
		$this->search_keys = array('characters.full_name', 'characters.aliases');
		$this->table_name = 'characters';
	}

    public function loadByRelationship($column_name, $related_record_id, $currentStoryId)
    {
        $parameters[$column_name] = array('type'=>'int', 'condition'=>'=', 'value'=>$related_record_id);
        $parameters['stories_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStoryId);
        return $this->loadByParameters($parameters);
    }

    public function searchLoadBySeries($search, $series_id, $stories_id, $return_array=true, $order_by='', $limit='')
    {
        $parameters['stories.series_id']=array('type'=>'int', 'condition'=>'=', 'value'=>$series_id);
        $parameters['characters_to_stories.stories_id']=array('type'=>'int', 'condition'=>'!=', 'value'=>$stories_id);

        return $this->searchLoadByParameters($search, $parameters, $return_array, $order_by, $limit);
    }

    //second parameter, $stories_id, removed
    public function loadById($id)
    {
        global $currentStory;
        if (!$this->table_name || $this->table_name == '')
            trigger_error('Table name is not defined',  E_USER_ERROR);

        $where='characters.id= :id AND characters_to_stories.stories_id=:stories_id';
        $values[':id'] = intval($id);
        $values[':stories_id'] = intval($currentStory->get_id());

        return $this->load($where, $values, false);
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='characters_to_stories.priority', $limit='')
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
				characters.id,
				full_name,
				aliases,
				
				characters_content.content AS content,

				characters_to_stories.stories_id AS stories_id,
				characters_to_stories.priority AS priority,

				stories.series_id AS series_id,
				stories.name AS stories_name,

				series.users_id AS users_id,
				series.name AS series_name

			FROM
				characters
			LEFT JOIN characters_content ON characters_content.characters_id=characters.id
			LEFT JOIN characters_to_stories ON characters_to_stories.characters_id=characters.id
			LEFT JOIN characters_to_plot_events ON characters_to_plot_events.characters_id=characters.id
			LEFT JOIN pictures_to_characters ON pictures_to_characters.characters_id=characters.id
			LEFT JOIN stories ON characters_to_stories.stories_id=stories.id AND characters_to_stories.characters_id = characters.id
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
				$character = new Character(array
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
				
				
				$data[] = $character;
				
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
        $sql = 'SELECT MAX(priority) AS max_priority FROM characters_to_stories WHERE stories_id=:stories_id';
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
            'UPDATE characters_to_stories SET
				priority = :priority
			WHERE
				stories_id = :stories_id AND
				characters_id = :characters_id
			';

        $character = $this->loadById(@$data[$moved_element]);
        $values = array(
            'characters_id'=>$character->get_id(),
            'stories_id'=>$currentStory->get_id()
        );

        return parent::updatePriorities($data, $moved_element, $sql, $values);
    }

    //write and pass the appropriate queries to Tobjects::cleanPriorities
    public function cleanPriorities($characters_id){
        $select_sql = '
            SELECT
                @row_number:=@row_number+1 AS row_number,
                characters_id,
                stories_id
            FROM
                characters_to_stories
            WHERE
                characters_id = :characters_id
            ORDER BY
                priority';

        $select_values[':characters_id'] = $characters_id;

        $update_sql = '
         UPDATE
                characters_to_stories
            SET
                priority=:priority
            WHERE
                characters_id = :characters_id AND
                stories_id = :stories_id
        ';

        $update_keys = array('characters_id', 'stories_id');

        parent::cleanPriorities($characters_id, $select_sql, $select_values, $update_sql, $update_keys);
    }
}

class Character extends Tobject
{
	protected $id;
	protected $series_id;
	protected $users_id;
	protected $story;
	protected $stories; // used in existing character search only
	protected $full_name, $aliases;
	protected $model;
	protected $priority;
	protected $content;
	
	function __construct($properties=NULL)
	{
		$table_name = 'characters';
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
			'INSERT INTO characters
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
			'UPDATE characters SET
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
			'INSERT INTO characters_content 
			(
				content,
				characters_id
			)
			VALUES
			(
				:content,
				:characters_id
			)
			ON DUPLICATE KEY UPDATE `content`=VALUES(content)';
		$values[':characters_id'] = $this->get_id();
		$values[':content'] = $this->get_content();
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}

    public function addToStory()
    {
        $characterManager = new Characters();
        $max_priority = $characterManager->getMaxPriority($this->get_story()->get_id());

        // We need the max priority from the database to add a new record
        // If there are no records, default to priority increment value
        if( $max_priority === false ) return false;
        if( $max_priority === NULL ) $max_priority = 0;

        //new record, step up
        $max_priority += 1000;

        $sql =
            'INSERT INTO characters_to_stories
            (
                stories_id,
                characters_id,
                priority
            )
            VALUES
            (
                :stories_id,
                :characters_id,
                :priority
            )
            ON DUPLICATE KEY UPDATE `priority`=VALUES(priority)';
        $values[':stories_id'] = $this->get_story()->get_id();
        $values[':characters_id'] = $this->get_id();
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
            'DELETE FROM characters_to_stories
            WHERE stories_id = :stories_id AND characters_id = :characters_id';
        $values[':stories_id'] = $this->get_story()->get_id();
        $values[':characters_id'] = $this->get_id();

        if(pdologged_preparedQuery($sql, $values) !== false)
            return true;
        return false;
    }

    public function delete()
    {
        global $currentUser;

        if( parent::delete() )
        {
            //Delete Relationship Chart Nodes and connections
            $node_controller = new RelationshipChartNodesController(NULL, $currentUser, 'characters', true, false);
            $node_loader = new RelationshipChartNodes();

            $parameters = array();
            $parameters['characters_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$this->get_id());
            $nodes = $node_loader->loadByParameters($parameters);

            if( is_array($nodes) && count($nodes) > 0 )
            {
                $connections = array();
                $conn_controller = new RelationshipChartConnectionsController(NULL, $currentUser, 'characters', true, false);
                $conn_loader = new RelationshipChartConnections();

                foreach($nodes as $node)
                {
                    $node_connections = $conn_loader->loadByNodesId($node->get_id());

                    $node_controller->setCheckXSRF(false);
                    $node_controller->setTobject($node);

                    if( $node_controller->delete() )
                    {
                        $connections = array_merge($connections, $node_connections);
                        AlertSet::addSuccess('Node deleted');
                    }
                }

                if( is_array($connections) && count($connections) > 0 )
                {
                    $conn_controller->setTobjects($connections);
                    $conn_controller->setCheckXSRF(false);
                    $conn_controller->bulkDelete();
                }
            }

            $sql = 'DELETE FROM characters_to_stories WHERE characters_id=:characters_id';
            $values[':characters_id'] = $this->get_id();
            pdologged_preparedQuery($sql, $values);
        }
        return true;
    }
}
?>
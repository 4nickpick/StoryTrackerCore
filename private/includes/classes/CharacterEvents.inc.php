<?php
class CharacterEvents extends Tobjects
{
    function __construct()
    {
        $this->search_keys = array('characters_events.description');
        $this->table_name = 'characters_events';
    }

    public function loadByCurrentUser($user_id)
    {
        $where = 'series.users_id = :users_id';
        $values[':users_id'] = $user_id;

        return $this->load($where, $values, true);
    }

    public function loadByCharacter($characters_id)
    {
        $where = 'characters_events.characters_id = :characters_id';
        $values[':characters_id'] = $characters_id;

        return $this->load($where, $values, true);
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='characters_events.priority', $limit='')
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
				characters_events.id,
				characters_events.characters_id,
				characters_events.description,
				characters_events.priority,
				characters_events.time,

				characters.series_id AS series_id,
				series.name AS series_name,
				series.users_id AS users_id,
				series.is_series AS is_series

			FROM
				characters_events
			LEFT JOIN characters ON characters.id = characters_events.characters_id
			LEFT JOIN series ON series.id = characters.series_id
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
                $character_event = new CharacterEvent(array
                (
                    'id'=>$row['id'],
                    'series'=>array(
                        'id'=>$row['series_id'],
                        'name'=>$row['series_name'],
                        'is_series'=>$row['is_series'],
                        'users_id'=>$row['users_id'],
                    ),
                    'description'=>$row['description'],
                    'time'=>$row['time'],
                    'priority'=>$row['priority'],
                    'characters_id'=>$row['characters_id']
                ));

                $data[] = $character_event;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }

    public function updatePriorities($data, $moved_element)
    {
        $sql =
            'UPDATE characters_events SET
				priority = :priority
			WHERE
				characters_id = :characters_id
				AND characters_events.id = :characters_events_id
			';

        $character_event = $this->loadById(@$data[$moved_element]);
        $values = array(
            'characters_id'=>$character_event->get_characters_id(),
            'characters_events_id'=>$character_event->get_id()
        );

        return parent::updatePriorities($data, $moved_element, $sql, $values);
    }

    //write and pass the appropriate queries to Tobjects::cleanPriorities
    public function cleanPriorities($characters_id){
        $select_sql = '
            SELECT
                @row_number:=@row_number+1 AS row_number,
                characters_events.id AS characters_events_id
            FROM
                characters_events
            LEFT JOIN characters ON characters.id = characters_events.characters_id
			WHERE
				series.users_id = :users_id
			ORDER BY
				priority';

        $select_values[':characters_id'] = $characters_id;

        $update_sql = '
			UPDATE
				characters_events
			SET
				priority=:priority
			WHERE
				characters_events.id = :characters_events_id';

        $update_keys = array('characters_events_id');

        parent::cleanPriorities($characters_id, $select_sql, $select_values, $update_sql, $update_keys);
    }


    public function getMaxPriority($characters_id)
    {
        $sql = '
            SELECT MAX(priority) AS max_priority FROM characters_events
                LEFT JOIN characters ON characters.id = characters_events.characters_id
                WHERE characters_events.characters_id=:characters_id';
        $values[':characters_id']=$characters_id;

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

class CharacterEvent extends Tobject
{
    protected $id;
    protected $characters_id;
    protected $time;
    protected $series;
    protected $description;

    function __construct($properties=NULL)
    {
        $table_name = 'characters_events';
        parent::__construct($table_name,$properties);
        if( isset($properties['series']))
            $this->series = new Series($properties['series']);
    }

    public function get_id()
    {
        return intval($this->id);
    }

    public function get_users_id()
    {
        return intval($this->series->get_users_id());
    }

    public function get_series()
    {
        return ($this->series);
    }

    public function get_characters_id()
    {
        return ($this->characters_id);
    }

    public function get_time()
    {
        return ($this->time);
    }

    public function get_description()
    {
        return ($this->description);
    }

    public function set_time($time)
    {
        $this->time = $time;
    }

    public function set_description($description)
    {
        $this->description = $description;
    }

    public function isPartOfSeries()
    {
        return $this->series->is_series();

        /*
        if( $this->series->get_name() != $this->get_name() )
            return true;

        $loader = (new Stories());
        $series_count = $loader->getCountBySeries($this->get_series()->get_id());

        return  intval($series_count) > 1;*/
    }

    public function add()
    {
        if( $this->get_description() == '' )
        {
            AlertSet::addError('Event name cannot be blank.');
            return false;
        }

        $loader = new CharacterEvents();
        $max_priority = $loader->getMaxPriority($this->get_characters_id());

        // We need the max priority from the database to add a new record
        // If there are no records, default to priority increment value
        if( $max_priority === false ) return false;
        if( $max_priority === NULL ) $max_priority = 0;

        //new record, step up
        $max_priority += 1000;

        $sql =
            'INSERT INTO characters_events
            (
                characters_id,
                description,
                `time`,
                priority
            )
            VALUES
            (
                :characters_id,
                :description,
                :time,
                :priority
            )
            ';

        $values = array(
            ':characters_id'=>$this->get_characters_id(),
            ':description'=>$this->get_description(),
            ':time'=>$this->get_time(),
            ':priority'=>$max_priority
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            $this->id=Tabmin::$db->lastInsertId();

            return true;

        }

        return false;
    }

    public function update()
    {
        if( $this->get_description() == '' )
        {
            AlertSet::addError('Event name cannot be blank.');
            return false;
        }

        $sql_simpleFields = $this->generateSimpleUpdateFields(array(''));

        /*$series_id = 'series_id=NULL';
        if( isset($this->series) )
        {
            $series_id = 'series_id=:series_id';
            $values['series_id'] = $this->series->get_id();
        }*/

        $sql =
            'UPDATE characters_events SET
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
}
?>
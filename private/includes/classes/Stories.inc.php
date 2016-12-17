<?php
class Stories extends Tobjects
{
    function __construct()
    {
        $this->search_keys = array('stories.name');
        $this->table_name = 'stories';
    }

    public function getCountByUser($users_id)
    {
        $sql = '
            SELECT stories.id FROM stories LEFT JOIN series ON stories.series_id = series.id WHERE series.users_id= :users_id
        ';

        $values = array('users_id'=>$users_id);
        if($rs = pdologged_preparedQuery($sql, $values))
        {
           return $rs->rowCount();
        }

        return -1;
    }

    public function getCountBySeries($series_id)
    {
        $where = 'series.id = :series_id';
        $values[':series_id'] = $series_id;

        $stories = $this->load($where, $values, true);
        if( count($stories) > 0 )
            return count($stories);
        else
            return 0;
    }

    public function loadByCurrentUser($user_id)
    {
        $where = 'series.users_id = :users_id';
        $values[':users_id'] = $user_id;

        return $this->load($where, $values, true);
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='stories.priority', $limit='')
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
				stories.id,
				stories.name,
				stories.description,
				stories.synopsis,
				stories.priority,
				UNIX_TIMESTAMP(stories.created) AS created,

				stories.series_id AS series_id,
				series.name AS series_name,
				series.users_id AS users_id,
				series.is_series AS is_series

			FROM
				stories
			LEFT JOIN series ON series.id = stories.series_id
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
                $story = new Story(array
                (
                    'id'=>$row['id'],
                    'series'=>array(
                        'id'=>$row['series_id'],
                        'name'=>$row['series_name'],
                        'is_series'=>$row['is_series'],
                        'users_id'=>$row['users_id'],
                    ),
                    'name'=>$row['name'],
                    'description'=>$row['description'],
                    'synopsis'=>$row['synopsis'],
                    'priority'=>$row['priority'],
                    'created'=>$row['created']
                ));

                $data[] = $story;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }

    //write and pass the appropriate queries to Tobjects::cleanPriorities
    public function cleanPriorities($users_id){
        $select_sql = '
            SELECT
                @row_number:=@row_number+1 AS row_number,
                stories.id AS stories_id
            FROM
                stories
            LEFT JOIN series ON stories.series_id = series.id
			WHERE
				series.users_id = :users_id
			ORDER BY
				priority';

        $select_values[':users_id'] = $users_id;

        $update_sql = '
			UPDATE
				stories
			SET
				priority=:priority
			WHERE
				stories.id = :stories_id';

        $update_keys = array('stories_id');

        parent::cleanPriorities($users_id, $select_sql, $select_values, $update_sql, $update_keys);
    }

    public function getMaxPriority($users_id)
    {
        $sql = 'SELECT MAX(priority) AS max_priority FROM stories LEFT JOIN series ON series.id = stories.series_id
            WHERE series.users_id=:users_id';
        $values[':users_id']=$users_id;

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

class Story extends Tobject
{
    protected $id;
    protected $name;
    protected $series;
    protected $description;
    protected $synopsis;
    protected $created;

    function __construct($properties=NULL)
    {
        $table_name = 'stories';
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

    public function get_name()
    {
        return ($this->name);
    }

    public function get_description()
    {
        return ($this->description);
    }

    public function get_synopsis()
    {
        return ($this->synopsis);
    }

    public function get_created()
    {
        return date('F jS, Y', $this->created);
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
        if( $this->series == NULL || $this->series->get_id() <= 0 )
        {
            /* create a new series */
            $users_id = $this->series != NULL && $this->series->get_users_id() != NULL ? $this->series->get_users_id() : NULL;
            if( $users_id == NULL )
            {
                AlertSet::addError('Users ID cannot be blank.');
                return false;
            }

            $series_name = $this->series != NULL && $this->series->get_name() != NULL ? $this->series->get_name() : $this->get_name();
            $is_series = $this->series != NULL && $this->series->is_series();
            $this->series = new Series(
                array(
                    'name'=>$series_name,
                    'users_id'=>$users_id,
                    'is_series'=>$is_series
                )
            );

            if( !$this->series->add() )
            {
                AlertSet::addError('Failed to create new Series for original story.');
                return false;
            }
        }

        $loader = new Stories();
        $max_priority = $loader->getMaxPriority($this->get_users_id());

        // We need the max priority from the database to add a new record
        // If there are no records, default to priority increment value
        if( $max_priority === false ) return false;
        if( $max_priority === NULL ) $max_priority = 0;

        //new record, step up
        $max_priority += 1000;

        $sql =
            'INSERT INTO stories
            (
                series_id,
                name,
                description,
                priority
            )
            VALUES
            (
                :series_id,
                :name,
                :description,
                :priority
            )
            ';

        $values = array(
            ':series_id'=>$this->series->get_id(),
            ':name'=>$this->name,
            ':description'=>$this->description,
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
        $sql_simpleFields = $this->generateSimpleUpdateFields(array('series'));

        $sql =
            'UPDATE stories SET
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

    public function delete()
    {
        if( parent::delete() )
        {
            $sql = 'DELETE FROM characters_to_stories WHERE stories_id=:stories_id';
            $values[':stories_id'] = $this->get_id();
            pdologged_preparedQuery($sql, $values);

            $sql = 'DELETE FROM settings_to_stories WHERE stories_id=:stories_id';
            $values[':stories_id'] = $this->get_id();
            pdologged_preparedQuery($sql, $values);

            $sql = 'DELETE FROM plot_events_to_stories WHERE stories_id=:stories_id';
            $values[':stories_id'] = $this->get_id();
            pdologged_preparedQuery($sql, $values);

            return true;
        }
        return false;
    }

}
?>
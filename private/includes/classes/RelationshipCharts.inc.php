<?php
class RelationshipCharts extends Tobjects
{
    function __construct()
    {
        $this->search_keys = array('relationship_charts.name', 'relationship_charts.priority');
        $this->table_name = 'relationship_charts';
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='relationship_charts.priority', $limit='')
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
				relationship_charts.id,
				relationship_charts.name,
				priority,
				series_id,

				series.users_id AS users_id

			FROM
				relationship_charts
			LEFT JOIN series ON series.id = relationship_charts.series_id
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
                $relationship_chart = new RelationshipChart(array
                (
                    'id'=>$row['id'],
                    'users_id'=>$row['users_id'],
                    'series_id'=>$row['series_id'],
                    'name'=>$row['name'],
                    'priority'=>$row['priority']
                ));


                $data[] = $relationship_chart;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }

    public function getMaxPriority($series_id)
    {
        $sql = '
            SELECT MAX(priority) AS max_priority FROM relationship_charts
                LEFT JOIN series ON series.id = relationship_charts.series_id
                WHERE relationship_charts.series_id=:series_id';
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

class RelationshipChart extends Tobject
{
    protected $id;
    protected $name;
    protected $users_id;
    protected $series_id;
    protected $priority;

    function __construct($properties=NULL)
    {
        $table_name = 'relationship_charts';
        parent::__construct($table_name,$properties);
    }

    public function get_id()
    {
        return intval($this->id);
    }

    public function get_users_id()
    {
        return intval($this->users_id);
    }

    public function get_series_id()
    {
        return intval($this->series_id);
    }

    public function get_name()
    {
        return ($this->name);
    }

    public function set_name($new_name)
    {
       $this->name = $new_name;
    }

    /* Setters */

    public function add()
    {
        $chartsManager = new RelationshipCharts();
        $max_priority = $chartsManager->getMaxPriority($this->series_id);

        // We need the max priority from the database to add a new record
        // If there are no records, default to priority increment value
        if( $max_priority === false ) return false;
        if( $max_priority === NULL ) $max_priority = 0;

        //new record, step up
        $max_priority += 1000;

        $sql =
            'INSERT INTO relationship_charts
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

        if( !$this->name )
            $this->name = 'New Chart';

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

    public function update()
    {
        $sql_simpleFields = $this->generateSimpleUpdateFields(array('users_id'));

        $sql =
            'UPDATE relationship_charts SET
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
        global $currentUser, $module;

        $conn_controller = new RelationshipChartConnectionsController(NULL, $currentUser, $module);
        $conn_loader = new RelationshipChartConnections();
        $connections = $conn_loader->loadByParameters(array('charts_id'=>array('type'=>'int', 'condition'=>'=', 'value'=>$this->id)));
        $conn_controller->setTobjects($connections);
        $conn_controller->setCheckXSRF(false);
        if( $conn_controller->bulkDelete() )
            AlertSet::clear();

        $node_controller = new RelationshipChartNodesController(NULL, $currentUser, $module);
        $node_loader = new RelationshipChartNodes();
        $nodes = $node_loader->loadByParameters(array('relationship_charts_id'=>array('type'=>'int', 'condition'=>'=', 'value'=>$this->id)));
        $node_controller->setTobjects($nodes);
        $node_controller->setCheckXSRF(false);
        if( $node_controller->bulkDelete() )
            AlertSet::clear();

        if( AlertSet::$success && parent::delete() )
        {
            return true;
        }

        return false;

    }
}
?>
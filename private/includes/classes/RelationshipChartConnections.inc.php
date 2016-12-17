<?php
class RelationshipChartConnections extends Tobjects
{
    function __construct()
    {
        $this->search_keys = array('relationship_chart_connections.nodes1_id', 'relationship_chart_connections.nodes2_id');
        $this->table_name = 'relationship_chart_connections';
    }

    public function loadByNodesId($nodes_id)
    {
        $where = 'nodes1_id=:nodes1_id OR nodes2_id=:nodes2_id';
        $values = array(
            'nodes1_id'=>$nodes_id,
            'nodes2_id'=>$nodes_id
        );
        return $this->load($where, $values, true);
    }

    public function loadByCharacterAndType($characters_id, $type, $require_content=false, $order_by='')
    {
        $where = '
            (
                characters1.id=:characters1_id
                OR characters2.id=:characters2_id
            )
            AND relationship_chart_connections.type=:chart_type';

        if( $require_content )
            $where .= ' AND content IS NOT NULL';

        $values = array(
            'characters1_id'=>$characters_id,
            'characters2_id'=>$characters_id,
            'chart_type'=>$type
        );
        return $this->load($where, $values, true, $order_by);
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='relationship_chart_connections.id', $limit='')
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
				relationship_chart_connections.id,
				relationship_chart_connections.charts_id,
				relationship_chart_connections.nodes1_id AS nodes1_id,
				characters1.id AS nodes1_characters_id,
				characters1.full_name AS nodes1_full_name,
				relationship_chart_connections.nodes2_id AS nodes2_id,
				characters2.id AS nodes2_characters_id,
				characters2.full_name AS nodes2_full_name,
				type,
				content

			FROM
				relationship_chart_connections
			LEFT JOIN relationship_chart_nodes AS nodes1 ON nodes1.id = nodes1_id
			LEFT JOIN characters AS characters1 ON characters1.id = nodes1.characters_id
			LEFT JOIN relationship_chart_nodes AS nodes2 ON nodes2.id = nodes2_id
			LEFT JOIN characters AS characters2 ON characters2.id = nodes2.characters_id
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
                $connection = new RelationshipChartConnection(array
                (
                    'id'=>$row['id'],
                    'chart'=>array('id'=>$row['charts_id']),
                    'node1'=>array(
                        'id'=>$row['nodes1_id'],
                        'characters_name'=>$row['nodes1_full_name'],
                        'characters_id'=>$row['nodes1_characters_id']
                    ),
                    'node2'=>array(
                        'id'=>$row['nodes2_id'],
                        'characters_name'=>$row['nodes2_full_name'],
                        'characters_id'=>$row['nodes2_characters_id']
                    ),
                    'type'=>$row['type'],
                    'content'=>$row['content']
                ));

                $data[] = $connection;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }
}

class RelationshipChartConnection extends Tobject
{
    protected $id;
    protected $chart;
    protected $node1, $node2;
    protected $type;
    protected $content;

    function __construct($properties=NULL)
    {
        $table_name = 'relationship_chart_connections';
        parent::__construct($table_name,$properties);
        if( isset($properties['chart']))
        {
            $this->chart = new RelationshipChart($properties['chart']);
        }
        if( isset($properties['node1']))
        {
            $this->node1 = new RelationshipChartNode($properties['node1']);
        }
        if( isset($properties['node2']))
        {
            $this->node2 = new RelationshipChartNode($properties['node2']);
        }
    }

    public function get_id()
    {
        return intval($this->id);
    }

    public function get_chart()
    {
        return ($this->chart);
    }

    public function get_node1()
    {
        return ($this->node1);
    }

    public function get_node2()
    {
        return ($this->node2);
    }

    public function get_type()
    {
        return ($this->type);
    }

    public function get_content()
    {
        return ($this->content);
    }

    /* Setters */

    public function add()
    {
        $sql =
            'INSERT INTO relationship_chart_connections
            (
                charts_id,
                nodes1_id,
                nodes2_id,
                type,
                content
            )
            VALUES
            (
                :charts_id,
                :nodes1_id,
                :nodes2_id,
                :type,
                :content
            )
            ';

        $values = array(
            ':charts_id'=>$this->get_chart()->get_id(),
            ':nodes1_id'=>$this->get_node1()->get_id(),
            ':nodes2_id'=>$this->get_node2()->get_id(),
            ':type'=>$this->type,
            ':content'=>$this->content
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
        $sql_simpleFields = $this->generateSimpleUpdateFields(array(''));

        $sql =
            'UPDATE relationship_chart_connections SET
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
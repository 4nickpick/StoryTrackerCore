<?php
class RelationshipChartNodes extends Tobjects
{
    function __construct()
    {
        $this->search_keys = array('relationship_chart_nodes.id');
        $this->table_name = 'relationship_chart_nodes';
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='relationship_chart_nodes.id', $limit='')
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
				relationship_chart_nodes.id,
				relationship_charts_id,
				characters_id,
				characters.full_name AS characters_name,
				top,
				`left`

			FROM
				relationship_chart_nodes
			LEFT JOIN characters ON relationship_chart_nodes.characters_id = characters.id
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
                $relationship_chart_node = new RelationshipChartNode(array
                (
                    'id'=>$row['id'],
                    'relationship_charts_id'=>$row['relationship_charts_id'],
                    'characters_id'=>$row['characters_id'],
                    'characters_name'=>$row['characters_name'],
                    'top'=>$row['top'],
                    'left'=>$row['left']
                ));


                $data[] = $relationship_chart_node;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }
}

class RelationshipChartNode extends Tobject
{
    protected $id;
    protected $relationship_charts_id;
    protected $characters_id;
    protected $characters_name;
    protected $top, $left;

    function __construct($properties=NULL)
    {
        $table_name = 'relationship_chart_nodes';
        parent::__construct($table_name,$properties);
    }

    public function get_id()
    {
        return intval($this->id);
    }

    public function get_characters_id()
    {
        return intval($this->characters_id);
    }

    public function get_characters_name()
    {
        return ($this->characters_name);
    }

    public function get_top()
    {
        return ($this->top);
    }

    public function get_left()
    {
        return ($this->left);
    }


    /* Setters */

    public function add()
    {
       $sql =
            'INSERT INTO relationship_chart_nodes
            (
                relationship_charts_id,
                characters_id,
                top,
                `left`
            )
            VALUES
            (
                :relationship_charts_id,
                :characters_id,
                :top,
                :left
            )
            ';

        $values = array(
            ':relationship_charts_id'=>$this->relationship_charts_id,
            ':characters_id'=>$this->characters_id,
            ':top'=>$this->top,
            ':left'=>$this->left
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
            'UPDATE relationship_chart_nodes SET
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
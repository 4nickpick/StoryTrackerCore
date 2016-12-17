<?php
class SeriesLoader extends Tobjects
{
    function __construct()
    {
        $this->search_keys = array('series.name');
        $this->table_name = 'series';
    }

    public function loadByCurrentUser($user_id)
    {
        $where = 'series.users_id = :users_id AND series.is_series = TRUE';
        $values[':users_id'] = $user_id;

        return $this->load($where, $values, true);
    }

    protected function load($where='', $values=NULL, $return_array=false, $order_by='series.name', $limit='')
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
				series.id,
				name,
				series.users_id AS users_id,
				series.is_series

			FROM
				series
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
                $series = new Series(array
                (
                    'id'=>$row['id'],
                    'users_id'=>$row['users_id'],
                    'name'=>$row['name'],
                    'is_series'=>$row['is_series'],
                ));

                $data[] = $series;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }

}

class Series extends Tobject
{
    protected $id;
    protected $name;
    protected $users_id;
    protected $is_series;

    function __construct($properties=NULL)
    {
        $table_name = 'series';
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

    public function get_name()
    {
        return ($this->name);
    }

    public function is_series()
    {
        return ($this->is_series);
    }

    public function add()
    {
        $sql =
            'INSERT INTO series
            (
                users_id,
                name,
                is_series
            )
            VALUES
            (
                :users_id,
                :name,
                :is_series
            )
            ';

        $values = array(
            ':users_id'=>$this->users_id,
            ':name'=>$this->name,
            ':is_series'=>$this->is_series
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            $this->id=Tabmin::$db->lastInsertId();
            $character_model_created = Models::generateDefaultCharacterModel($this->id);
            $setting_model_created = Models::generateDefaultSettingModel($this->id);

            return $character_model_created && $setting_model_created;
        }

        return false;
    }

    public function update()
    {
        $sql_simpleFields = $this->generateSimpleUpdateFields(array(''));

        $sql =
            'UPDATE series SET
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
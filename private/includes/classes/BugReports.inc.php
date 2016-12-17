<?php
class BugReports extends Tobjects
{
    function __construct()
    {
        $this->search_keys = NULL;
        $this->table_name = 'bug_reports';
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
				users_id,
				current_page,
				browser,
				problem

			FROM
				bug_reports
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
                $bug = new BugReport(array
                (
                    'id'=>$row['id'],
                    'user'=>array(
                        'id'=>$row['users_id'],
                        'name'=>$row['users_name'],
                        'email'=>$row['users_email']
                    ),
                    'current_page'=>$row['current_page'],
                    'browser'=>$row['browser'],
                    'problem'=>$row['problem']
                ));

                $data[] = $bug;

                if(!$return_array)
                    return $data[0];
            }

            return $data;
        }
        return false;
    }
}

class BugReport extends Tobject
{
    protected $id;
    protected $user;
    protected $problem;
    protected $current_page;
    protected $browser;

    function __construct($properties=NULL)
    {
        $table_name = 'bug_reports';
        parent::__construct($table_name,$properties);
        if( isset($properties['user']))
            $this->user = new User($properties['user']);
    }

    public function get_id()
    {
        return intval($this->id);
    }

    public function get_users_id()
    {
        return intval($this->series->get_users_id());
    }

    public function add()
    {
        $sql =
            'INSERT INTO bug_reports
            (
                users_id,
                browser,
                current_page,
                problem
            )
            VALUES
            (
                :users_id,
                :browser,
                :current_page,
                :problem
            )
            ';

        $values = array(
            ':users_id'=>$this->user->get_id(),
            ':browser'=>$this->browser,
            ':current_page'=>$this->current_page,
            ':problem'=>$this->problem
        );
        if(pdologged_preparedQuery($sql, $values))
        {
            $this->id=Tabmin::$db->lastInsertId();

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: '.WEBSITE_EMAIL."\r\n";

            $html = 'Bug Report ID: ' . $this->id;
            mail(ERROR_REPORTING_EMAIL, 'StoryTracker - Bug Report', $html, $headers);

            return true;

        }

        return false;
    }

    function update()
    {
        return false;
    }

    function delete()
    {
        return false;
    }

}
?>
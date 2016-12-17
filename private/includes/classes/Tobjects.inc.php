<?php
abstract class Tobjects
{
	protected $found_rows=0;
	protected $search_keys;
	protected $table_name = '';
	
	public function loadById($id)
	{	
		if (!$this->table_name || $this->table_name == '')
			trigger_error('Table name is not defined',  E_USER_ERROR);
		$where=$this->table_name.'.id= :id';
		$values[':id'] = intval($id);
		return $this->load($where, $values, false);
	}
	
	public function loadByIds($ids, $order_by='', $limit='')
	{
		if (!$this->table_name || $this->table_name == '')
			trigger_error('Table name is not defined',  E_USER_ERROR);
		for($i=0; $i<count($ids); $i++)
			$ids[$i]=intval($ids[$i]);
			
		$where=$this->table_name.'.id IN (';
		foreach ($ids as $i=>$id)
		{
			$where.=':id'.$i . ((count($ids) - 1) > $i ? ',' : '');
			$values[':id'.$i] = $id;
		}
		$where .= ')';	
		
		
		return $this->load($where, $values, true, $order_by, $limit);
	}
	
	
	
	/***
	 * This function loads an object based on the parameters passed
	 * It requires parameters be defined in terms of column name, condition and value
	***/
	public function loadByParameters($parameters, $order_by='', $limit='')
	{
		$whereStr = '';
		$pwhere = $this->getParametersWhere($parameters);
		if ($pwhere)
		{
			$where = $pwhere[0];
			$values = $pwhere[1];
			$whereStr = implode(' AND ', $where);	
		}		
		
		return $this->load($whereStr, $values, true, $order_by, $limit);
		
	}
		
	protected function getParametersWhere($parameters)
	{
		$where = array();
		$values = array();
		$ret = array();		
		if ($parameters)
		{
			foreach($parameters as $parameter=>$value)
			{
				$pname = str_replace('.','',$parameter);
				if ($value['condition'] != 'Between')
				{
					$where[] = $parameter.$value['condition'].' :'.$pname;
					//$values[':'.$pname] = $value['value'];
					if ($value['type'] == 'int')
						$values[':'.$pname] = intval($value['value']);
					else
						$values[':'.$pname] = $value['value'];
					
					/*if ($value['type'] == 'string')
						$where[] = $parameter.$value['condition'].'"'.addslashes($value['value']).'"';
					else if($value['type'] == 'int')
						$where[] = $parameter.$value['condition'].intval($value['value']);
					else if($value['type'] == 'double')
						$where[] = $parameter.$value['condition'].doubleval($value['value']);*/
				}
				else
				{
					switch(strtoupper($value['condition']))
					{
						case 'BETWEEN':
							$where[] = $parameter.' '.$value['condition'].' :'.$pname.'1 AND :'.$pname.'2';	
							$values[':'.$pname.'1'] = $value['value1'];	
							$values[':'.$pname.'2'] = $value['value2'];				
						break;					
					}
				}
			}
			$ret[] = $where;
			$ret[] = $values;
		}
		else
			$ret = false;
		return $ret;		
	}
	
	/***
	 * This function performs search on the specified columns and loads Users that match teh search result
	***/	
	public function searchLoad($search, $order_by='', $limit='')
	{		
		$where = '';
		$pwhere = $this->getSearchLoadWhere($search);
		if ($pwhere)
		{
			$where = $pwhere[0];
			$values = $pwhere[1];			
		}
		return $this->load($where, $values, true, $order_by, $limit);
	}
	
	protected function getSearchLoadWhere($search)
	{
		$where = '';
		$values = array();
		$ret = array();
		if(!empty($search))
		{
			$search_sql=array();
			$search_terms=preg_split('/\s+/', trim($search));
			$counter = 0;
			for($i=0; $i<count($search_terms); $i++)
			{
				foreach ($this->search_keys as $search_key)
				{				
					$search_sql[]=$search_key.' LIKE :search'.$counter;
					$values[':search'.$counter] = '%'. $search_terms[$i].'%';
					$counter++;
				}			
			}
			$where='('. implode(' OR ', $search_sql) .')';
			$ret[] = $where;
			$ret[] = $values;
		}
		else
			$ret = false;
		
		return $ret;
	}
	
	public function searchLoadByParameters($search, $parameters= NULL, $order_by='', $limit='')
	{	
		$svalues = array();
		$pvalues = array();
		$pwhereStub = array();
		$swhereStub = false;
		if ($parameters)
		{
			$pwhere = self::getParametersWhere($parameters);
			
			if ($pwhere)
			{
				$pwhereStub = $pwhere[0];
				$pvalues = $pwhere[1];
			}
		}
		
		$swhere = $this->getSearchLoadWhere($search);
		if ($swhere)
		{
			$swhereStub = $swhere[0];
			$svalues = $swhere[1];			
		}
		$values = array_merge($pvalues, $svalues);	
		
		$where = '';
		if( count($pwhereStub) > 0 )
			$where = implode(' AND ', $pwhereStub);	
				
		if ($swhereStub)
		{
			if( strlen($where) > 0 )
				$where .= ' AND ';
			$where .= $swhereStub;
		}	
		
		return $this->load($where, $values, true, $order_by, $limit);
	}
	
	public function loadAll($order_by='', $limit='')
	{
		return $this->load('',NULL, true, $order_by, $limit);
	}

	protected abstract function load($where='', $values=NULL, $return_array=false, $order_by='', $limit='');
	
	/* These functions are required for sorting Tobjects */
	
	public function updatePriorities($data, $moved_element, $sql='', $values='')
	{
		if( !$sql )
        {
            $sql =
                'UPDATE ' . $this->table_name . ' SET
                    priority = :priority
                WHERE
                    id = :id
			    ';
        }

		$current_tobject = $this->loadById(@$data[$moved_element]);
		$next_tobject = $this->loadById(@$data[$moved_element-1]);
		$last_tobject = $this->loadById(@$data[$moved_element+1]);

		$next_priority = $next_tobject ? $next_tobject->get_priority() : 0;
		$last_priority = $last_tobject ? $last_tobject->get_priority() : NULL;
		
		if( $last_priority == NULL )
			$new_priority = $next_tobject->get_priority() + 1000;
		else
			$new_priority = intval(($next_priority + $last_priority) / 2);
		
		if( $new_priority == $next_priority || $new_priority == $last_priority || $new_priority < 2 )
		{
			//trigger priority cleaning, then reattempt the update
			$this->cleanPriorities($current_tobject->get_users_id());
			return $this->updatePriorities($data, $moved_element, $sql, $values);
		}

        if( $values == '' )
        {
            $values[':id'] = $current_tobject->get_id();
        }
		$values[':priority'] = $new_priority;
				
		if($rs = pdologged_preparedQuery($sql, $values) )
		{
			return true;
		}
		
		return false;
	}

    /* reset priority values - cleans up values to prevent collisions */
	public function cleanPriorities($users_id, $select_sql='', $select_values='', $update_sql='', $update_keys=array())
	{
        if( !$select_sql )
        {
            $select_sql =
                '
                SELECT
                    @row_number:=@row_number+1 AS row_number,
                    id
                FROM
                    ' . $this->table_name . '
                WHERE
                    users_id = :users_id
                ORDER BY
                    priority';
        }

        if( !$select_values )
            $select_values[':users_id'] = $users_id;

        if( !$update_sql ) {

            $update_sql = '
                    UPDATE
                        ' . $this->table_name . '
                    SET
                        priority=:priority
                    WHERE
                        id = :id';
        }

		$sql = 'SET @row_number = 0;';
		$rs_row_number = pdologged_query($sql);
		if(!$rs_row_number)
			return false;


        if($rs_select = pdologged_preparedQuery($select_sql, $select_values) )
		{
			while($row = $rs_select->fetch(PDO::FETCH_ASSOC))
			{
                $update_values = NULL;
                if( !$update_keys )
                {
                    $update_values[':id'] = $row['id'];
                }
                else
                {
                    foreach($update_keys as $update_key)
                    {
                        $update_values[':'.$update_key] = $row[$update_key];
                    }
                }
                $update_values[':priority'] = $row['row_number']*1000;
				$rs_update = pdologged_preparedQuery($update_sql, $update_values);

				if( !$rs_update )
				{
					return false;
				}
			}
			return false;
		}
		
		return false;
		
	}
	
	public function getMaxPriority($users_id)
	{
		$sql = 'SELECT MAX(priority) AS max_priority FROM ' . $this->table_name . ' WHERE users_id=:users_id';
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
	
	public function getFoundRows()
	{
		return $this->found_rows;
	}
}

abstract class Tobject
{
	private $table_name;
	protected $inclusion;
	protected $priority;
	
	function __construct($table_name, $properties=NULL)
	{	
		$this->inclusion = array();
		$this->table_name = $table_name;

        if( is_array($properties) )
		{
			foreach($properties as $property=>$value)
			{
				if(property_exists($this, $property))
				{
					$this->{"$property"}=$value;
					$this->inclusion[]=$property;
				}
			}
		}	
	}	
	
	function __get($name)
	{
		trigger_error('Accessing a private, protected or non-existent member "'.$name.'" of class "'.get_class($this).'". Default getter is used.',  E_USER_NOTICE);
		if (method_exists($this, 'get_'.$name))
		{
			return $this->{'get_'.$name}();			
		}
		else
			return $this->{"$name"};
	}
	
	function __set($name, $value)
	{
		trigger_error('Trying to assign a value to a private, protected or non-existent member '.$name.'" of class "'.get_class($this).'". Default setter is used.',  E_USER_NOTICE);
		if (method_exists($this, 'set_'.$name))
		{
			$this->{'set_'.$name}($value);			
		}
		else
			$this->{"$name"} = $value;
	}
	
	function __call($name, $args)
	{		
		if (strpos($name, 'get_') === 0)
		{
			$member = substr($name,4);
			trigger_error('Method '.$name.'" of class "'.get_class($this).'" does not exists or inaccessible from this context. Getting memeber value',  E_USER_NOTICE);
			return ($this->$member);
		}	
		else
			trigger_error('Method '.$name.'" of class "'.get_class($this).'" does not exists or inaccessible from this context.',  E_USER_NOTICE);
	}
	
	
		
	public abstract function add();
	public abstract function update();
	
	public function generateSimpleUpdateFields($exclude=array())
	{	
		$values = array();				
		$incCount = count($this->inclusion);
		$sql = '';		
		foreach($this->inclusion as $i=>$property)
		{	
			if ((!is_object($this->{$property})) && (!in_array($property,$exclude)))
			{				
				if ($incCount == $i+1)
				{
					$comma ='
					';
				}
				else
				{
					$comma =',
					';
				}				
				
				$sql .= $this->table_name.'.'.$property . '= :'.$this->table_name.$property.$comma;
				if(is_int($this->{$property}))
					$values[':'.$this->table_name.$property] = intval($this->{$property});
				else
					$values[':'.$this->table_name.$property] = $this->{$property};
			}
		}
		$sql = trim($sql);	//find a comma in case we still put it... quick and dirty fix	
		if (substr($sql,strlen($sql)-1,1) == ',')
			$sql = substr_replace($sql,'',strlen($sql)-1,1);
		return new stubContainer($sql, $values);
	}
	
	public function get_priority()
	{
		return intval($this->priority);
	}
	
	public function set_priority($priority)
	{
		$this->priority = intval($this->priority);
	}
	
	public function updatePriority()
	{		
		$sql =
			'UPDATE ' . $this->table_name . ' SET
				priority = :priority
			WHERE id=:id';
		
		$values[':id'] = $this->get_id();
		$values[':priority'] = $this->get_priority();
		//AlertSet::addError(var_export($sql, true));
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}
		
	public function delete()
	{
		$sql =
			'DELETE FROM
				'.$this->table_name.'
			WHERE
				id='.intval($this->id);
		if(pdologged_exec($sql) !== false)
		{
			return true;
		}
		return false;
	}
}

class stubContainer
{
	private $stub, $values;
	function __construct($stub, $values)
	{
		$this->stub = $stub;
		$this->values = $values;
	}
	
	public function getStub()
	{
		return $this->stub;
	}
	
	public function getValues()
	{
		return $this->values;
	}
}
?>
<?php
class Permissions extends Tobjects
{
	protected $permissions = array();
	
	function __construct()
	{
		$this->search_keys = array('permissions.module', 'permissions.tab' , 'permissions.verb', 'roles.role');
		$this->table_name = 'permissions';
	}
		
	protected function load($where='', $values=NULL, $return_array=false, $order_by='', $limit='')
	{
		if($where!='')
			$where='WHERE '.$where;
		
		if($order_by!='')
			$order_by='ORDER BY '.$order_by;
		
		$sql =
			'SELECT
				permissions.id,
				roles.role,
				roles.id AS roles_id,
				module,
				tab,
				verb,
				own
			FROM
				permissions
			INNER JOIN roles ON permissions.roles_id=roles.id
			'.$where.'
			'.$order_by;
		
		if($rs=pdologged_preparedQuery($sql, $values))
		{
			$data=array();
			
			while($row = $rs->fetch(PDO::FETCH_ASSOC))
			{		
			
				$data[] = new Permission(array
				(
					'id'=>$row['id'],
					'role'=>array
					(
						'id'=>$row['roles_id'],
						'role'=>$row['role']
					),
					'module'=>$row['module'],
					'tab'=>$row['tab'],
					'verb'=>$row['verb'],
					'own'=>$row['own']
				));
				
				if(!$return_array)
					return $data[0];
			}
			
			return $data;
		}
		
		return false;
	}
	
	function PermissionsByRole($roles_id, $dev = false)
	{
		if ($dev)
		{			
			$this->permissions['developer']['tabs']['switch-user'][0] = true;
			$this->permissions['developer']['verbs']['switch-user'][0] = true;	
			$this->permissions['developer']['tabs']['view'][0] = true;		
			$this->permissions['developer']['verbs']['view'][0] = true;			
			$this->permissions['developer']['tabs']['add'][0] = true;
			$this->permissions['developer']['tabs']['edit'][0] = true;
			$this->permissions['developer']['tabs']['bulk_add'][0] = true;
			$this->permissions['developer']['verbs']['add'][0] = true;
			$this->permissions['developer']['verbs']['edit'][0] = true;
			$this->permissions['developer']['verbs']['delete'][0] = true;
			$this->permissions['developer']['verbs']['bulk_add'][0] = true;
		}
		$sql =
			'SELECT
				module,
				tab,
				verb,
				own
			FROM
				permissions
			WHERE
				roles_id='.intval($roles_id);
		if($rs=pdologged_query($sql))
		{
			while($row=$rs->fetch(PDO::FETCH_ASSOC))
			{
				if(empty($this->permissions[$row['module']]) || !is_array($this->permissions[$row['module']]))
				{
					$this->permissions[$row['module']] = array();
					$this->permissions[$row['module']]['tabs'] = array();
					$this->permissions[$row['module']]['verbs'] = array();
				}
				
				if($row['tab']!=null)
					$this->permissions[$row['module']]['tabs'][$row['tab']] = true;
				
				if($row['verb']!=null)
				{
					if(empty($this->permissions[$row['module']]['verbs'][$row['verb']]) || !is_array($this->permissions[$row['module']]['verbs'][$row['verb']]))
						$this->permissions[$row['module']]['verbs'][$row['verb']] = array();
					
					if($row['own']===NULL)
					{
						$this->permissions[$row['module']]['verbs'][$row['verb']][0] = true;
						$this->permissions[$row['module']]['verbs'][$row['verb']][1] = true;
					}
					else
						$this->permissions[$row['module']]['verbs'][$row['verb']][$row['own']] = true;
				}
			}
		}
	}
	
	function hasPermission($module, $verb, $own=NULL)
	{
		if(is_object($module))
			$module=(string)$module;
		
		if($own===NULL)
			return (!empty($this->permissions[$module]['verbs'][$verb][1]) || !empty($this->permissions[$module]['verbs'][$verb][0]));
		
		if($own==true)
			$own=1;
		else
			$own=0;
		return !empty($this->permissions[$module]['verbs'][$verb][$own]);
	}
	
	function tabPermission($module, $tab)
	{
		if(is_object($module))
			$module=(string)$module;
		if(is_object($tab))
			$tab=(string)$tab;
		
		return !empty($this->permissions[$module]['tabs'][$tab]);
	}
}

class Permission extends Tobject
{
	protected $id, $role, $module, $verb, $tab, $own;
	
	function __construct($properties)
	{
		parent::__construct('permissions',$properties);		
		if(isset($properties['role']))
			$this->role=new Role($properties['role']);
	}
	
	public function get_id()
	{
		return intval($this->id);
	}
	
	public function get_role()
	{
		return ($this->role);
	}
	
	public function get_module()
	{
		return htmlentitiesUTF8($this->module);
	}
	
	public function get_verb()
	{
		return htmlentitiesUTF8($this->verb);
	}
	
	public function get_tab()
	{
		return htmlentitiesUTF8($this->tab);
	}
	
	public function get_own()
	{
		return intval($this->own);
	}
	
	
	public function add()
	{
	
		$values = array(
			':module'=>$this->module,
			':tab'=>$this->tab,
			':verb'=>$this->verb
		);

		if(!empty($this->role))
			$values[':rolesid'] = $this->role->get_id();
		else
			$values[':rolesid'] = 'DEFAULT';
			
		if(!empty($this->own))
			$values[':own']=intval($this->own);
		else
			$values[':own']='DEFAULT';
			
		$sql =
			'INSERT INTO permissions
			(
				roles_id,
				own,
				module,
				tab,
				verb
			)
			VALUES
			(
				:rolesid,
				:own,
				:module,
				:tab,
				:verb
			)';
			
		//AlertSet::addInfo($sql);
		if(pdologged_preparedQuery($sql, $values))
		{
			$this->id=Tabmin::$db->lastInsertId();
			return true;
		}
		
		return false;
	}
	
	public function update()
	{
		$sql_simpleFields = $this->generateSimpleUpdateFields();
		$values = $sql_simpleFields->getValues();
		
		
		$rolesStub='';		
		if(!empty($this->role))
		{			
			$rolesStub='roles_id=:rolesid,';
			$values[':rolesid'] = $this->role->get_id();
		}
			
		$sql =
			'UPDATE permissions SET
				'.$rolesStub.'
				'.$sql_simpleFields->getStub().'				
			WHERE id=:id';
		
		$values[':id'] = $this->get_id();
		
		if(pdologged_preparedQuery($sql, $values) !== false)
			return true;
		return false;
	}
	
}
?>
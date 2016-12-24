<?phpclass Users extends Tobjects{	function __construct()	{		$this->search_keys = array('users.first_name', 'users.last_name');		$this->table_name = 'users';	}	public function loadAllWithHidden($order_by='', $limit='')	{		//$parameters['roles.hidden'] = array('type'=>'int', 'condition'=>'=', 'value'=>0);		$parameters = array();		return parent::loadByParameters($parameters, $order_by, $limit);	}	public function loadAll($order_by='', $limit='')	{        $parameters = array();		return parent::loadByParameters($parameters, $order_by, $limit);	}	/***	 * This function loads an object based on the parameters passed	 * It requires parameters be defined in terms of column name, condition and value	***/	public function loadByParameters($parameters, $order_by='', $limit='')	{		return parent::loadByParameters($parameters, $order_by, $limit);	}	/***	 * This function performs search on the specified columns and loads Users that match teh search result	***/	public function searchLoad($search, $order_by='', $limit='')	{		$where = array();		$where[] = $this->getSearchLoadWhere($search);		return $this->load(implode(' AND ', $where), true, $order_by, $limit);	}	public function searchLoadByParameters($search, $parameters, $order_by='', $limit='')	{		return parent::searchLoadByParameters($search, $parameters, $order_by, $limit);	}	protected function load($where='', $values=NULL, $return_array=false, $order_by='', $limit='')	{		if($where!='')			$where='WHERE '.$where;		if($order_by!='')			$order_by='ORDER BY '.$order_by;		$sql_calc_found_rows='';		if($limit!='')		{			$limit='LIMIT '.$limit;			$sql_calc_found_rows='SQL_CALC_FOUND_ROWS';		}		$sql=			'SELECT DISTINCT '.$sql_calc_found_rows.'				users.id,				email,				first_name,				last_name,				nick_name,				phone,					address, 				city, 				state,				zip,				last_login,				users.roles_id AS roles_id,								pictures.id AS pictures_id							FROM				users			LEFT JOIN pictures ON users.pictures_id=pictures.id			'.$where.'			'.$order_by.'			'.$limit;		$this->found_rows=0;		$data=array();		if($rs = pdologged_preparedQuery($sql, $values))		{			if(!empty($limit))			{				$rs_count = pdologged_query('SELECT FOUND_ROWS()');				if($row = $rs_count->fetch(PDO::FETCH_NUM))					$this->found_rows=$row[0];			}			else				$this->found_rows = $rs->rowCount();			while($row = $rs->fetch(PDO::FETCH_ASSOC))			{				$user = new User(array				(					'id'=>$row['id'],					'email'=>$row['email'],					'address'=>$row['address'],					'city'=>$row['city'],					'state'=>$row['state'],					'zip'=>$row['zip'],					'first_name'=>$row['first_name'],					'last_name'=>$row['last_name'],					'nick_name'=>$row['nick_name'],					'phone'=>$row['phone'],					'last_login'=>$row['last_login'],					'role'=>array					(						'id'=>$row['roles_id'],					),					'picture'=>array					(						'id'=>$row['pictures_id']					)				));				$data[] = $user;				if(!$return_array)					return $data[0];			}			return $data;		}		return false;	}	public function exists($email, $users_id_ignored='')	{		$sql=			'SELECT				users.id			FROM				users			WHERE			(				TRIM(email)=TRIM("'. addslashes($email) .'")			)';		if(!empty($users_id_ignored))			$sql.=' AND id<>'. intval($users_id_ignored);		if($rs=pdologged_query($sql))		{			if($row=$rs->fetch(PDO::FETCH_ASSOC))				return $row['id'];			return false;		}		return true;	}	public function login($email, $password)	{		$where=			'(				email=:email				AND				approved = 1			)			AND			password=SHA1(CONCAT(:password, salt))';		$values = array(			':email'=>$email,			':password'=>$password		);		if($user=$this->load($where, $values, false))		{			$user->update_last_login();			return $user;		}		return false;	}	public function sendForgotPasswordLink($email)	{        $subject = 'Site Password Recovery';        $message = 'This email has been sent to you automatically in order to change your password. To continue the process, please click the below link. <br><br><a href="' . CURRENT_DOMAIN . '/forgot-pw.php?key=:KEY">' . CURRENT_DOMAIN . '/forgot-pw.php?key=:KEY</a>';        return $this->sendSecurityEmail($email, $subject, $message);	}    public function sendConfirmAccountLink($email)    {        $subject = 'Confirmation';        $message = 'This email has been sent to you automatically in order to confirm your account. To continue the process, please click the below link. <br><br><a href="' . CURRENT_DOMAIN . '/confirm.php?key=:KEY">' . CURRENT_DOMAIN . '/confirm.php?key=:KEY</a>';        return $this->sendSecurityEmail($email, $subject, $message);    }    private function sendSecurityEmail($email, $subject, $message)    {        if($id = self::exists($email))        {            if(is_numeric($id))            {                $key = randString(20);                $sql =                    'UPDATE users SET                        password_retrieval_key = "'.addslashes($key).'"					WHERE						id='.$id.'					LIMIT 1';                if($rs=pdologged_query($sql))                {                    //inject key into confirm and forgotpw links                    $message = str_replace(':KEY', $key, $message);                    UserController::sendMail($email, $subject, $message);                    return true;                }            }        }        return false;    }	public function setPassword($key, $password)	{		$salt=randString(10);		$sql =			'UPDATE users SET				password = SHA1(CONCAT("'.addslashes($password).'", "'. addslashes($salt) .'")),				salt="'. addslashes($salt) .'",				password_retrieval_key = NULL			WHERE				password_retrieval_key="'.addslashes($key).'"			LIMIT 1';		if($rs=pdologged_exec($sql))		{			if($rs > 0)				return true;		}		return false;	}    public function confirmAccount($key)    {        $salt=randString(10);        $sql =            'UPDATE users SET				password_retrieval_key = NULL,				approved = 1			WHERE				password_retrieval_key="'.addslashes($key).'"			LIMIT 1';        if($rs=pdologged_exec($sql))        {            if($rs > 0)                return true;        }        return false;    }}class User extends Tobject{	protected $id, $email, $password, $nick_name, $phone;	protected $role, $picture;	protected $permissions;	protected $address, $city, $state, $zip;	protected $last_name, $first_name, $last_login;    protected $referral;	function __construct($properties=NULL)	{		parent::__construct('users',$properties);		if(isset($properties['role']))        {            $rolesManager = new Roles();            $this->role = $rolesManager->loadById($properties['role']['id']);        }		if(isset($properties['picture']))			$this->picture=new Picture($properties['picture']);	}	public function get_id()	{		return intval($this->id);	}	public function get_role()	{		return ($this->role);	}	public function get_permissions()	{		return ($this->permissions);	}	public function get_picture()	{		return ($this->picture);	}	public function get_email()	{		return ($this->email);	}	public function get_city()	{		return ($this->city);	}	public function get_address()	{		return ($this->address);	}	public function get_zip()	{		return ($this->zip);	}	public function get_state()	{		return ($this->state);	}	public function get_first_name()	{		return ($this->first_name);    }	public function get_referral()	{		return ($this->referral);	}	public function get_last_name()	{		return ($this->last_name);	}	public function get_last_login()	{		return $this->last_login;	}	/*	Setter	*/    public function set_role($role_id)    {        $this->role = new Role(array('id'=>intval($role_id)));    }	public function set_permissions($permissions)	{		$this->permissions = $permissions;	}	public function set_last_login($last_login)	{		$this->last_login = $last_login;	}	public function add()	{		$salt = randString(10);		$roles_id=3;		if(!empty($this->role))//we check if object exists, but we might also need to check if id > 0			$roles_id=$this->role->get_id();		$pictures_id='NULL';		if(!empty($this->picture))			$pictures_id=$this->picture->get_id();		$sql =			'INSERT INTO users			(				roles_id,				pictures_id,				email,				password,				salt,				first_name,				last_name,				nick_name,				referral,				phone,				address, 				city, 				state, 				zip			)			VALUES			(				:roles_id,				:pictures_id,				:email,				SHA(:password),				:salt,				:first_name,				:last_name,				:nick_name,				:referral,				:phone,				:address,				:city,				:state,				:zip			)';		$values = array(			':roles_id'=>$roles_id,			':pictures_id'=>$pictures_id,			':email'=>($this->email),			':password'=>$this->password.$salt,			':salt'=>$salt,			':first_name'=>$this->first_name,			':last_name'=>$this->last_name,			':nick_name'=>$this->nick_name,			':referral'=>$this->referral,			':phone'=>$this->phone,			':address'=>$this->address,			':city'=>$this->city,			':state'=>$this->state,			':zip'=>$this->zip		);		if(pdologged_preparedQuery($sql, $values))		{			$this->id=Tabmin::$db->lastInsertId();			return true;		}		return false;	}	public function update()	{		$sql_simpleFields = $this->generateSimpleUpdateFields(array('password'));		$roles_id='';		if(!empty($this->role))//we check if object exists, but we might also need to check if id > 0			$roles_id='roles_id='.intval($this->role->get_id()).',';		$password='';		$salt='';		if(!empty($this->password))		{			$salt=addslashes(randString(10));			$password='password=SHA1(CONCAT("'.addslashes($this->password).'", "'. addslashes($salt) .'")),';			$salt='salt="'. $salt .'",';		}		$pictures_id='';		if(!empty($this->picture))			$pictures_id='pictures_id='.$this->picture->get_id().',';		$sql =			'UPDATE users SET				'.$roles_id.'				'.$password.'				'.$salt.'				'.$pictures_id.'				'.$sql_simpleFields->getStub().'			WHERE id=:id';		$values = $sql_simpleFields->getValues();		$values[':id'] = $this->get_id();		if(pdologged_preparedQuery($sql, $values) !== false)			return true;		return false;	}	public function update_last_login()	{		$this->last_login=date('Y-m-d h:i:s');		$sql =			'UPDATE users SET				last_login= :lastlogin			WHERE id=:id';		$values = array(			':lastlogin'=>date('Y-m-d h:i:s'),			':id'=>intval($this->id)		);		if(pdologged_preparedQuery($sql, $values) !== false)			return true;		return false;	}	public function hasPermission($module, $verb, $own=NULL)	{		if(!empty($this->permissions))			return $this->permissions->hasPermission($module, $verb, $own);		return false;	}	public function tabPermission($module, $tab)	{		if(!empty($this->permissions))			return $this->permissions->tabPermission($module, $tab);		return false;	}    /*     * Provide a complete export of the Current User's data     */    public function export()    {        $data = '';        $usersManager = new Users();        $user = $usersManager->loadById($this->id);        $storiesManager = new Stories();        $parameters = array();        $parameters['users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$user->get_id());        $stories=$storiesManager->searchLoadByParameters(@$_GET['s'], $parameters, '', '');        $charactersManager = new Characters();        $parameters = array();        $parameters['series.users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$user->get_id());        $characters=$charactersManager->searchLoadByParameters(@$_GET['s'], $parameters, 'stories_id', '');        $settingsManager = new Settings();        $parameters = array();        $parameters['users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$user->get_id());        $settings=$settingsManager->searchLoadByParameters(@$_GET['s'], $parameters, 'stories_id', '');        $plotEventsManager = new PlotEvents();        $parameters = array();        $parameters['users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$user->get_id());        $plot_events=$plotEventsManager->searchLoadByParameters(@$_GET['s'], $parameters, 'stories_id', '');        $relationship_types = array(            1=>'Allied',            2=>'Conflicted',            3=>'Romantic'        );        $data .= '<h1>Story Tracker - Data Export</h1>';        $data .= '<p>Below is a raw export of Story Tracker data. If anything is missing, please let us know. </p>';        //Table of Contents        $data .= '<h4>Stories</h4>';        $data .= '<ul>';        foreach($stories as $story)        {            $data .= '<li><a href="#story'.$story->get_id().'">' . $story->get_name() . '</a></li>';        }        $data .= '</ul>';        $data .= '<h4>Characters</h4>';        $data .= '<ul>';        foreach($characters as $character)        {            $data .= '<li><a href="#character'.$character->get_id().'">' . $character->get_full_name() . '</a></li>';        }        $data .= '</ul>';        $data .= '<h4>Settings</h4>';        $data .= '<ul>';        foreach($settings as $setting)        {            $data .= '<li><a href="#setting'.$setting->get_id().'">' . $setting->get_full_name() . '</a></li>';        }        $data .= '</ul>';        $data .= '<h4>Plot Events</h4>';        $data .= '<ul>';        foreach($plot_events as $plot_event)        {            $data .= '<li><a href="#plot_event'.$plot_event->get_id().'">' . $plot_event->get_event() . '</a></li>';        }        $data .= '</ul>';        $data .= '<h2>Account Information</h2>';        $data .= '<strong>Email: </strong>' . $user->get_email() . "<br />";        $data .= '<strong>First Name: </strong>' . $user->get_first_name(). "<br />";        $data .= '<strong>Last Name: </strong>' . $user->get_last_name(). "<br />";        $data .= '<strong>Last Login: </strong>' . date("M d, Y h:i:s a", strtotime($user->get_last_login())) . "<br />";        $data .= '<h2>Stories</h2>';        foreach($stories as $story)        {            $data.= '<a name="story' . $story->get_id() . '"/>';            $data.= '<h3>' . $story->get_name() . '</h3>';            $data.= '<strong>Series: </strong>' . $story->get_series()->get_name() . '<br />';            $data.= '<strong>Description: </strong>' . (strlen($story->get_description()) > 0 ? '<br />' . ($story->get_description()) : '<em>N/A</em>') . '<br />';            $data.= '<strong>Plot Synopsis: </strong>' . (strlen($story->get_synopsis()) > 0 ? '<br />' . ($story->get_synopsis()) : '<em>N/A</em>') . '<br />';            $data.= '<strong>Created on: </strong>' . $story->get_created() . '<br />';        }        $data .= '<h2>Characters</h2>';        foreach($characters as $character)        {            //get Model Data            $characterModel=$charactersManager->loadModel($character->get_series_id(), $character->get_id());            $groups = $characterModel->get_groups();            //get Timeline Data            $eventsManager = new CharacterEvents();            $parameters['characters_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$character->get_id());            $events = $eventsManager->searchLoadByParameters(@$_GET['s'], $parameters, 'characters_events.priority');            $data.= '<a name="character' . $character->get_id() . '"/>';            $data.= '<h3>' . $character->get_full_name() . '</h3>';            $data.= '<strong>Aliases: </strong>' . (strlen($character->get_aliases()) > 0 ? ($character->get_aliases()) : '<em>N/A</em>') . '<br />';            $data.= '<strong>Bio: </strong>' . (strlen($character->get_content()) > 0 ? '<br />' . ($character->get_content()) : '<em>N/A</em>') . '<br />';            if( count($groups) > 0 )            {                foreach($groups as $group)                {                    $fields = $group->get_fields();                    if( count($fields) > 0 )                    {                        $show_group = false;                        foreach($fields as $field)                        {                            if( $field->get_value() != '' )                            {                                $show_group = true;                                break;                            }                        }                        if( $show_group )                        {                            $data .= '<h4>' . htmlentitiesUTF8($group->get_name()) . '</h4>';                            foreach($fields as $field)                            {                                if( $field->get_value() != '' )                                {                                    $data.= '<strong>' . $field->get_name() . ': </strong>' .                                        (strlen($field->get_value()) > 0 ? '' .                                            ($field->get_value()) : '<em>N/A</em>') . '<br />';                                }                            }                        }                    }                }            }            if( count($events) > 0 )            {                $data .= '<h4>Timeline</h4>';                foreach($events as $event)                {                    $data.= '<strong>' . $event->get_description() . ': </strong>' .                        $event->get_time() . '<br />';                }            }            //Draw Relationships            $data .= '<h4>Relationships</h4>';            $any_relationships_found = false;            foreach($relationship_types as $i=>$relationship_type)            {                $relationships_loader = new RelationshipChartConnections();                $relationships = $relationships_loader->loadByCharacterAndType($character->get_id(), $i, true);                if( count($relationships) > 0 )                {                    foreach($relationships as $relationship)                    {                        $any_relationships_found = true;                        $data.= '<strong>' . ($relationship->get_node1()->get_characters_id() != $character->get_id() ?                            htmlentitiesUTF8($relationship->get_node1()->get_characters_name()) :                            htmlentitiesUTF8($relationship->get_node2()->get_characters_name())) . '</strong>';                        $data .= strlen($relationship->get_content()) > 0 ? ' - ' . ($relationship->get_content()) : '<em>N/A</em>';                    }                }            }            if( !$any_relationships_found )                $data .= '<em>This character has no relationships.</em>';        }        $data .= '<h2>Settings</h2>';        foreach($settings as $setting)        {            //get Model Data            $settingModel=$settingsManager->loadModel($setting->get_series_id(), $setting->get_id());            $groups = $settingModel->get_groups();            $data.= '<a name="setting' . $setting->get_id() . '"/>';            $data.= '<h3>' . $setting->get_full_name() . '</h3>';            $data.= '<strong>Aliases: </strong>' . (strlen($setting->get_aliases()) > 0 ? ($setting->get_aliases()) : '<em>N/A</em>') . '<br />';            $data.= '<strong>Description: </strong>' . (strlen($setting->get_content()) > 0 ? '<br />' . ($setting->get_content()) : '<em>N/A</em>') . '<br />';            if( count($groups) > 0 )            {                foreach($groups as $group)                {                    $fields = $group->get_fields();                    if( count($fields) > 0 )                    {                        $show_group = false;                        foreach($fields as $field)                        {                            if( $field->get_value() != '' )                            {                                $show_group = true;                                break;                            }                        }                        if( $show_group )                        {                            $data .= '<h4>' . htmlentitiesUTF8($group->get_name()) . '</h4>';                            foreach($fields as $field)                            {                                if( $field->get_value() != '' )                                {                                    $data.= '<strong>' . $field->get_name() . ': </strong>' .                                        (strlen($field->get_value()) > 0 ? '' .                                            ($field->get_value()) : '<em>N/A</em>') . '<br />';                                }                            }                        }                    }                }            }        }        $data .= '<h2>Plot Events</h2>';        foreach($plot_events as $plot_event)        {            $characters = $plot_event->get_characters();            $settings = $plot_event->get_settings();            $data .= '<a name="plot_event' . $plot_event->get_id() . '"/>';            $data .= '<h3>' . $plot_event->get_event() . '</h3>';            $data .= '<strong>Summary: </strong>' . (strlen($plot_event->get_summary()) > 0 ? '<br />' . ($setting->get_content()) : '<em>N/A</em>') . '<br />';            if( count($characters) )            {                $data .= '<strong>Characters: </strong>';                foreach($characters as $i=>$character)                {                    $data .= $character->get_full_name();                    if( $i < count($characters) - 1 )                        $data .= ', ';                }                $data .= '<br />';            }            if( count($settings) )            {                $data .= '<strong>Settings: </strong>';                foreach($settings as $j=>$setting)                {                    $data .= $setting->get_full_name();                    if( $j < count($settings) - 1 )                        $data .= ', ';                }                $data .= '<br />';            }        }        if( UserController::sendMail($user->get_email(), 'StoryTracker.net: Your Data Export', $data) )        {            AlertSet::addSuccess('A copy of your data has been sent to ' . $user->get_email() . '. Be sure to check your spam filter.                If you do not receive your export soon, please contact us. ');            return true;        }        else        {            AlertSet::addError('Your export could not be completed at this time, please try again or contact us.');        }        return false;    }}
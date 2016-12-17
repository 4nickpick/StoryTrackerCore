<?
class SettingController extends Controller
{

    protected $caption = 'Setting';

	protected function loadTobject($properties)
	{
		$this->tobject = new Setting($properties);

    }

    public function setTobject($properties)
    {
        $this->loadTobject($properties);
    }

    public function setNewNodes($new_nodes)
    {
        $this->json['new_nodes'] = $new_nodes;
    }
	
	public function add()
	{
        if( $this->user->hasPermission($this->module, 'add', $this->user->get_id()==$this->tobject->get_users_id()))
        {
            if( parent::add() )
            {
                $this->json['id'] = $this->tobject->get_id();
                return true;
            }
            return false;
        }
        else
        {
            AlertSet::addError('You do not have permission to add this setting.');
        }
    }
	
	public function update()
	{
        if( $this->user->hasPermission($this->module, 'edit', $this->user->get_id()==$this->tobject->get_users_id()))
        {
            if( parent::update() )
                $this->json['id'] = $this->tobject->get_id();
        }
        else
        {
            AlertSet::addError('You do not have permission to edit this setting.');
        }
    }

    public function delete()
    {
        if( $this->user->hasPermission($this->module, 'delete', $this->user->get_id()==$this->tobject->get_users_id()))
        {
            if( parent::delete() )
            {
                return true;
            }
            return false;
        }
        else
        {
            Console::add($this->user->get_id());
            Console::add($this->tobject->get_users_id());
            AlertSet::addError('You do not have permission to delete this setting.');
        }
    }

    public function addToStory()
    {
        if( $this->tobject->addToStory() ){
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            return true;
        }
        else {
            AlertSet::addError('Unable to add setting to story');
            return false;
        }

    }

    public function removeFromStory()
    {
        if( $this->tobject->removeFromStory() ){
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            return true;
        }
        else {
            AlertSet::addError('Unable to remove setting from story');
            return false;
        }

    }

    public function updateModel($data)
	{				
		if( Models::updateObject('settings', $this->tobject->get_id(), $data ) )
		{
			$this->json['id'] = $this->tobject->get_id();
		}
	}
	
	public function listUpdatePriorities($data, $moved_element)
	{
		$manager = new Settings();
		parent::listUpdatePriorities($manager, $data, $moved_element);
	}

	public function groupAdd($series_id)
	{
		$group = new Group(
			array(
				'series_id'=>$series_id
			)
		);
		
		if( $group->add('settings') )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Group added.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new groups. ');
		}
	}	
	
	public function groupEdit($users_id, $groups_id, $new_name)
	{
		$group = Groups::loadById('settings', $groups_id);
		$group->set_name($new_name);
		
		if( $group->update('settings') )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Group updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new groups. ');
		}
	}
	
	public function groupDelete($users_id, $groups_id)
	{
		$group = Groups::loadById('settings', $groups_id);
		
		if( $group->delete('settings') )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Group updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new groups. ');
		}
	}

	public function groupUpdatePriorities($users_id, $data, $moved_element)
	{
		if( Groups::updatePriorities('settings', $users_id, $data, $moved_element) )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Groups updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to arrange your groups. If there is a priority conflict, this should trigger an automatic updater. ');
		}
	}
	
	public function fieldAdd($users_id, $groups_id)
	{		
		$field = new Field(
			array(
				'groups_id'=>$groups_id
			)
		);
		
		if( $field->add('settings') )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Field added.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new groups. ');
		}
	}	
	
	public function fieldEdit($users_id, $groups_id, $new_name)
	{
		$field = Fields::loadById('settings', $groups_id);
		$field->set_name($new_name);
		
		if( $field->update('settings') )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Field updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new fields. ');
		}
	}
	
	public function fieldDelete($users_id, $groups_id)
	{
		$field = Fields::loadById('settings', $groups_id);
		
		if( $field->delete('settings') )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Field updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new fields. ');
		}
	}
	
	public function fieldUpdatePriorities($users_id, $groups_id, $data, $moved_element)
	{
		if( Fields::updatePriorities('settings', $users_id, $groups_id, $data, $moved_element) )
		{
			$this->json['success'] = true;
			AlertSet::addSuccess('Fields updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to arrange your fields. If there is a priority conflict, this should trigger an automatic updater. ');
		}
    }
}
?>
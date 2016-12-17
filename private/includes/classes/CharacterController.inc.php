<?
class CharacterController extends Controller
{

    protected $caption = 'Character';

	protected function loadTobject($properties)
	{
		$this->tobject = new Character($properties);

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
            AlertSet::addError('You do not have permission to add this character.');
        }
	}
	
	public function update()
	{
        if( $this->user->hasPermission($this->module, 'add', $this->user->get_id()==$this->tobject->get_users_id()))
        {
            if( parent::update() )
                $this->json['id'] = $this->tobject->get_id();
        }
        else
        {
            AlertSet::addError('You do not have permission to edit this character.');
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
            AlertSet::addError('You do not have permission to delete this character.');
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
            AlertSet::addError('Unable to add character to story');
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
            AlertSet::addError('Unable to remove character from story');
            return false;
        }

    }

    public function updateModel($data)
	{				
		if( Models::updateObject('characters', $this->tobject->get_id(), $data ) )
		{
			$this->json['id'] = $this->tobject->get_id();
		}
	}
	
	public function listUpdatePriorities($data, $moved_element)
	{
		$manager = new Characters();
		parent::listUpdatePriorities($manager, $data, $moved_element);
	}

	public function timelineUpdatePriorities($characters_id, $data, $moved_element)
	{
		$manager = new CharacterEvents();
		if( parent::listUpdatePriorities($manager, $data, $moved_element) )
        {
            $this->json['characters_id'] = $characters_id;
        }
	}

	public function groupAdd($series_id)
	{
		$group = new Group(
			array(
				'series_id'=>$series_id
			)
		);
		
		if( $group->add('characters') )
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
		$group = Groups::loadById('characters', $groups_id);
		$group->set_name($new_name);
		
		if( $group->update('characters') )
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
		$group = Groups::loadById('characters', $groups_id);
		
		if( $group->delete('characters') )
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

	public function eventAdd($characters_id, $event_name, $event_time)
	{
		$event = new CharacterEvent(
			array(
				'characters_id'=>$characters_id,
                'description'=>$event_name,
                'time'=>$event_time
			)
		);

		if( $event->add('characters') )
		{
			$this->json['success'] = true;
			$this->json['characters_id'] = $characters_id;
			AlertSet::addSuccess('Event added.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to add new event. ');
		}
	}

	public function eventEdit($users_id, $characters_events_id, $new_name, $new_time)
	{
        $eventManager = new CharacterEvents();
        $event = $eventManager->loadById($characters_events_id);
        $characters_id = $event->get_characters_id();
        $event->set_description($new_name);
        $event->set_time($new_time);

		if( $event->update('characters') )
		{
			$this->json['success'] = true;
			$this->json['characters_id'] = $characters_id;
			AlertSet::addSuccess('Event updated.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to edit event. ');
		}
	}

	public function eventDelete($users_id, $characters_events_id)
	{
		$eventManager = new CharacterEvents();
        $event = $eventManager->loadById($characters_events_id);
        $characters_id = $event->get_characters_id();

        if( !$event || $event->get_id() <= 0 )
        {
            $this->json['success'] = false;
            AlertSet::addError('An error occurred trying to delete event, Event does not exist. ');
            return false;
        }

		if( $event->delete('characters_events') )
		{
			$this->json['success'] = true;
			$this->json['characters_id'] = $characters_id;
			AlertSet::addSuccess('Event deleted.');
		}
		else
		{
			$this->json['success'] = false;
			AlertSet::addError('An error occurred trying to delete event. ');
		}

        return false;
	}

	public function groupUpdatePriorities($users_id, $data, $moved_element)
	{
		if( Groups::updatePriorities('characters', $users_id, $data, $moved_element) )
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
		
		if( $field->add('characters') )
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
		$field = Fields::loadById('characters', $groups_id);
		$field->set_name($new_name);
		
		if( $field->update('characters') )
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
		$field = Fields::loadById('characters', $groups_id);
		
		if( $field->delete('characters') )
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
		if( Fields::updatePriorities('characters', $users_id, $groups_id, $data, $moved_element) )
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

    public function chartAdd($series_id)
    {
        $chart = new RelationshipChart(
            array(
                'series_id'=>$series_id
            )
        );

        if( $chart->add() )
        {
            $this->json['success'] = true;
            $this->json['id'] = $chart->get_id();
            AlertSet::addSuccess('Chart added.');
        }
        else
        {
            $this->json['success'] = false;
            AlertSet::addError('An error occurred trying to add new charts. ');
        }
    }

    public function chartEdit($users_id, $charts_id, $new_name)
    {
        $loader = new RelationshipCharts();
        $chart = $loader->loadById($charts_id);
        $chart->set_name($new_name);


        if( $chart->update('characters') )
        {
            $this->json['success'] = true;
            AlertSet::addSuccess($new_name . ' saved.');
        }
        else
        {
            $this->json['success'] = false;
            AlertSet::addError('An error occurred trying to change the chart\'s name. ');
        }
    }

    public function chartDelete($charts_id, $users_id)
    {
        $chart = new RelationshipChart(
            array(
                'id'=>$charts_id
            )
        );

        if( $chart->delete() )
        {
            $this->json['success'] = true;
            AlertSet::addSuccess('Chart deleted.');
        }
        else
        {
            $this->json['success'] = false;
            AlertSet::addError('An error occurred trying to delete a chart. ');
        }
    }

    public function chartUpdatePriorities($data, $moved_element)
    {
        $chartsManager = new RelationshipCharts();
        if( $chartsManager->updatePriorities($data, $moved_element) )
        {
            $this->json['success'] = true;
            AlertSet::addSuccess('Charts updated.');
        }
        else
        {
            $this->json['success'] = false;
            AlertSet::addError('An error occurred trying to arrange your charts. If there is a priority conflict, this should trigger an automatic updater. ');
        }
    }

}
?>
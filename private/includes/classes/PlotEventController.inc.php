<?
class PlotEventController extends Controller
{

    protected $caption = 'Plot Event';

	protected function loadTobject($properties)
	{
		$this->tobject = new PlotEvent($properties);

    }

    public function setTobject($properties)
    {
        $this->loadTobject($properties);
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
            AlertSet::addError('You do not have permission to add this plot event.');
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
            AlertSet::addError('You do not have permission to edit this plot event.');
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
            AlertSet::addError('You do not have permission to delete this plot event.');
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
            AlertSet::addError('Unable to add plot event to story');
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
            AlertSet::addError('Unable to remove plot event from story');
            return false;
        }

    }

    public function addRelationship($table_name, $column_name, $related_object_id)
    {
        if( $this->tobject->addRelationship($table_name, $column_name, $related_object_id) ){
            AlertSet::AddInfo(var_export('relationship added', true));
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            return true;
        }
        else {
            AlertSet::addError('Unable to add relationship');
            return false;
        }

    }

    public function removeRelationship($table_name, $column_name, $related_object_id)
    {
        if( $this->tobject->removeRelationship($table_name, $column_name, $related_object_id) ){
            AlertSet::AddInfo(var_export('relationship removed', true));
            $this->json['id'] = $this->tobject->get_id();
            $this->json['success'] = true;
            return true;
        }
        else {
            AlertSet::addError('Unable to remove relationship');
            return false;
        }

    }


    public function listUpdatePriorities($data, $moved_element)
	{
		$manager = new PlotEvents();
		parent::listUpdatePriorities($manager, $data, $moved_element);
	}

}
?>
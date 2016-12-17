<?
class StoryController extends Controller
{
    protected $caption = 'Story';
	protected function loadTobject($properties)
	{
		$this->tobject = new Story($properties);
	}

	public function add()
	{
        if( $this->user->hasPermission($this->module, 'add', $this->user->get_id()==$this->tobject->get_series()->get_users_id()))
        {
            if( parent::add() )
            {
                $this->json['id'] = $this->tobject->get_id();
                return true;
            }
        }
        else
        {
            AlertSet::addError('You do not have permission to add this story.');
        }
		return false;
	}
	
	public function update()
	{
        if( $this->user->hasPermission($this->module, 'edit', $this->user->get_id()==$this->tobject->get_series()->get_users_id()))
        {
            if( parent::update() )
                $this->json['id'] = $this->tobject->get_id();
        }
        else
        {
            AlertSet::addError('You do not have permission to edit this story.');
        }
	}

    public function delete()
    {
        global $currentStory;

        if( $this->user->hasPermission($this->module, 'delete', $this->user->get_id()==$this->tobject->get_series()->get_users_id()))
        {
            if( parent::delete() ){
                $this->json['id'] = $this->tobject->get_id();
                if( isset($currentStory) )
                {
                    if( $currentStory->get_id() == $this->tobject->get_id() )
                    {
                        unset($currentStory);
                        unset($_SESSION['currentStory']);
                    }
                }
            }
        }
        else
        {
            AlertSet::addError('You do not have permission to delete this story.');
        }
    }

    public function listUpdatePriorities($data, $moved_element)
	{
		$manager = new Stories();
		parent::listUpdatePriorities($manager, $data, $moved_element);
	}
}
?>
<?
class CharacterEventController extends Controller
{
    protected $caption = 'Character Timeline Event';
	protected function loadTobject($properties)
	{
		$this->tobject = new Story($properties);
	}

	public function add()
	{
		if( parent::add() )
        {
            $this->json['id'] = $this->tobject->get_id();
            return true;
        }
		return false;
	}
	
	public function update()
	{				
		if( parent::update() )
			$this->json['id'] = $this->tobject->get_id();
	}

    public function delete()
    {
        if( parent::delete() ){
            $this->json['id'] = $this->tobject->get_id();
        }
    }

    public function listUpdatePriorities($data, $moved_element)
	{
		$manager = new CharacterEvents();
		parent::listUpdatePriorities($manager, $data, $moved_element);
	}
}
?>
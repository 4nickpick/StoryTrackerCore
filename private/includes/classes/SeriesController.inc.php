<?
class SeriesController extends Controller
{
	protected function loadTobject($properties)
	{
		$this->tobject = new Series($properties);
	}

	public function add()
	{
		if( parent::add() )
			$this->json['id'] = $this->tobject->get_id();
			
	}
	
	public function update()
	{				
		if( parent::update() )
			$this->json['id'] = $this->tobject->get_id();
	}
	
	/*public function listUpdatePriorities($data, $moved_element)
	{
		$manager = new Relationships();
		parent::listUpdatePriorities($manager, $data, $moved_element);
	}
	*/
}
?>
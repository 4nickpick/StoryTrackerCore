<?
class PermissionsController extends TobjectArrayController
{
	
	protected function loadTobject($properties)
	{		
		$this->tobject = new Permission($properties);
	}
	
	
	
	public function validate()
	{		
		if (intval($this->properties['role']['id']) < 1)
			AlertSet::addValidation('Please select a role');
		if(empty($this->properties['perm-verb']) && empty($this->properties['tab']) )
			AlertSet::addValidation('Either tab or verb must be set - usually they are set to the same value.');
	}
	
	public function add()
	{
		$this->validate();
		parent::add();
	}
	
	
	
	public function update()
	{
		$this->validate();
		parent::update();
	}
	
}
?>
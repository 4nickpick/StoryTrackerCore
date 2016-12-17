<?
class RelationshipChartNodesController extends TobjectArrayController
{
    protected $caption = 'Node';

	protected function loadTobject($properties)
	{
		$this->tobject = new RelationshipChartNode($properties);
	}

	public function validate()
	{		
		/*if (intval($this->properties['role']['id']) < 1)
			AlertSet::addValidation('Please select a role');
		if(empty($this->properties['perm-verb']) && empty($this->properties['tab']) )
			AlertSet::addValidation('Either tab or verb must be set - usually they are set to the same value.');*/
	}

    public function add()
    {
        $this->validate();

        if( parent::add() )
        {
            $this->json['id'] = $this->tobject->get_id();

            return true;
        }

        return false;
    }

    public function update()
    {
        $this->validate();
        return parent::update();
    }

    public function bulkAdd()
    {
        $this->setCheckXSRF(false);
        $this->validate();
        if( parent::bulkAdd() && AlertSet::$success )
        {
            if( AlertSet::$success )
            {
                AlertSet::clear();
                $this->json['success'] = true;
                AlertSet::addSuccess('Relationship Chart additions completed successfully...');
                return true;
            }
        }
        else
        {
            AlertSet::addError('Bulk add failed.');
        }

        return false;
    }

    public function bulkUpdate()
    {
        $this->setCheckXSRF(false);
        $this->validate();
        if( parent::bulkUpdate() && AlertSet::$success )
        {
            if( AlertSet::$success )
            {
                AlertSet::clear();
                AlertSet::addSuccess('Relationship Chart updated successfully.');
            }
        }
    }
    public function loadByChart($chart_id)
    {
        $parameters = array();
        $parameters['relationship_charts_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$chart_id);
        $nodes_controller = new RelationshipChartNodes();
        $loaded_nodes = $nodes_controller->loadByParameters($parameters);

        $nodes = array();
        foreach($loaded_nodes as $i=>$node)
        {
            $nodes[$i]['id'] = $node->get_id();
            $nodes[$i]['characters_id'] = $node->get_characters_id();
            $nodes[$i]['characters_name'] = $node->get_characters_name();
            $nodes[$i]['top'] = $node->get_top();
            $nodes[$i]['left'] = $node->get_left();
        }

        $this->json['success'] = true;

        return $nodes;
    }
}
?>
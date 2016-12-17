<?
class RelationshipChartConnectionsController extends TobjectArrayController
{
    protected $caption = 'Connection';

	protected function loadTobject($properties)
	{
		$this->tobject = new RelationshipChartConnection($properties);
	}

	public function validate()
	{		
		/*if (intval($this->properties['role']['id']) < 1)
			AlertSet::addValidation('Please select a role');
		if(empty($this->properties['perm-verb']) && empty($this->properties['tab']) )
			AlertSet::addValidation('Either tab or verb must be set - usually they are set to the same value.');
		*/
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

    public function bulkAdd()
    {
        $this->setCheckXSRF(false);
        $this->validate();
        if( parent::bulkAdd() && AlertSet::$success )
        {
            if( AlertSet::$success )
            {
                AlertSet::clear();
                AlertSet::addSuccess('Relationship Chart additions completed successfully.');
            }
        }
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
        $parameters['charts_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$chart_id);
        $connections_controller = new RelationshipChartConnections();
        $loaded_connections = $connections_controller->loadByParameters($parameters);

        $connections = array();
        foreach($loaded_connections as $i=>$connection)
        {
            $connections[$i]['id'] = $connection->get_id();
            $connections[$i]['sourceNode'] = $connection->get_node1()->get_id();
            $connections[$i]['targetNode'] = $connection->get_node2()->get_id();
            $connections[$i]['type'] = $connection->get_type();
        }

        $this->json['success'] = true;

        return $connections;
    }
	
}
?>
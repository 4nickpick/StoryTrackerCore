<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
if(@$_POST['verb']!='forgotpw' && @$_POST['verb']!='resetpw' && @$_POST['verb']!='signup')
	include INCLUDE_ROOT.'/ajax_secure.inc.php';
ErrorSet::setAJAX(true);

require_once GLOBAL_ROOT.'HTMLPurifier.inc.php';

$storyManager = new Stories();
$story = $storyManager->loadById(@$_POST['stories_id']);

if(empty($story))
{
    $story = $currentStory; //tagging depends on this, perhaps other actions
}

$properties = array
				(
					'id'=>@$_POST['characters_id'],
					'series_id'=>isset($story) ? $story->get_series()->get_id() : NULL,
					'users_id'=>isset($story) ? $story->get_series()->get_users_id() : NULL,
					'story'=>array('id'=>isset($story) ? $story->get_id() : NULL),
					'full_name'=>@$_POST['full_name'],
					'aliases'=>@$_POST['aliases'],
					'content'=>$purifier->purify(@$_POST['content'])
				);

$controller = new CharacterController($properties, $currentUser, $module);

$controller->setAllRequired(array(
		'full_name'=>'Full Name',
		));
$module = 'characters';
switch(@$_POST['verb'])
{
	case 'add':
		$controller->add();
		$controller->updateModel(@$_POST['fields']);
	break;
	case 'edit':
		$controller->update();
		$controller->updateModel(@$_POST['fields']);
	break;
	case 'delete':
		$controller->delete();
	break;
    case 'add-to-story':
        $controller->addToStory();
        break;
    case 'remove-from-story':
        $controller->removeFromStory();
    break;
	case 'group_add':
		$controller->groupAdd($currentStory->get_series()->get_id());
	break;
	case 'group_edit':
		$controller->groupEdit($currentUser->get_id(), $_POST['groups_id'], $_POST['name']);
	break;
	case 'group_delete':
		$controller->groupDelete($currentUser->get_id(), $_POST['groups_id']);
	break;
	case 'event_add':
		$controller->eventAdd($_POST['characters_id'], $_POST['event_name'], $_POST['event_time']);
	break;
	case 'event_edit':
        $controller->eventEdit($currentUser->get_id(), $_POST['characters_events_id'], $_POST['new_name'], $_POST['new_time']);
	break;
	case 'event_delete':
		$controller->eventDelete($currentUser->get_id(), $_POST['characters_events_id']);
	break;
	case 'field_add':
		$controller->fieldAdd($currentUser->get_id(), $_POST['groups_id']);
	break;
	case 'field_edit':
		$controller->fieldEdit($currentUser->get_id(), $_POST['fields_id'], $_POST['name']);
	break;
	case 'field_delete':
		$controller->fieldDelete($currentUser->get_id(), $_POST['fields_id']);
	break;
    case 'chart_add':
        $controller->chartAdd($currentStory->get_series()->get_id());
    break;
    case 'chart_edit':
        $controller->chartEdit($currentUser->get_id(), $_POST['charts_id'], $_POST['new_name']);
        break;
    case 'chart_delete':
        $controller->chartDelete($_POST['charts_id'], $currentUser->get_id());
        break;
    case 'list_update_priority':
        $controller->listUpdatePriorities($_POST['characters'], $_POST['moved_element']);
    break;
    case 'timeline_update_priority':
        $controller->timelineUpdatePriorities($_POST['characters_id'], $_POST['events'], $_POST['moved_element']);
    break;
	case 'group_update_priority':
		$controller->groupUpdatePriorities($currentUser->get_id(), $_POST['groups'], $_POST['moved_element']);
	break;
	case 'field_update_priority':
		$controller->fieldUpdatePriorities($currentUser->get_id(), $_POST['groups_id'], $_POST['fields'], $_POST['moved_element']);
	break;
    case 'chart_update_priority':
        $controller->chartUpdatePriorities($_POST['charts'], $_POST['moved_element']);
    break;
    case 'add_nodes':

    if( count(@$_POST['nodes']) > 0 )
    {
        $properties = array();
        foreach($_POST['nodes'] as $node)
        {
            $properties[] = array(
                'relationship_charts_id'=>$node['charts_id'],
                'characters_id'=>$node['characters_id'],
                'top'=>$node['top'],
                'left'=>$node['left']
            );
        }

        $controller = new RelationshipChartNodesController($properties, $currentUser, 'characters', true, false);
        $controller->bulkAdd();
    }
    break;

    case 'add_new_characters_via_chart':

        if( !isset($_POST['chart_id']) )
            AlertSet::addError('Chart must be specified');

        if( AlertSet::$success && count(@$_POST['character_names']) > 0 )
        {
            $properties = array();
            $new_nodes = array();
            $controller = new CharacterController($properties, $currentUser, 'characters');
            foreach($_POST['character_names'] as $character_name)
            {
                if( $character_name == '' )
                    continue;

                $properties = array(
                    'full_name'=>$character_name,
                    'series_id'=>$currentStory->get_series()->get_id(),
                    'story'=>array('id'=>$currentStory->get_id()),
                    'users_id'=>$currentUser->id
                );

                $controller->setTobject($properties);
                $controller->setCheckXSRF(false);

                if( $controller->add() )
                {
                    $new_nodes_index = count($new_nodes);

                    $new_nodes[$new_nodes_index]['characters_id'] = $controller->get_tobject()->get_id();
                    $new_nodes[$new_nodes_index]['characters_name'] = $controller->get_tobject()->get_full_name();
                    $new_nodes[$new_nodes_index]['top'] = 40 * $new_nodes_index;
                    $new_nodes[$new_nodes_index]['left'] = 40 * $new_nodes_index;
                }
            }

            if( is_array($new_nodes) && count($new_nodes) > 0 )
            {
                $properties = array();
                foreach($new_nodes as $i=>$node)
                {
                    $properties = array(
                        'relationship_charts_id'=>@$_POST['chart_id'],
                        'characters_id'=>$node['characters_id'],
                        'characters_name'=>$node['characters_name'],
                        'top'=>$node['top'],
                        'left'=>$node['left']
                    );

                    $nodes_controller = new RelationshipChartNodesController($properties, $currentUser, 'characters', false, false);

                    $nodes_controller->setCheckXSRF(false);
                    if( $nodes_controller->add() )
                        $new_nodes[$i]['id'] = $nodes_controller->get_tobject()->get_id();

                    $controller->setNewNodes($new_nodes);
                }

            }
            else
            {
                Console::add('No new nodes detected');
            }
        }
        break;

    case 'add_connection':
        if( is_array(@$_POST['connections']) )
        {
            $properties = array();
            foreach($_POST['connections'] as $connection)
            {
                if( intval($connection['nodes1_id']) > 0 && intval($connection['nodes2_id']) > 0 )
                {
                    $properties[] = array(
                        'chart'=>array('id'=>$connection['charts_id']),
                        'node1'=>array('id'=>$connection['nodes1_id']),
                        'node2'=>array('id'=>$connection['nodes2_id']),
                        'type'=>$connection['type'],
                        'content'=>NULL
                    );
                }
            }

            if( count($properties) > 0 )
            {
                $controller = new RelationshipChartConnectionsController($properties, $currentUser, 'characters', true, false);
                $controller->bulkAdd();
            }
        }
    break;

    case 'edit_connection':
        $properties = array();
        if( isset($_POST['connections_id']) )
        {
            $properties[] = array(
                'id'=>($_POST['connections_id']),
                'type'=>($_POST['connections_type']),
                'content'=>$_POST['content'] != '' ? $_POST['content'] : NULL,
                //'type'=>$connection['type']
            );

            if( count($properties) > 0 )
            {
                $controller = new RelationshipChartConnectionsController($properties, $currentUser, 'characters', true, false);
                $controller->setCheckXSRF(false);
                $controller->update();
            }
        }
        break;

    case 'delete_connection':
        $properties = array();
        if( isset($_POST['connections_id']) )
        {
            $properties[] = array(
                'id'=>($_POST['connections_id']),
                //'type'=>$connection['type']
            );

            if( count($properties) > 0 )
            {
                $controller = new RelationshipChartConnectionsController($properties, $currentUser, 'characters', true, false);
                $controller->setCheckXSRF(false);
                if(!$controller->delete())
                {
                    AlertSet::addError('Unable to delete connection');
                }
            }
        }
        break;
    case 'save_chart':

        if( count(@$_POST['nodes']) > 0 )
        {
            $properties = array();
            foreach($_POST['nodes'] as $node)
            {
                $properties[] = array(
                    'id'=>$node['nodes_id'],
                    'relationship_charts_id'=>$node['charts_id'],
                    'characters_id'=>$node['characters_id'],
                    'top'=>$node['top'],
                    'left'=>$node['left']
                );
            }

            $controller = new RelationshipChartNodesController($properties, $currentUser, 'characters', true, false);
            $controller->bulkUpdate();
        }

        if( count(@$_POST['relationships']) > 0 )
        {
            $properties = array();
            foreach($_POST['relationships'] as $relationship)
            {
                $properties[] = array(
                    'id'=>$relationship['id'],
                    'nodes1_id'=>$relationship['nodes1_id'],
                    'nodes2_id'=>$relationship['nodes2_id'],
                    'relationship_charts_id'=>$relationship['charts_id']
                );
            }

            $controller = new RelationshipChartConnectionsController($properties, $currentUser, 'characters', true, false);
            $controller->bulkUpdate();
        }

    break;
    case 'get_chart_data':
        $controller = new RelationshipChartNodesController(NULL, $currentUser, 'characters', true, false);
        $nodes = $controller->loadByChart(@$_POST['charts_id']);

        $controller = new RelationshipChartConnectionsController(NULL, $currentUser, 'characters', true, false);
        $connections = $controller->loadByChart(@$_POST['charts_id']);

        $json = array('nodes'=>$nodes, 'connections'=>$connections, 'success'=>true);
        echo json_encode($json);
        die();

    break;
    case 'delete_node_and_connections':
        $controller = new RelationshipChartNodesController(NULL, $currentUser, 'characters', true, false);
        $node_loader = new RelationshipChartNodes();
        $node = $node_loader->loadById(@$_POST['nodes_id']);

        if( !$node )
            AlertSet::addError('Node not found');
        else
        {
            $conn_controller = new RelationshipChartConnectionsController(NULL, $currentUser, 'characters', true, false);
            $conn_loader = new RelationshipChartConnections();
            $connections = $conn_loader->loadByNodesId($node->get_id());

            $controller->setCheckXSRF(false);
            if( $controller->delete() )
            {
                $conn_controller->setTobjects($connections);
                $conn_controller->setCheckXSRF(false);
                if( $conn_controller->bulkDelete() )
                {
                    $controller->setTobject($node);
                    $controller->delete();
                }

            }

        }
    break;
    case 'get_connection_dialog':
        $loader = new RelationshipChartConnections();
        $parameters['charts_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>@$_POST['charts_id']);
        $parameters['nodes1_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>@$_POST['source_id']);
        $parameters['nodes2_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>@$_POST['target_id']);
        $connections = $loader->loadByParameters($parameters);

        if( count($connections) > 0 )
        {
            $connection = $connections[0];
            $json = array(
                'connections_id'=>$connection->get_id(),
                'connections_type'=>$connection->get_type(),
                'names'=>$connection->get_node1()->get_characters_name() . ' and ' .
                    $connection->get_node2()->get_characters_name(),
                'content'=>$connection->get_content(),
                'success'=>true
            );
            echo json_encode($json);
            die();
        }
        else
        {
            AlertSet::addError('Could not load connection dialog.');
        }

        break;
    case 'get_node_dialog':
        $loader = new RelationshipChartNodes();
        $node = $loader->loadById(@$_POST['nodes_id']);

        if( isset($node) && $node->get_id() > 0 )
        {
            $json = array(
                'characters_name'=>$node->get_characters_name(),
                'characters_id'=>$node->get_characters_id(),
                'nodes_id'=>$node->get_id()
            );
            echo json_encode($json);
            die();
        }
        else
        {
            AlertSet::addError('Could not load connection dialog.');
        }

        break;
    default:
		AlertSet::AddError('Verb not recognized: ' . @$_POST['verb']);
	break;
}

echo $controller->get_json();


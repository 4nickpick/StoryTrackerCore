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
    $story = $currentStory;
}

$properties = array
				(
					'id'=>@$_POST['plot_events_id'],
                    'series_id'=>isset($story) ? $story->get_series()->get_id() : NULL,
                    'users_id'=>isset($story) ? $story->get_series()->get_users_id() : NULL,
                    'story'=>array('id'=>isset($story) ? $story->get_id() : NULL),
					'event'=>@$_POST['event'],
					'summary'=>$purifier->purify(@$_POST['summary'])
				);

$module = 'plot';
$controller = new PlotEventController($properties, $currentUser, $module);

$controller->setAllRequired(array(
		'event'=>'Event',
		));
switch(@$_POST['verb'])
{
	case 'add':			
		$controller->add();
	break;
	case 'edit':			
		$controller->update();
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
    case 'list_update_priority':
        $controller->listUpdatePriorities($_POST['plot'], $_POST['moved_element']);
    break;
    case 'add_new_characters_to_plot':

        if( !isset($_POST['plot_events_id']) )
            AlertSet::addError('Plot Event must be specified');

        if( AlertSet::$success && count(@$_POST['character_names']) > 0 )
        {
            $properties = array();
            $new_nodes = array();
            $characterController = new CharacterController($properties, $currentUser, 'characters');
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

                $characterController->setTobject($properties);
                $characterController->setCheckXSRF(false);

                if( $characterController->add() )
                {
                    $new_nodes_index = count($new_nodes);

                    $new_nodes[$new_nodes_index]['characters_id'] = $characterController->get_tobject()->get_id();
                    $new_nodes[$new_nodes_index]['characters_name'] = $characterController->get_tobject()->get_full_name();
                }
                else
                {
                    AlertSet::addError('Could not add Character');
                }
            }

            if( is_array($new_nodes) && count($new_nodes) > 0 )
            {
                foreach($new_nodes as $i=>$node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['plot_events_id'],
                        'users_id'=>$currentUser->id
                    );
                    $controller = new PlotEventController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'characters_to_plot_events',
                        'characters_id',
                        $node['characters_id']
                    );
                }
            }
            else
            {
                Console::add('No new nodes detected');
            }
        }
        break;
    case 'add_characters_to_plot':

        if( !isset($_POST['plot_events_id']) )
            AlertSet::addError('Plot Event must be specified');



        if( AlertSet::$success )
        {
            if( count(@$_POST['nodes']) > 0 )
            {
                $properties = array();
                foreach($_POST['nodes'] as $node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['plot_events_id'],
                        'users_id'=>$currentUser->id
                    );
                    $controller = new PlotEventController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'characters_to_plot_events',
                        'characters_id',
                        $node['characters_id']
                    );
                }
            }
        }
        break;
        case 'add_new_settings_to_plot':

        if( !isset($_POST['plot_events_id']) )
            AlertSet::addError('Plot Event must be specified');

        if( AlertSet::$success && count(@$_POST['setting_names']) > 0 )
        {
            $properties = array();
            $new_nodes = array();
            $settingController = new SettingController($properties, $currentUser, 'settings');
            foreach($_POST['setting_names'] as $setting_name)
            {
                if( $setting_name == '' )
                    continue;

                $properties = array(
                    'full_name'=>$setting_name,
                    'series_id'=>$currentStory->get_series()->get_id(),
                    'story'=>array('id'=>$currentStory->get_id()),
                    'users_id'=>$currentUser->id
                );

                $settingController->setTobject($properties);
                $settingController->setCheckXSRF(false);

                if( $settingController->add() )
                {
                    $new_nodes_index = count($new_nodes);

                    $new_nodes[$new_nodes_index]['settings_id'] = $settingController->get_tobject()->get_id();
                    $new_nodes[$new_nodes_index]['settings_name'] = $settingController->get_tobject()->get_full_name();
                }
                else
                {
                    AlertSet::addError('Could not add Setting');
                }
            }

            if( is_array($new_nodes) && count($new_nodes) > 0 )
            {

                foreach($new_nodes as $i=>$node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['plot_events_id']
                    );
                    $controller = new PlotEventController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'settings_to_plot_events',
                        'settings_id',
                        $node['settings_id']
                    );
                }
            }
            else
            {
                Console::add('No new nodes detected');
            }
        }
        break;
        case 'add_settings_to_plot':

        if( !isset($_POST['plot_events_id']) )
            AlertSet::addError('Plot Event must be specified');



        if( AlertSet::$success )
        {
            if( count(@$_POST['nodes']) > 0 )
            {
                $properties = array();
                foreach($_POST['nodes'] as $node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['plot_events_id'],
                        'users_id'=>$currentUser->id
                    );
                    $controller = new PlotEventController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'settings_to_plot_events',
                        'settings_id',
                        $node['settings_id']
                    );
                }
            }
        }
        break;
    case 'remove-tag':
        switch( $_POST['object_type'] )
        {
            case 'characters':
                $controller->removeRelationship(
                    'characters_to_plot_events',
                    'characters_id',
                    $_POST['characters_id']
                );
                break;
            case 'settings':
                $controller->removeRelationship(
                    'settings_to_plot_events',
                    'settings_id',
                    $_POST['settings_id']
                );
                break;
        }
        break;
    default:
		AlertSet::AddError('Verb not recognized: ' . @$_POST['verb']);
	break;
}

echo $controller->get_json();


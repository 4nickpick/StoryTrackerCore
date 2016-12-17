<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
if(@$_POST['verb']!='forgotpw' && @$_POST['verb']!='resetpw' && @$_POST['verb']!='signup')
	include INCLUDE_ROOT.'/ajax_secure.inc.php';
ErrorSet::setAJAX(true);

require_once GLOBAL_ROOT.'HTMLPurifier.inc.php';

$series = NULL;
if( $_POST['verb'] == 'add' &&
    ((isset($_POST['series_id']) && $_POST['series_id'] != 'no_series') || isset($_POST['series_name'])) )
{
    $series_properties = array (
        'id'=>isset($_POST['series_id']) ? intval($_POST['series_id']) : NULL,
        'name'=>isset($_POST['series_name']) ? ($_POST['series_name']) : NULL,
        'is_series'=> !(isset($_POST['series_id']) && $_POST['series_id'] == 'no_series'),
        'users_id'=>@$_POST['users_id']
    );
}
else
{
    if( !isset($_POST['users_id']) )
    {
        $storyManager = new Stories();
        $story = $storyManager->loadById(@$_POST['stories_id']);
    }
    $series_properties = array (
        'id'=>isset($_POST['series_id']) ? intval($_POST['series_id']) : NULL,
        'users_id'=>isset($story) ? $story->get_series()->get_users_id() : @$_POST['users_id']
    );
}

$properties = array
    (
        'id'=>@$_POST['stories_id'],
        'series'=>$series_properties,
        'name'=>@$_POST['name'],
        'description'=>$purifier->purify(@$_POST['description']),
        'synopsis'=>$purifier->purify(@$_POST['synopsis'])
    );

$controller = new StoryController($properties, $currentUser, $module);

$controller->setAllRequired(array(
		'name'=>'Name',
		));
$module = 'stories';
switch(@$_POST['verb'])
{
	case 'add':			
		if( $controller->add() )
        {
            $_SESSION['currentStory'] = $controller->get_tobject()->get_id();
        }
	break;
	case 'edit':			
		$controller->update();
	break;
	case 'delete':			
		$controller->delete();
	break;
    case 'list_update_priority':
        $controller->listUpdatePriorities($_POST['stories'], $_POST['moved_element']);
        break;
    default:
		AlertSet::AddError('Verb not recognized: ' . @$_POST['verb']);
	break;
}

echo $controller->get_json();


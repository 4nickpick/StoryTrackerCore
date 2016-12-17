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
					'id'=>@$_POST['settings_id'],
					'series_id'=>isset($story) ? $story->get_series()->get_id() : NULL,
					'users_id'=>isset($story) ? $story->get_series()->get_users_id() : NULL,
					'story'=>array('id'=>isset($story) ? $story->get_id() : NULL),
					'full_name'=>@$_POST['full_name'],
					'aliases'=>@$_POST['aliases'],
					'content'=>$purifier->purify(@$_POST['content'])
				);

$controller = new SettingController($properties, $currentUser, $module);

$controller->setAllRequired(array(
		'full_name'=>'Full Name',
		));
$module = 'settings';
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
	case 'field_add':
		$controller->fieldAdd($currentUser->get_id(), $_POST['groups_id']);
	break;
	case 'field_edit':
		$controller->fieldEdit($currentUser->get_id(), $_POST['fields_id'], $_POST['name']);
	break;
	case 'field_delete':
		$controller->fieldDelete($currentUser->get_id(), $_POST['fields_id']);
	break;
    case 'list_update_priority':
        $controller->listUpdatePriorities($_POST['settings'], $_POST['moved_element']);
    break;
	case 'group_update_priority':
		$controller->groupUpdatePriorities($currentUser->get_id(), $_POST['groups'], $_POST['moved_element']);
	break;
	case 'field_update_priority':
		$controller->fieldUpdatePriorities($currentUser->get_id(), $_POST['groups_id'], $_POST['fields'], $_POST['moved_element']);
	break;
    default:
		AlertSet::AddError('Verb not recognized: ' . @$_POST['verb']);
	break;
}

echo $controller->get_json();


<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
if(@$_POST['verb']!='forgotpw' && @$_POST['verb']!='resetpw' && @$_POST['verb']!='signup')
	include INCLUDE_ROOT.'/ajax_secure.inc.php';
ErrorSet::setAJAX(true);

$picture_properties = array (
    'id'=>(isset($_POST['pictures_id']) ? intval($_POST['pictures_id']) : NULL),
    'caption'=>(isset($_POST['caption']) ? $_POST['caption'] : NULL),
    'users_id'=>$currentUser->get_id()
);

if( isset($_FILES['files']) )
{
    $picture_properties['picture_file'] = @$_FILES['files'];

    if( count($_FILES) > 0 )
    {
        for($i=0; $i<count($_FILES); $i++)
        {
            $content_type = NULL;
            switch( exif_imagetype($picture_properties['picture_file']['tmp_name'][$i] ) )
            {
                case IMAGETYPE_JPEG:
                case IMAGETYPE_PNG:
                    $content_type = 'image/jpeg';
                    break;

                default:
                    AlertSet::AddError('File type not allowed.');
                    break;
            }
        }
    }
    //AlertSet::AddError(var_export($picture_properties['picture_file'], true));
}
else if( $_POST['verb'] == 'add-from-internet')
{
    if( ini_get('allow_url_fopen') )
    {
        $tmp_name = tempnam(TEMP_ROOT, 'webupload');
        if( !copy(@$_POST['image_link'], $tmp_name) )
        {
            AlertSet::AddError('Could not copy file from internet.');
        }
        else
        {
            $content_type = NULL;
            switch( exif_imagetype($tmp_name) )
            {
                case IMAGETYPE_JPEG:
                case IMAGETYPE_PNG:
                    $content_type = 'image/jpeg';
                    break;

                default:
                    AlertSet::AddError('File type not allowed.');
                    break;
            }

            if( $content_type !== NULL )
            {
                $picture_properties['picture_file'] = array(
                    'name'=>array(''),
                    'size'=>array(filesize($tmp_name)),
                    'tmp_name'=>array($tmp_name),
                    'type'=>array($content_type)
                );
            }
        }
    }
    else
    {
        AlertSet::addError('A problem occurred - files cannot be downloaded.');
    }
}

$pictureController = new PictureController($picture_properties, $currentUser, $module);
$pictureController->setCheckXSRF(false);

$pictureFilesManager = new PictureFiles();

switch(@$_POST['verb'])
{
	case 'add':
        if( AlertSet::$success )
        {
            if( $pictureController->add() )
                AlertSet::addSuccess('Picture(s) uploaded successfully.');

            if( AlertSet::$success )
            {

                switch( $_POST['object_type'] )
                {
                    case 'characters':
                        $pictureController->addRelationship(
                            'pictures_to_characters',
                            'characters_id',
                            $_POST['object_id']
                        );
                        break;
                    case 'settings':
                        $pictureController->addRelationship(
                            'pictures_to_settings',
                            'settings_id',
                            $_POST['object_id']
                        );
                        break;
                    case 'plot_events':
                        $pictureController->addRelationship(
                            'pictures_to_plot_events',
                            'plot_events_id',
                            $_POST['object_id']
                        );
                        break;
                }
            }
        }
	break;
	case 'add-from-internet':
        if( AlertSet::$success )
        {
            if( $pictureController->add() )
                AlertSet::addSuccess('Picture uploaded successfully.');
        }
	break;
	case 'edit':
        if( AlertSet::$success )
        {
            $pictureController->update();
        }
	break;
	case 'delete':
        if( AlertSet::$success )
        {
            $pictureController->delete();
        }
	break;
    case 'add_new_characters_to_picture':

        if( !isset($_POST['pictures_id']) )
            AlertSet::addError('Picture must be specified');

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
                        'id'=>@$_POST['pictures_id']
                    );
                    $controller = new PictureController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'pictures_to_characters',
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
    case 'add_characters_to_picture':

        if( !isset($_POST['pictures_id']) )
            AlertSet::addError('Picture must be specified');



        if( AlertSet::$success )
        {
            if( count(@$_POST['nodes']) > 0 )
            {
                $properties = array();
                foreach($_POST['nodes'] as $node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['pictures_id']
                    );
                    $controller = new PictureController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'pictures_to_characters',
                        'characters_id',
                        $node['characters_id']
                    );
                }
            }
        }
        break;
    case 'add_new_settings_to_picture':

        if( !isset($_POST['pictures_id']) )
            AlertSet::addError('Picture must be specified');

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
                        'id'=>@$_POST['pictures_id']
                    );
                    $controller = new PictureController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'pictures_to_settings',
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
    case 'add_settings_to_picture':

        if( !isset($_POST['pictures_id']) )
            AlertSet::addError('Picture must be specified');



        if( AlertSet::$success )
        {
            if( count(@$_POST['nodes']) > 0 )
            {
                $properties = array();
                foreach($_POST['nodes'] as $node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['pictures_id']
                    );
                    $controller = new PictureController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'pictures_to_settings',
                        'settings_id',
                        $node['settings_id']
                    );
                }
            }
        }
        break;

    case 'add_new_plot_events_to_picture':

        if( !isset($_POST['pictures_id']) )
            AlertSet::addError('Picture must be specified');

        if( AlertSet::$success && count(@$_POST['plot_event_names']) > 0 )
        {
            $properties = array();
            $new_nodes = array();
            $plotEventController = new PlotEventController($properties, $currentUser, 'plot');
            foreach($_POST['plot_event_names'] as $plot_event_name)
            {
                if( $plot_event_name == '' )
                    continue;

                $properties = array(
                    'event'=>$plot_event_name,
                    'summary'=>'',
                    'outline'=>'',
                    'story'=>array('id'=>$currentStory->get_id()),
                    'series_id'=>$currentStory->get_series()->get_id(),
                    'users_id'=>$currentUser->id
                );

                $plotEventController->setTobject($properties);
                $plotEventController->setCheckXSRF(false);

                if( $plotEventController->add() )
                {
                    $plotEventController->addToStory();
                    $new_nodes_index = count($new_nodes);

                    $new_nodes[$new_nodes_index]['plot_events_id'] = $plotEventController->get_tobject()->get_id();
                    $new_nodes[$new_nodes_index]['plot_events_name'] = $plotEventController->get_tobject()->get_event();
                }
                else
                {
                    AlertSet::addError('Could not add Plot Event');
                }
            }

            if( is_array($new_nodes) && count($new_nodes) > 0 )
            {

                foreach($new_nodes as $i=>$node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['pictures_id']
                    );
                    $controller = new PictureController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'pictures_to_plot_events',
                        'plot_events_id',
                        $node['plot_events_id']
                    );
                }
            }
            else
            {
                Console::add('No new nodes detected');
            }
        }
        break;
    case 'add_plot_events_to_picture':

        if( !isset($_POST['pictures_id']) )
            AlertSet::addError('Picture must be specified');

        if( AlertSet::$success )
        {
            if( count(@$_POST['nodes']) > 0 )
            {
                $properties = array();
                foreach($_POST['nodes'] as $node)
                {
                    $properties = array
                    (
                        'id'=>@$_POST['pictures_id']
                    );
                    $controller = new PictureController($properties, $currentUser, $module);

                    $controller->addRelationship(
                        'pictures_to_plot_events',
                        'plot_events_id',
                        $node['plot_events_id']
                    );
                }
            }
        }
        break;
    case 'remove-tag':
        switch( $_POST['object_type'] )
        {
            case 'characters':
                $pictureController->removeRelationship(
                    'pictures_to_characters',
                    'characters_id',
                    $_POST['characters_id']
                );
                break;
            case 'settings':
                $pictureController->removeRelationship(
                    'pictures_to_settings',
                    'settings_id',
                    $_POST['settings_id']
                );
                break;
            case 'plot_events':
                $pictureController->removeRelationship(
                    'pictures_to_plot_events',
                    'plot_events_id',
                    $_POST['plot_events_id']
                );
                break;
        }
        break;
    case 'update-photo-priority':
        $order = NULL;
        if( isset($_POST['characters']) )
        {
            $order = $_POST['characters'];
            $table_name = 'pictures_to_characters';
            $column_name = 'characters_id';
        }
        else if( isset($_POST['settings']) )
        {
            $order = $_POST['settings'];
            $table_name = 'pictures_to_settings';
            $column_name = 'settings_id';
        }
        else if( isset($_POST['plot']) )
        {
            $order = $_POST['plot'];
            $table_name = 'pictures_to_plot_events';
            $column_name = 'plot_events_id';
        }

        if( $order != NULL )
        {
            if( Pictures::sortByTag($order, $table_name, $column_name, intval($_POST['objects_id'])) )
            {
                AlertSet::addSuccess('Pictures sorted successfully.');
            }
            else
            {
                AlertSet::addError('An error occurred attempting to sort your photos.');
            }
        }
        else
        {
            AlertSet::addError('An error occurred. Please Try Again. ');
        }

        break;

    case 'make-cover-photo':
        switch( $_POST['object_type'] )
        {
            case 'characters':
                $pictureController->makeCoverPhoto(
                    'pictures_to_characters',
                    'characters_id',
                    $_POST['objects_id']
                );
                break;
            case 'settings':
                $pictureController->makeCoverPhoto(
                    'pictures_to_settings',
                    'settings_id',
                    $_POST['objects_id']
                );
                break;
            case 'plot':
                $pictureController->makeCoverPhoto(
                    'pictures_to_plot_events',
                    'plot_events_id',
                    $_POST['objects_id']
                );
                break;
        }

        break;
    default:
		AlertSet::AddError('Verb not recognized: ' . @$_POST['verb']);
	break;
}

$json['success'] = AlertSet::$success;
$json['alerts'] = AlertSet::$alerts;
echo json_encode($json);


<?php

include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');;

$verb='load';
if(!empty($_GET['stories_id']))
{
    $loader = new Stories();
    $story=$loader->loadById($_GET['stories_id']);
}

if( is_object($story) )
{
    $_SESSION['currentStory'] = $story->get_id();

    $location = '';
    if( (!isset($_GET['module']) || $_GET['module'] == 'undefined') || $_GET['module'] == 'stories')
        $location = '/stories/view/'.$story->get_id();
    else if ( !isset($_GET['action']) || $_GET['action'] == 'undefined' || trim($_GET['action']) == '' )
        $location = '/'.addslashes($_GET['module']).'/list/';
    else
        $location = '/'.addslashes($_GET['module']).'/'.addslashes($_GET['action']).'/';

    header('Location: ' . $location );
    exit();
}
else
{
    unset($currentStory);
    unset($_SESSION['currentStory']);

    header('Location: /stories/add/');
    exit();
}


?>
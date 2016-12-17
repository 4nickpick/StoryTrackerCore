<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='edit';
if(!empty($_GET['users_id']))
{
	$usersManager = new Users();
	$user=$usersManager->loadById($_GET['users_id']);
}

if($user)
	include 'form.php';
else
	echo 'The user you selected was not found in the database. They may have been deleted.';
?>
<?php
if(empty($currentUser))
{
	AlertSet::addError('You have been logged out. Please log in again.');
	AlertSet::save();
    header('HTTP/1.1 403 Not Logged In');
	header('Location: /');
	exit();
}
else if(!empty($tab) && !$currentUser->tabPermission($module, $tab))
{
	echo 'You do not have permission to view this page.';
	exit();
}
?>
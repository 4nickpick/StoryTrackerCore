<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='edit';
if(!empty($_GET['settings_id']))
{
	$settingsManager = new Settings();
	$setting=$settingsManager->loadById($_GET['settings_id']);
}
?>

<?php TemplateSet::begin('body') ?>
	<?php
	if(!$setting)
		echo 'The setting you selected was not found in the database. They may have been deleted.';
	else if( $setting->get_story()->get_id() != $currentStory->get_id() ||
        $setting->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to edit this setting.';
	else
		include 'form.php';
	?>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
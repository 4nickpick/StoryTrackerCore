<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='edit';
if(!empty($_GET['characters_id']))
{
	$charactersManager = new Characters();
	$character=$charactersManager->loadById($_GET['characters_id'], $currentStory->get_id());
}
?>

<?php TemplateSet::begin('body') ?>
	<?php
	if(!$character)
		echo 'The character you selected was not found in the database. They may have been deleted.';
	else if( $character->get_story()->get_id() != $currentStory->get_id() ||
        $character->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to edit this character.';
	else
		include 'form.php';
	?>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
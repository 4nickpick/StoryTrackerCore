<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='edit';
if(!empty($_GET['stories_id']))
{
	$loader = new Stories();
	$story=$loader->loadById($_GET['stories_id']);
}
?>

<?php TemplateSet::begin('body') ?>
	<?php
	if(!$story)
		echo 'The story you selected was not found in the database. They may have been deleted.';
	else if( $story->get_series()->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to edit this story.';
	else
		include 'form.php';
	?>
<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
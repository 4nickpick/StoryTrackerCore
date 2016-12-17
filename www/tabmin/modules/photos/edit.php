<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='edit';
if(!empty($_GET['photos_id']))
{
	$picturesManager = new Pictures();
	$picture=$picturesManager->loadById($_GET['photos_id']);
}

?>

<?php TemplateSet::begin('body') ?>
	<?php
	if(!$picture)
		echo 'The photo you selected was not found. It may have been deleted.';
	else if( $picture->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to edit this photo.';
	else
		include 'form.php';
	?>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
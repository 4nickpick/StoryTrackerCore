<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='edit';
if(!empty($_GET['plot_id']))
{
	$eventsManager = new PlotEvents();
	$plot_event=$eventsManager->loadById($_GET['plot_id']);
}
?>

<?php TemplateSet::begin('body') ?>
	<?php
	if(!$plot_event)
		echo 'The plot event you selected was not found in the database. They may have been deleted.';
	else if( $plot_event->get_story()->get_id() != $currentStory->get_id() ||
        $plot_event->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to edit this plot event.';
	else
		include 'form.php';
	?>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
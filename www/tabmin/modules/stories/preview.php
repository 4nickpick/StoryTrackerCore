<?php
global $currentUser;

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();

$verb='preview';
?>

<?php TemplateSet::begin('scripts'); ?>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
<?php
    $loader = new Stories();
    $stories = $loader->loadByCurrentUser($currentUser->get_id());

	if(count($stories) > 0)
    {
        //redirect to Stories add screen
        header('Location: /stories/add/');
        exit();
    }
	else
	{
		?>
        <div class="preview-message">
            <h2>Welcome to Story Tracker!</h2>
            <h4>First things first, you should <a href="/stories/add/">add your first story.</a></h4>
        </div>
		<?php
	}
?>
<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
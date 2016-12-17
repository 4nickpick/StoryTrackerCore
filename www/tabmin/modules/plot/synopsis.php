<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start()

;$verb='view';
?>

<?php TemplateSet::begin('scripts'); ?>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
<?php
if(!$currentStory)
    echo 'The plot event you selected was not found in the database. They may have been deleted.';
else if( $currentStory->get_users_id() != $currentUser->get_id() )
    echo 'You do not have permission to view this plot event.';
else
{
    ?>
    <div id="view">

        <div id="synopsis">
            <h2>Synopsis
                <small><a href="/stories/edit/<?=$currentStory->get_id()?>?synopsis">edit</a></small>
            </h2>
            <?php
                if( strlen($currentStory->get_synopsis()) > 0 )
                    ($currentStory->get_synopsis());
                else
                    include('preview-synopsis.php');
            ?>
        </div>

    </div><!-- view -->
<?php
}
?>
<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
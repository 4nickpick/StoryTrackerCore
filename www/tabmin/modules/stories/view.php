<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start()

;$verb='view';
if(!empty($_GET['stories_id']))
{
	$loader = new Stories();
	$story=$loader->loadById($_GET['stories_id']);

    $pictures = NULL;
    if( $story )
    {
        $picturesManager = new Pictures();
        $pictures=$picturesManager->loadByStory($story->get_id());
    }
}
?>

<?php TemplateSet::begin('scripts'); ?>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
<?php
	if(!$story)
		echo 'The story you selected was not found in the database. They may have been deleted.';
	else if( $story->get_series()->get_users_id() !== $currentUser->get_id() )
    {
        echo 'You do not have permission to view this story.';
    }
	else
	{
		?>
        <div id="view" class="story_view">
            <table class="story_table">
                <tr id="header">
                    <th colspan="2">
                        <div>
                            <span><?php echo htmlentitiesUTF8($story->get_name()) . ' '; ?></span>
                            <div class="actions">
                                <a href="/stories/edit/<?=$story->get_id()?>"><img src="/tabmin/icons/edit.png"/></a>
                                <?php
                                if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$story->get_series()->get_users_id()))
                                {
                                    ?>
                                    <form id="delete_form" action="/stories/ajax/" method="post" onsubmit="return false;">
                                        <input type="hidden" name="verb" value="delete" />
                                        <input type="hidden" name="stories_id" value="<?php echo $story->get_id() ?>" />
                                        <?php echo XSRF::html() ?>
                                        <a href="javascript:" onclick="AlertSet.confirm('Are you sure you want to delete <?php echo addslashes($story->get_name())?>?', function(){throb(); handleAjaxForm(document.getElementById('delete_form'), function(resp){ AlertSet.redirectWithAlert('/stories/list/', resp)}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;"">
                                        <img src="/tabmin/icons/delete.png" />
                                        </a>
                                    </form>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </th>

                </tr>
                <?php
                if( isset($pictures) && count($pictures) > 0 )
                {
                    ?>
                    <tr id="image">
                        <td colspan="4">
                            <div class="photo-slider photo-slider-large">
                                <span class="prev"> <a href="javascript:" onclick="slideSwitch(false);">&laquo;</a> </span>
                                <?php
                                    foreach( $pictures as $i=>$picture )
                                    {
                                        $class = 'active-slide';
                                        if( $i != 0 )
                                            $class="inactive-slide";
                                        ?>
                                        <a id="photo-thumbnail<?=$picture->get_id()?>" data-picture_id="<?=$picture->get_id()?>" class="photo-thumbnail fancybox <?=$class?> slide" rel="group" href="/show-picture.php?pictures_id=<?=@$picture->get_id()?>">
                                        </a>
                                    <?php
                                    }
                                ?>
                                <span class="next"> <a href="javascript:" onclick="slideSwitch(true);">&raquo;</a> </span>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>

        </div><!-- view -->


        <div id="story_profile_menu">
            <div class="story_button"><a href="/characters/list/">Characters</a></div>
            <div class="story_button"><a href="/settings/list/">Settings</a></div>
            <div class="story_button"><a href="/plot/list/">Plot</a></div>
            <div class="story_button"><a href="/inspiration/list/">Inspiration</a></div>
        </div>

        <div id="content" class="story_content">

            <div id="description" class="content_section">
                <h2>Description</h2>
                <?
                    if( strlen($story->get_description()) > 0 )
                        echo ($story->get_description());
                    else
                        echo '<em>No description has been added for this story.</em>';
                ?>
            </div>

            <div id="outline" class="content_section">
                <h2>Plot Synopsis</h2>
                <?
                    if( strlen($story->get_synopsis()) > 0 )
                        echo ($story->get_synopsis());
                    else
                        echo '<em>No synopsis has been added for this story.</em>';
                ?>
            </div>

            <div id="created" class="content_section">
                <h2>Created on </h2>
                <?
                    if( strlen($story->get_created()) > 0 )
                        echo '<em>'.htmlentitiesUTF8($story->get_created()).'</em>';
                ?>
            </div>
        </div>
    <?php
	}
?>
<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
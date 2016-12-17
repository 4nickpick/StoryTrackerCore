<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start()

;$verb='view';
if(!empty($_GET['plot_id']))
{
	$plot_events = new PlotEvents();
    $plot_event=$plot_events->loadById($_GET['plot_id']);

    $pictures = NULL;
    if( $plot_event )
    {
        $picturesManager = new Pictures();
        $pictures=$picturesManager->loadByTags(
            '',
            $currentUser->get_id(),
            NULL,
            NULL,
            array($plot_event->get_id()),
            'pictures_to_plot_events.cover_photo DESC, pictures_to_plot_events.priority'
        );
    }
}
?>

<?php TemplateSet::begin('scripts'); ?>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
<?php
	if(!$plot_event)
		echo 'The plot event you selected was not found in the database. They may have been deleted.';
	else if( $plot_event->get_story()->get_id() != $currentStory->get_id() ||
        $plot_event->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to view this plot event.';
	else
	{
		?>
			<div id="view">
				<table class="view_table">
					<tr id="header">
						<th colspan="2">
                            <div>
                                <span><?php echo htmlentitiesUTF8($plot_event->get_event()) . ' '; ?></span>
                                <div class="actions">
                                    <a href="/plot/edit/<?=$plot_event->get_id()?>"><img src="/tabmin/icons/edit.png"/></a>

                                    <a href="/plot/edit/<?=$plot_event->get_id()?>/?gallery"><img src="/tabmin/icons/gallery.png"/></a>

                                    <?php
                                    if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$plot_event->get_series_id()))
                                    {
                                        ?>
                                        <form id="delete_form" action="/plot/ajax/" method="post" onsubmit="return false;">
                                            <input type="hidden" name="verb" value="delete" />
                                            <input type="hidden" name="plot_events_id" value="<?php echo $plot_event->get_id() ?>" />
                                            <?php echo XSRF::html() ?>
                                            <a href="javascript:" onclick="AlertSet.confirm('Are you sure you want to delete <?php echo addslashes($plot_event->get_event())?>?', function(){throb(); handleAjaxForm(document.getElementById('delete_form'), function(resp){ AlertSet.redirectWithAlert('/plot/list/', resp)}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;"">
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
                            <td colspan="3">
                                <div class="photo-slider">
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
                                </div></td>
                        </tr>
                        <?php
                        }
                    ?>

                    <?php
                    $characters = $plot_event->get_characters();
                    if( count($characters) > 0 )
                    {
                        ?>
                        <tr>
                            <th class="label">
                                Characters
                            </th>
                            <td colspan="2" class="data">
                                <?php
                                foreach($characters as $i=>$character)
                                {
                                    ?>
                                    <a href="/characters/view/<?=$character->get_id()?>">
                                        <?= htmlentitiesUTF8($character->get_full_name()) ?>
                                    </a>
                                    <?php
                                    if( $i < count($characters) - 1 )
                                        echo ',';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>

                    <?php
                    $settings = $plot_event->get_settings();
                    if( count($settings) > 0 )
                    {
                        ?>
                        <tr>
                            <th class="label">
                                Settings
                            </th>
                            <td colspan="2" class="data">
                                <?php
                                foreach($settings as $i=>$setting)
                                {
                                    ?>
                                    <a href="/settings/view/<?=$setting->get_id()?>">
                                        <?= htmlentitiesUTF8($setting->get_full_name()) ?>
                                    </a>
                                    <?php
                                    if( $i < count($settings) - 1 )
                                        echo ',';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
				</table>

                <div id="content">

                    <div id="summary" class="content_section">
                        <h1>Summary</h1>
                        <?
                        if( strlen($plot_event->get_summary()) > 0 )
                            echo ($plot_event->get_summary());
                        else
                            echo '<em>No summary has been added for this plot event.</em>';
                        ?>
                    </div>
                    
                </div>

			</div><!-- view -->
		<?php
	}
?>
<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
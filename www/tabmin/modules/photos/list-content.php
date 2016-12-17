<?php

global $currentStory;

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
switch(@$_GET['sort'])
{
    default:
        $sort='pictures.sort_order ';
        break;
}

$picturesManager = new Pictures();

if( isset($_GET['untagged_only']) )
{
    $pictures=$picturesManager->loadByUntagged(
        @$_GET['s'],
        $currentUser->get_id()
    );
}
else
{
    $pictures=$picturesManager->loadByTags(
        @$_GET['s'],
        $currentUser->get_id(),
        @$_GET['character-tags'],
        @$_GET['setting-tags'],
        @$_GET['plot-event-tags'],
        'pictures_to_characters.characters_id, pictures_to_settings.settings_id,
        pictures_to_plot_events.plot_events_id, pictures.id DESC'
    );
}


$total_records=$picturesManager->getFoundRows();
if($total_records > 0)
{
    ?>
    <div id="photo_counter">
        Displaying <span id="picture_count"><?php echo count($pictures)?></span> Pictures(s)...
        <?php include('add-button.php') ?>
    </div>

    <div id="photos">
        <?php
        if( isset($pictures) && count($pictures) > 0 )
        {
            foreach($pictures as $picture)
            {
                ?>
                <div class="photo-wrapper"
                     data-picture_id="<?=$picture->get_id()?>"
                     data-character_tags="<?=$picture->get_characters_ids()?>"
                     data-setting_tags="<?=$picture->get_settings_ids()?>"
                     data-plot_event_tags="<?=$picture->get_plot_events_ids()?>">

                    <div class="photo-menu-wrapper">
                        <a class="photo-menu-button" href="javascript:" onclick="togglePhotoMenu('<?=$picture->get_id()?>');">
                            <img src="/images/ui/select_arrow.png" />
                        </a>
                        <div id="photo-menu-<?=$picture->get_id()?>" class="photo-menu">
                            <ul>
                                <li onclick="editPhoto('<?=$picture->get_id()?>');">
                                    <span>Edit Caption/Tags</span>
                                </li>
                                <li onclick="downloadPhoto('<?=$picture->get_id()?>');">
                                    <span>Download</span>
                                </li>
                                <li onclick="deletePhoto('<?=$picture->get_id()?>');">
                                    <span class="delete">Delete</span>
                                </li>
                            </ul>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <div class="caption">
                        <div><?=$picture->get_caption()?></div>
                    </div>

                    <a id="photo-thumbnail<?=$picture->get_id()?>" data-picture_id="<?=$picture->get_id()?>" class="fancybox photo-thumbnail" rel="group" href="/show-picture.php?pictures_id=<? echo $picture->get_id(); ?>">

                    </a>

                </div>
                <?php
            }
        }
        ?>
        <div class="clear"></div>
    </div>

    <?php
}
else if ( !empty($_GET['s']) )
{
    echo '<br /><strong>No Picture Captions matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".';
    include('add-button.php');
}
else
{
    echo '<br /><strong>There are currently no Pictures in "' . htmlentitiesUTF8($currentStory->get_name()) . '".</strong>&nbsp;';
    include('add-button.php');
}
?>
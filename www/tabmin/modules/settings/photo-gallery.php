<?php

$picturesManager = new Pictures();
$pictures=$picturesManager->loadByTags(
    @$_GET['s'],
    $currentUser->get_id(),
    NULL,
    array($setting->get_id()),
    NULL,
    'pictures_to_settings.priority'
);

$cover_photo_id = $picturesManager->getCoverPhotoByTag('pictures_to_settings', 'settings_id', $setting->get_id());
$total_records=$picturesManager->getFoundRows();
if($total_records > 0)
{
?>
<div id="photo_counter">
    Displaying <?php echo count($pictures)?> Pictures(s)...
    <?php include('../photos/add-button.php') ?>
</div>

<div id="photos">
    <?php
    if( isset($pictures) && count($pictures) > 0 )
    {
        foreach($pictures as $picture)
        {
            $is_cover_photo = ($cover_photo_id == $picture->get_id());
            ?>
            <div id="settings_<?=$picture->get_id()?>" class="photo-wrapper <?= $is_cover_photo ? 'cover_photo' : ''?>">

                <div class="photo-menu-wrapper">
                    <a class="photo-menu-button" href="javascript:" onclick="togglePhotoMenu('<?=$picture->get_id()?>');">
                        <img src="/images/ui/select_arrow.png" />
                    </a>

                    <div id="photo-menu-<?=$picture->get_id()?>" class="photo-menu">
                        <ul>
                            <li onclick="editPhoto('<?=$picture->get_id()?>');">
                                <span>Edit Caption/Tags</span>
                            </li>
                            <li onclick="makeCoverPhoto('<?=$picture->get_id()?>', '<?=$setting->get_id()?>', 'settings');">
                                <span>Set as Cover Photo</span>
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

                <?php
                if( $is_cover_photo )
                {
                    ?><span id="cover_photo_icon" title="Cover Photo"><img src="/tabmin/icons/accept.png" /></span><?php
                }
                ?>

                <div class="caption">
                    <div><?=$picture->get_caption()?></div>
                </div>


                <a id="photo-thumbnail<?=$picture->get_id()?>" data-picture_id="<?=$picture->get_id()?>" class="photo-thumbnail fancybox <?=$class?> slide" rel="group" href="/show-picture.php?pictures_id=<?=@$picture->get_id()?>">
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
else
{
    echo '<p>There are currently no photos.</p>';
    include('../photos/add-button.php');
}
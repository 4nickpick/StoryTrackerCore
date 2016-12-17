<?php
?>

<div id="add_photo_wrapper">
    <a href="javascript:" onclick="toggleAddPhotoMenu();">Add Photos</a>
    <div id="add_photo_menu">
        <ul>
            <li onclick="addPhotosFromComputer();">
                <img src="/tabmin/icons/computer.png" />
                <span>From Your Computer</span>
            </li>
            <li onclick="addPhotosFromInternet();">
                <img src="/tabmin/icons/world.png" />
                <span>From The Internet</span>
            </li>
        </ul>
    </div>
    <div class="clear"></div>
</div>
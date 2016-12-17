<?php

// Photos - Add From Computer Form

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<p id="instructions">Paste a link to an image on the internet below...</p>

<form action="<?=$module->path?>/ajax.php" method="post" onsubmit="return addPhotosFromInternetSubmit(this)">
    <input type="hidden" name="verb" value="add-from-internet" />

    Link: <input type="text" name="image_link" /><br /><br />
    <input type="submit" value="Upload Image From Internet"/>
</form>

<div id="results"></div>
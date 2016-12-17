<?php

// Photos - Add From Computer Form

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
    if( isset($character) )
    {
        ?><p>You are adding photos to <strong><?=htmlentitiesUTF8($character->get_full_name())?></strong>.</p><?php
    }
?>

<p id="instructions">Select files from your computer...</p>

<p>Note: only JPEG and PNG files are currently supported.</p>

<input id="fileupload" type="file" name="files[]" accept=".png, .jpg, .jpeg" data-url="/tabmin/modules/photos/ajax.php" multiple>

<div id="progress">
    <div class="bar" style="width: 1%;"></div>
</div>

<div id="results"></div>
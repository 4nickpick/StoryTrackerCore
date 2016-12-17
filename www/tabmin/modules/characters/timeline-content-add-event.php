<?php

// Relationship Charts - Add Nodes Form

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<form id="eventAdd" action="/characters/ajax/" method="post" style="display:inline;">
        <input type="hidden" name="verb" value="event_add" />
        <input type="hidden" name="characters_id" value="<?=$character->get_id()?>" />
        <?php echo XSRF::html() ?>

        <label for="event_name">
            Event Name:
        </label>

        <input type="text" name="event_name" value="" class="character-to-add"/><br />

        <br />
        <label for="event_time">
            Event Time:
        </label>

        <input type="text" name="event_time" value="" /><br />
</form>

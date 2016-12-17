<?php

// Relationship Charts - Add Nodes Form

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
$charactersManager = new Characters();
$parameters = array();
$parameters['characters.series_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_series()->get_id());
$parameters['characters_to_stories.stories_id']=array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_id());
$characters = $charactersManager->loadByParameters($parameters);

?>
<strong>Add New Characters</strong>
<form>
    <fieldset id="add-new-form">
        <a class="add-new" href="javascript:" onclick="addNewObjectInQuickForm('character')">Add New</a><br />
    </fieldset>
</form>

<br />
<strong>Add Existing Characters</strong>
<form>
    <fieldset>
        <?php
        foreach($characters as $character)
        {
            ?>
            <a class="add" href="javascript:" onclick="toggleObject('character', this, '<?=$character->get_id()?>');">Add</a>
            <input type="checkbox" name="<?=$character->get_id()?>" id="character-check_<?=$character->get_id()?>"
                   class="checkbox ui-widget-content ui-corner-all character-to-add"
                   data-characters-name="<?=htmlentitiesUTF8($character->get_full_name())?>"/>
            <label for="character-check_<?=$character->get_id()?>"><?=htmlentitiesUTF8($character->get_full_name())?></label><br />
        <?php
        }
        ?>
    </fieldset>
</form>
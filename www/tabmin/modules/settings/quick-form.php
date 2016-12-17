<?php

// Relationship Charts - Add Nodes Form

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
$manager = new Settings();
$parameters = array();
$parameters['settings.series_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_series()->get_id());
$settings = $manager->loadByParameters($parameters);
?>
<strong>Add New Settings</strong>
<form>
    <fieldset id="add-new-form">
        <a class="add-new" href="javascript:" onclick="addNewObjectInQuickForm('setting')">Add New</a><br />
    </fieldset>
</form>

<br />
<strong>Add Existing Settings</strong>
<form>
    <fieldset>
        <?php
        foreach($settings as $setting)
        {
            ?>
            <a class="add" href="javascript:" onclick="toggleObject('setting', this, '<?=$setting->get_id()?>');">Add</a>
            <input type="checkbox" name="<?=$setting->get_id()?>" id="setting-check_<?=$setting->get_id()?>"
                   class="checkbox ui-widget-content ui-corner-all setting-to-add"
                   data-characters-name="<?=htmlentitiesUTF8($setting->get_full_name())?>"/>
            <label for="setting-check_<?=$setting->get_id()?>"><?=htmlentitiesUTF8($setting->get_full_name())?></label><br />
        <?php
        }
        ?>
    </fieldset>
</form>
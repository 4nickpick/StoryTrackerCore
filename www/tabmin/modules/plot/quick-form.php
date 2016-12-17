<?php

// Relationship Charts - Add Nodes Form

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
$manager = new PlotEvents();
$parameters = array();
$parameters['stories_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_id());
$plot_events = $manager->loadByParameters($parameters);
?>
<strong>Add New Plot Events</strong>
<form>
    <fieldset id="add-new-form">
        <a class="add-new" href="javascript:" onclick="addNewObjectInQuickForm('plot-event')">Add New</a><br />
    </fieldset>
</form>

<br />
<strong>Add Existing Plot Events</strong>
<form>
    <fieldset>
        <?php
        foreach($plot_events as $plot_event)
        {
            ?>
            <a class="add" href="javascript:" onclick="toggleObject('plot-event', this, '<?=$plot_event->get_id()?>');">Add</a>
            <input type="checkbox" name="<?=$plot_event->get_id()?>" id="plot-event-check_<?=$plot_event->get_id()?>"
                   class="checkbox ui-widget-content ui-corner-all plot-event-to-add"
                   data-characters-name="<?=htmlentitiesUTF8($plot_event->get_event())?>"/>
            <label for="plot-event-check_<?=$plot_event->get_id()?>"><?=htmlentitiesUTF8($plot_event->get_event())?></label><br />
        <?php
        }
        ?>
    </fieldset>
</form>

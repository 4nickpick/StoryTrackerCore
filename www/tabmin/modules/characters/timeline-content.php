<?php

global $currentStory;

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
switch(@$_GET['sort'])
{
	case 'full_name':
		$sort='characters_events.description ';
	break;
	case 'priority':
	default:
		$sort='characters_events.priority ';
	break;
}

$character = null;
if(!empty($_GET['characters_id']))
{
    $charactersManager = new Characters();
    $character=$charactersManager->loadById($_GET['characters_id'], $currentStory->get_id());
}

$eventsManager = new CharacterEvents();
$events = null;
if( $character )
{
    $parameters['characters_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$character->get_id());
    $events = $eventsManager->searchLoadByParameters(@$_GET['s'], $parameters, 'characters_events.priority');
}

if(!$character)
{
    echo 'The character you selected was not found in the database. They may have been deleted.';
    die();
}
else if( $character->get_story()->get_id() != $currentStory->get_id() ||
    $character->get_users_id() !== $currentUser->get_id() )
    echo 'You do not have permission to edit this character.';

//var_dump($characters);
$total_records=$eventsManager->getFoundRows();
if($total_records > 0)
{
	?>
	<table <?php echo @$_GET['sort'] != 'full_name' ? 'id="sortable"' : '' ?> class="info_table list_table">
		<thead>
			<tr>
				<th colspan="7" align="left">
				
					<!--<div style="float:right">
						Sort By : 
							<a <?php echo (@$_GET['sort'] == 'full_name') ? 'class="disabled"' : '' ?> href="javascript:;" onclick="document.getElementById('sort').value='full_name'; listUpdateContent(); ">Alphabetical</a> | 
							<a <?php echo (@$_GET['sort'] != 'full_name' || !isset($_GET['sort'])) ? 'class="disabled"' : '' ?> href="javascript:;" onclick="document.getElementById('sort').value='priority'; listUpdateContent(); ">Priority</a>
						<input type="hidden" name="sort" id="sort" />
					</div>-->

                    Displaying <?php echo count($events)?> Event(s) in <a href="/characters/view/<?=htmlentitiesUTF8($character->get_id())?>"><?=htmlentitiesUTF8($character->get_full_name())?></a>'s Timeline...
                        <a href="javascript:" onclick="addTimelineEventForm(<?php echo $character->get_id()?>);">Add an Event to this Timeline</a>
				</th>
			</tr>
			<tr>
				<th>
					Name
				</th>
				<th>
					Time
				</th>
				<th width="80">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($events as $i=>$event)
			{
				$class='light';
				if($i%2==0)
					$class='dark';
				?>
                <tr id="events_<?php echo $event->get_id()?>" class="<?php echo $class ?>">
					<td>
                            <span class="view"><?php Printer::printString ( $event->get_description() ); ?></span>
                            <span class="edit">
                                <input class="event_new_name_input" type="text" placeholder="Change Event Name Here"
                                        value="<?php Printer::printString ( $event->get_description() ); ?>"
                                        onkeyup="document.getElementById('event_new_name_<?=$event->get_id()?>').value = this.value;"
                                        />
                                <?php echo XSRF::html() ?>
                            </span>
                    </td>
                    <td>
                        <form id="eventEdit_<?php echo ( $event->get_id() ); ?>" action="/characters/ajax/" method="post" onsubmit="return false;" class="event_edit">
                            <input type="hidden" name="verb" value="event_edit" />
                            <input type="hidden" name="characters_events_id" value="<?php Printer::printString ( $event->get_id() ); ?>" />
                            <span class="edit">
                                <input id="event_new_name_<?=$event->get_id()?>" type="hidden" name="new_name" value="<?php Printer::printString ( $event->get_description() ); ?>" />
                            </span>
                        <span class="view"><?php Printer::printString ( $event->get_time() ); ?></span>
                            <span class="edit">
                                <input type="text" name="new_time" placeholder="Change Time Here" value="<?php Printer::printString ( $event->get_time() ); ?>" />
                                <input type="button" onclick="eventEdit(document.getElementById('eventEdit_<?php echo ( $event->get_id() ); ?>'))" value="Save" />
                                <input type="button" onclick="eventHideEdit('<?php echo $event->get_id() ?>')" value="Cancel" />
                                <?php echo XSRF::html() ?>
                            </span>
                        </form>
					</td>
					<td>
                        <?php if (@$_GET['sort'] != 'full_name' ? 'id="sortable"' : '') : ?>
						<div class="sort action_button" title="Sort Event">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$character->get_users_id()))
							{
								?>
								<img src="/tabmin/icons/sort.png" style="cursor:pointer;"/>
                                <!--<a href="/characters/view/<?php echo $character->get_id()?>"><img src="/tabmin/icons/view.png" /></a>-->
								<?php
							}
							?>
						</div>
                        <?php endif; ?>
						<div class="action_button" title="Edit Event">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$character->get_users_id()))
							{
								?>
								<a href="javascript:"><img src="/tabmin/icons/edit.png"
                                        onclick="eventShowEdit(<?=$event->get_id()?>);"/></a>
								<?php
							}
							?>
						</div>
						<div class="action_button" title="Delete Event">
							<?php
							if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$character->get_users_id()))
							{
								?>
								<form id="eventDelete_<?=$event->get_id()?>" action="/characters/ajax/" method="post"
                                      onsubmit="AlertSet.confirm('Are you sure you want to delete this event?', function(){ handleAjaxForm(this, function(resp){timelineListUpdateContent(resp.characters_id)}, function(resp){console.log('delete failed.');AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;" >
									<input type="hidden" name="verb" value="event_delete" />
									<input type="hidden" name="characters_events_id" value="<?php echo $event->get_id() ?>" />
									<?php echo XSRF::html() ?>
									<input type="image" src="/tabmin/icons/delete.png" />
								</form>
								<?php
							}
							?>
						</div>
						
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<?php
}
else if ( isset($_GET['s']) )
{
	echo '<br /><strong>No Events matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".</strong>';
    ?>
    <a href="javascript:" onclick="addTimelineEventForm(<?php echo $character->get_id()?>);">Add an Event to this Timeline</a>
    <?php
}
else
{
	echo '<br /><strong>There are currently no Events in ' . '<a href="/characters/view/' . htmlentitiesUTF8($character->get_id()) . '">' . htmlentitiesUTF8($character->get_full_name()) . '</a>\'s Timeline.</strong>&nbsp;';
    ?>
    <a href="javascript:" onclick="addTimelineEventForm(<?php echo $character->get_id()?>);">Add an Event to this Timeline</a>
    <?php
}
?>


<div id="add-event-dialog" title="Add Event" class="hidden-dialog">
    <?php include('timeline-content-add-event.php'); ?>
</div>
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
    default:
		$sort='characters.full_name ';
	break;
}

$charactersManager = new Characters();
	
$parameters = array();
$characters=$charactersManager->searchLoadBySeries(@$_GET['s'], $currentStory->get_series()->get_id(), $currentStory->get_id(), $sort, '');
//$characters = $charactersManager->loadAll();
//var_dump($characters);

$total_records=$charactersManager->getFoundRows();
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
					
					Displaying <?php echo count($characters)?> Character(s) from other stories in this series...
				</th>
			</tr>
			<tr>
				<th>
					Name
				</th>
                <th>
                    Stories
                </th>
				<th width="80">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($characters as $i=>$character)
			{
				$class='light';
				if($i%2==0)
					$class='dark';
				?>
				<tr id="characters-add-existing_<?php echo $character->get_id()?>" class="<?php echo $class ?>">
					<td>
						<?php 
						Printer::printString ( $character->get_full_name() );                     
						?>
					</td>
                    <td>
                        <em><?php
                        Printer::printString ( $character->get_stories() != '' ? $character->get_stories() : 'none' );
                        ?></em>
                    </td>
					<td>
                        <div class="action_button" title="Add Character to '<?=htmlentitiesUTF8($currentStory->get_name())?>'">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$character->get_users_id()))
							{
								?>

                                <form id="characters-add-existing-form_<?php echo $character->get_id()?>" action="/characters/ajax/" method="post" onsubmit="return addToCurrentStory(this);" >
                                    <input type="hidden" name="verb" value="add-to-story" />
                                    <input type="hidden" name="stories_id" value="<?php echo $currentStory->get_id() ?>" />
                                    <input type="hidden" name="characters_id" value="<?php echo $character->get_id() ?>" />
                                    <?php echo XSRF::html() ?>
                                    <input type="image" src="/tabmin/icons/add.png" />
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
	echo '<br /><strong>No Characters matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".</strong> <a href="/characters/add">Add a Character</a>';
}
else
{
	echo '<br /><strong>There are currently no other Characters in this series.</strong>&nbsp;<a href="/characters/add">Add a new Character</a>';
}
?>
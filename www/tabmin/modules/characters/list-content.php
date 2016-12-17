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
		$sort='characters.full_name ';
	break;
	case 'priority':
	default:
		$sort='characters_to_stories.priority ';
	break;
}

$charactersManager = new Characters();
	
$parameters = array();
$parameters['series.users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentUser->get_id());
$parameters['stories_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_id());
$characters=$charactersManager->searchLoadByParameters(@$_GET['s'], $parameters, $sort, '');
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
				
					<div style="float:right">
						Sort By : 
							<a <?php echo (@$_GET['sort'] == 'full_name') ? 'class="disabled"' : '' ?> href="javascript:;" onclick="document.getElementById('sort').value='full_name'; listUpdateContent(); ">Alphabetical</a> | 
							<a <?php echo (@$_GET['sort'] != 'full_name' || !isset($_GET['sort'])) ? 'class="disabled"' : '' ?> href="javascript:;" onclick="document.getElementById('sort').value='priority'; listUpdateContent(); ">Priority</a>
						<input type="hidden" name="sort" id="sort" />
					</div>
					
					Displaying <?php echo count($characters)?> Character(s)... <a href="/characters/add/">Add a Character</a>
				</th>
			</tr>
			<tr>
				<th>
					Name
				</th>
				<th width="100">Actions</th>
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
				<tr id="characters_<?php echo $character->get_id()?>" class="<?php echo $class ?>">
					<td>
						<?php 
						Printer::printString ( $character->get_full_name() );                     
						?>
					</td>
					<td>
                        <?php if (@$_GET['sort'] != 'full_name' ? 'id="sortable"' : '') : ?>
						<div class="sort action_button" title="Sort Character">
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
						<div class="action_button" title="Edit Character">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$character->get_users_id()))
							{
								?>
								<a href="/characters/edit/<?php echo $character->get_id()?>"><img src="/tabmin/icons/edit.png" /></a>
								<?php
							}
							?>
						</div>
                        <div class="action_button" title="Character's Timeline">
                            <?php
                            if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$character->get_users_id()))
                            {
                                ?>
                                <a href="/characters/timeline/<?php echo $character->get_id()?>"><img src="/tabmin/icons/timeline.png" /></a>
                                <?php
                            }
                            ?>
                        </div>
						<div class="action_button" title="Delete Character">
							<?php
							if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$character->get_users_id()))
							{
								?>
								<form action="/characters/ajax/" method="post" onsubmit="return deleteOrRemove(this);" >
									<input type="hidden" name="verb" value="delete" />
									<input type="hidden" name="stories_id" value="<?php echo $currentStory->get_id() ?>" />
									<input type="hidden" name="characters_id" value="<?php echo $character->get_id() ?>" />
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
        <tr class="spacer">
            <td colspan="2">
                <div>&nbsp;</div>
            </td>
        </tr>
		</tbody>
	</table>
	<?php
}
else if ( !empty($_GET['s']) )
{
	echo '<br /><strong>No Characters matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".</strong> <a href="/characters/add">Add a Character</a>';
}
else
{
	echo '<br /><strong>There are currently no Characters in "' . htmlentitiesUTF8($currentStory->get_name()) . '".</strong>&nbsp;<a href="/characters/add">Add a Character</a>';
}
?>
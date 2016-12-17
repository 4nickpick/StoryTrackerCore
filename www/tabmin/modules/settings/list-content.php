<?php

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
switch(@$_GET['sort'])
{
	case 'full_name':
		$sort='settings.full_name';
	break;
	case 'priority':
	default:
		$sort='settings_to_stories.priority ';
	break;
}

$loader = new Settings();
	
$parameters = array();
$parameters['users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentUser->get_id());
$parameters['stories_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_id());
$settings=$loader->searchLoadByParameters(@$_GET['s'], $parameters, $sort, '');
//$characters = $charactersManager->loadAll();
//var_dump($characters);

$total_records=$loader->getFoundRows();
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
					
					Displaying <?php echo count($settings)?> Settings... <a href="/settings/add/">Add a Setting</a>
				</th>
			</tr>
			<tr>
				<th>
					Name
				</th>
				<th width="80">Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($settings as $i=>$setting)
			{
				$class='light';
				if($i%2==0)
					$class='dark';
				?>
				<tr id="settings_<?php echo $setting->get_id()?>" class="<?php echo $class ?>">
					<td>
						<?php

                        $name = $setting->get_full_name();
                        echo htmlentitiesUTF8($name);

						?>
					</td>
					<td>
                        <?php if (@$_GET['sort'] != 'full_name' ? 'id="sortable"' : '') : ?>
                            <div class="sort action_button" title="Sort Setting">
                                <?php
                                if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$setting->get_users_id()))
                                {
                                    ?>
                                    <img src="/tabmin/icons/sort.png" style="cursor:pointer;"/>
                                    <!--<a href="/stories/view/<?php echo $setting->get_id()?>"><img src="/tabmin/icons/sort.png" /></a>-->
                                    <?php
                                }
                                ?>
                            </div>
                        <?php endif; ?>
						<div class="action_button" title="Edit Setting">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$setting->get_users_id()))
							{
								?>
								<a href="/settings/edit/<?php echo $setting->get_id()?>"><img src="/tabmin/icons/edit.png" /></a>
								<?php
							}
							?>
						</div>
						<div class="action_button" title="Delete Setting">
							<?php
							if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$setting->get_users_id()))
							{
								?>
								<form action="/settings/ajax/" method="post" onsubmit="AlertSet.confirm('Are you sure you want to delete <?php echo $setting->get_full_name()?>?', function(){handleAjaxForm(this, function(){window.location.reload();}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;">
									<input type="hidden" name="verb" value="delete" />
									<input type="hidden" name="settings_id" value="<?php echo $setting->get_id() ?>" />
                                    <input type="hidden" name="stories_id" value="<?php echo $currentStory->get_id() ?>" />
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
else if ( !empty($_GET['s']) )
{
	echo '<br /><strong>No Settings matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".</strong> <a href="/settings/add">Add a Setting</a>';
}
else
{
	echo '<br /><strong>There are currently no Settings in the database.</strong>&nbsp;<a href="/settings/add">Add a Setting</a>';
}
?>
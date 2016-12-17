<?php

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php

$parameters = NULL;
$charactersManager = new Characters();
	
$characterModel=$charactersManager->loadModel($currentStory->get_series()->get_id());
//$characters = $charactersManager->loadAll();
//var_dump($characters);

$groups = $characterModel->get_groups();
?>
<p>Character Models let you specify every detail that define your characters. </p>
<p>This model applies to every character in the current story (and stories in the same series). </p>
<table class="info_table model_table">
    <thead>
        <tr>
            <th colspan="7" align="left">

                <div style="float:right">
                    <form id="groupAdd" action="/characters/ajax/" method="post">
                        <input type="hidden" name="verb" value="group_add" />
                        <?php echo XSRF::html() ?>
                        <a href="javascript:;" onclick="groupAdd(document.getElementById('groupAdd'))" >Add New Group</a>
                    </form>
                </div>

                &nbsp;
            </th>
        </tr>
    </thead>
</table>

<?php
if( count($groups) > 0 )
{
	?>
	<table id="sortable_groups" class="info_table model_table">
		<thead>
			<tr>
				<th>
					Groups and Fields
				</th>
				<th width="80">Actions</th>
			</tr>
		</thead>
		
		<tbody>
			<?php
			foreach($groups as $group)
			{
				?>
				<?php $class='dark'; ?>
				<tr id="groups_<?php echo $group->get_id() ?>" class="sortable_group <?php echo $class ?>">
					<td>
						<table id="sortable_fields_<?php echo $group->get_id();?>" class="sortable_field">
							<thead >
								<tr>
									<th class="group_name">
										<span class="drag_icon" onmousedown="groupMouseDown(this);" onmouseup="groupMouseUp(this);"><img src="/tabmin/icons/drag-vertical.png" /></span>
										<span class="view" ><?php Printer::printString ( $group->get_name() ); ?></span>
										<span class="edit">
											<form id="groupEdit_<?php echo ( $group->get_id() ); ?>" action="/characters/ajax/" method="post" onsubmit="return false;" class="group_edit">
												<input type="hidden" name="verb" value="group_edit" />
												<input type="hidden" name="groups_id" value="<?php Printer::printString ( $group->get_id() ); ?>" />
												<input type="text" name="name" placeholder="Change Group Name Here" value="<?php Printer::printString ( $group->get_name() ); ?>" />
												<?php echo XSRF::html() ?>
												<input type="button" onclick="groupEdit(document.getElementById('groupEdit_<?php echo ( $group->get_id() ); ?>'))" value="Save" /> 
												<input type="button" onclick="groupHideEdit('<?php echo $group->get_id() ?>')" value="Cancel" />
											</form>
										</span>
									</th>
								</tr>
							</thead>
							
							<tbody>
								<?php
								if( count($group->get_fields()) > 0 )
								{
									$class = 'light';
									foreach($group->get_fields() as $field)
									{
										?>
											<tr id="fields_<?php echo $field->get_id() ?>" class="<?php echo $class ?>">
												<td class="field_name">
													<span class="drag_icon"><img src="/tabmin/icons/sort.png" /></span>
													<span class="view"><?php P::rint ($field->get_name()) ?></span>
													<span class="edit">
														<form id="fieldEdit_<?php echo ( $field->get_id() ); ?>" action="/characters/ajax/" method="post" onsubmit="return false;" class="field_edit">
															<input type="hidden" name="verb" value="field_edit" />
															<input type="hidden" name="fields_id" value="<?php Printer::printString ( $field->get_id() ); ?>" />
															<input type="text" name="name" placeholder="Change Field Name Here" value="<?php Printer::printString ( $field->get_name() ); ?>" />
															<?php echo XSRF::html() ?>
															<input type="button" onclick="fieldEdit(document.getElementById('fieldEdit_<?php echo ( $field->get_id() ); ?>'))" value="Save" /> 
															<input type="button" onclick="fieldHideEdit('<?php echo $field->get_id() ?>')" value="Cancel" />
														</form>
													</span>
												</td>
												<td>
													<div class="action_button" title="">
														&nbsp;
													</div>
													<div class="action_button" title="Edit Field">
														<?php
														if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$currentStory->get_series()->get_users_id()))
														{
															?>
															<a href="javascript:;" onclick="fieldShowEdit('<?php echo $field->get_id() ?>')" ><img src="/tabmin/icons/edit.png" /></a>
															<?php
														}
														?>
													</div>
													<div class="action_button" title="Delete Field">
														<?php
														if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$currentStory->get_series()->get_users_id()))
														{
															?>
															<form action="/characters/ajax/" method="post" onsubmit="AlertSet.confirm('Are you sure you want to delete <?php echo $field->get_name()?>?', function(){handleAjaxForm(this, function(){modelUpdateContent()}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;">
																<input type="hidden" name="verb" value="field_delete" />
																<input type="hidden" name="fields_id" value="<?php echo $field->get_id() ?>" />
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
								}
								else
								{
									//placeholder for elements in other groups to be dropped in. 
									?>
										<tr>
											<td>
												
											</td>
										</tr>
									<?php
								}
							?>
							</tbody>
						</table>
					</td>
					<td>
						<div class="action_button" title="Add Field to Group">
							<?php
								if($currentUser->hasPermission($module, 'add', $currentUser->get_id()==$currentStory->get_series()->get_users_id()))
								{
									?>
									<form id="fieldAdd_<?php echo $group->get_id()?>" action="/characters/ajax/" method="post">
										<input type="hidden" name="verb" value="field_add" />
										<input type="hidden" name="groups_id" value="<?php echo $group->get_id() ?>" />
										<?php echo XSRF::html() ?>
										<a href="javascript:;" onclick="fieldAdd(document.getElementById('fieldAdd_<?php echo $group->get_id()?>'))" ><img src="/tabmin/icons/add.png" /></a>
									</form>
									<?php
								}
							?>
						</div>
						<div class="action_button" title="Edit Group">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$currentStory->get_series()->get_users_id()))
							{
								?>
								<a href="javascript:;" onclick="groupShowEdit('<?php echo $group->get_id() ?>')" ><img src="/tabmin/icons/edit.png" /></a>
								<?php
							}
							?>
						</div>
						<div class="action_button" title="Delete Group">
							<?php
							if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$currentStory->get_series()->get_users_id()))
							{
								?>
								<form action="/characters/ajax/" method="post" onsubmit="AlertSet.confirm('Are you sure you want to delete <?php echo $group->get_name()?>?', function(){handleAjaxForm(this, function(){modelUpdateContent()}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;">
									<input type="hidden" name="verb" value="group_delete" />
									<input type="hidden" name="groups_id" value="<?php echo $group->get_id() ?>" />
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
else
{
	echo '<br /><strong>Your character model is empty.</strong>';
}
?>
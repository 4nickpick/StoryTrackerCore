<?php
ini_set('display_errors', true);
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();

switch(@$_GET['sort'])
{
	case 'last_name':
		$sort='users.last_name '. (@$_GET['sort_order']=='desc'? 'DESC' : '') .', users.first_name';
	break;
	case 'email':
		$sort = 'users.email '. (@$_GET['sort_order']=='desc'? 'DESC' : '');
	break;	
	case 'role':
	default:
		$sort='users.roles_id '. (@$_GET['sort_order']=='desc'? 'DESC' : '') .', users.last_name, users.first_name';
	break;
}
?>
<p>Search users by first and last names, email, phone, address:</p>
<input type="text" id="s<?php echo $module?>" value="<?php echo @$_GET['s'] ?>" />
<?php		
	$rolesManager = new Roles();					
	$roles = $rolesManager->loadAll();	
?>                                    
<select id="role<?php echo $module?>" name="roles[]">
	
	<option  value="">Please Select Role</option>
										 
	<?php
	
	foreach ($roles as $role)
	{	
	?>
		<option value="<?php echo $role->get_id()?>" <?php echo (@$_GET['role'] == $role->get_id()) ? 'selected' : ''?>><?php echo $role->get_role()?></option>
	<?php
	}
	?>
</select>

<input type="button" value="Search" onclick="
	tabset_<?php echo $module?>.getTab('<?php echo $tab ?>').url.search.s = document.getElementById('s<?php echo $module?>').value; 
    tabset_<?php echo $module?>.getTab('<?php echo $tab ?>').url.search.role = document.getElementById('role<?php echo $module?>').value; 
    tabset_<?php echo $module?>.getTab('<?php echo $tab ?>').reload()" /> 
<input type="button" value="Clear Search" onclick="
	tabset_<?php echo $module?>.getTab('<?php echo $tab ?>').url.search.s = '';
    tabset_<?php echo $module?>.getTab('<?php echo $tab ?>').url.search.role='';
    tabset_<?php echo $module?>.getTab('<?php echo $tab ?>').reload()" />

<?php

$page=intval(@$_GET['page']);
$records_per_page=50;
if($page<=0)
	$page=1;

$parameters = array();
if (@$_GET['role'])
	$parameters['roles.id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$_GET['role']);
$usersManager = new Users();
//$users=$usersManager->loadByParameters($parameters, $sort, (($page-1)*$records_per_page).','.$records_per_page);

$users=$usersManager->searchLoadByParameters(@$_GET['s'], $parameters, $sort, (($page-1)*$records_per_page).','.$records_per_page);
//$users=$usersManager->searchLoadByParameters(@$_GET['s'], $parameters, $sort, 2);
	
$total_records=$usersManager->getFoundRows();
if($total_records > 0)
{
	$total_pages=ceil($total_records/$records_per_page);
	?>
	<table class="info_table">
		<tr>
			<th colspan="7" align="left">
				<div style="float:right">
					<?php
					if($page>1)
					{
						echo '<div class="action_button"><a href="javascript:;" onclick="Tabmin.sortTab(tabset_'.$module.'.getTab(\''.$tab.'\'), false, 1);"><img src="'. $module->getIcon('page_first') .'" /></a></div>';
						echo '<div class="action_button"><a href="javascript:;" onclick="Tabmin.sortTab(tabset_'.$module.'.getTab(\''.$tab.'\'), false, '.($page-1).');"><img src="'. $module->getIcon('page_previous') .'" /></a></div>';
					}
					
					echo ' <div style="float:left;">Page <select onchange="Tabmin.sortTab(tabset_'.$module.'.getTab(\''.$tab.'\'), false, this.value);">';
					for($i=1; $i<=$total_pages; $i++)
					{
						echo '<option value="'.$i.'"';
						if($page==$i)
							echo ' selected="selected"';
						echo '>'.$i.'</option>';
					}
					echo '</select></div> ';
					
					if($page<$total_pages)
					{
						echo '<div class="action_button"><a href="javascript:;" onclick="Tabmin.sortTab(tabset_'.$module.'.getTab(\''.$tab.'\'), false, '.($page+1).');"><img src="'. $module->getIcon('page_next') .'" /></a></div>';
						echo '<div class="action_button"><a href="javascript:;" onclick="Tabmin.sortTab(tabset_'.$module.'.getTab(\''.$tab.'\'), false, '.$total_pages.');"><img src="'. $module->getIcon('page_last') .'" /></a></div>';
					}
					?>
				</div>
				Displaying <?php echo count($users)?> of <?php echo $total_records?> total members.
			</th>
		</tr>
		<tr>
			<th>
				<a href="javascript:Tabmin.sortTab(tabset_<?php echo $module?>.getTab('<?php echo $tab?>'), 'last_name');">Name</a>
				<?php echo (@$_GET['sort']=='last_name'? (@$_GET['sort_order']=='desc'? '&uarr;' : '&darr;') : '&nbsp;&nbsp;')?>
			</th>
			<th>
				<a href="javascript:Tabmin.sortTab(tabset_<?php echo $module?>.getTab('<?php echo $tab?>'), 'email');">Email</a>
				<?php echo (@$_GET['sort']=='email'? (@$_GET['sort_order']=='desc'? '&uarr;' : '&darr;') : '&nbsp;&nbsp;')?>
			</th>
            <th>
				<a href="javascript:Tabmin.sortTab(tabset_<?php echo $module?>.getTab('<?php echo $tab?>'), 'role');">Role</a>
				<?php echo (@$_GET['sort']=='role'? (@$_GET['sort_order']=='desc'? '&uarr;' : '&darr;') : '&nbsp;&nbsp;')?>
			</th>
			<th width="80">Actions</th>
		</tr>
		<?php
		foreach($users as $i=>$user)
		{
			$class='light';
			if($i%2==0)
				$class='dark';
			?>
			<tr class="<?php echo $class ?>">
				<td>
					<?php 
					Printer::printString ($user->get_last_name() .', '. $user->get_first_name());                     
					?>
                </td>
				<td><?php P::rint($user->get_email()) ?></td>
                <td><?php P::rint($user->get_role()->get_role())?></td>
				<td>
					<div class="action_button" title="Edit Member">
						<?php
						if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$user->get_id()))
						{
							?>
							<a href="javascript:;" onclick="Tabmin.addEditTab(tabset_<?php echo $module?>, '<?php echo htmlentitiesUTF8(str_replace('\'', '\\\'', json_encode($module->tabs['edit']->toArray($module))))?>', <?php echo $user->get_id()?>, {users_id: <?php echo $user->get_id()?>})"><img src="<?php echo $module->getIcon('edit') ?>" /></a>
							<?php
						}
						?>
					</div>
					<div class="action_button" title="Delete Member">
						<?php
						if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$user->get_id()))
						{
							?>
							<form action="<?php echo $module->path ?>/ajax.php" method="post" onsubmit="AlertSet.confirm('Are you sure you want to delete this user?', function(){handleAjaxForm(this, function(){tabset_<?php echo $module?>.getTab('<?php echo $tab?>').reload()}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;">
								<input type="hidden" name="verb" value="delete" />
								<input type="hidden" name="users_id" value="<?php echo $user->get_id() ?>" />
								<?php echo XSRF::html() ?>
								<input type="image" src="<?php echo $module->getIcon('delete')?>" />
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
	</table>
	<?php
}
else
	echo '<br /><strong>There are currently no members in the database.</strong>';
?>
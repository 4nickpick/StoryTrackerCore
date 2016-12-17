<form action="<?php echo $module->path?>/ajax.php" method="post" class="tabmin_form" onsubmit="return handleAjaxForm(this, function(resp){AlertSet.addJSON(resp).add(new AlertSet.Button('Back', function(){ window.location = '/'; })).show(); try{tabset_<?php echo $module?>.<?php echo $verb=='add'? 'getTab(\''.$tab.'\').reload(false)' : 'getTab(\''.$tab.(@$user ? $user->get_id() : '').'\').close(false)'?>; tabset_<?php echo $module?>.getTab('view').show();} catch(err){}}, function(resp) {AlertSet.addJSON(resp).show();})" autocomplete="off" enctype="multipart/form-data">
	<input type="hidden" name="verb" value="<?php echo $verb?>" />
	<input type="hidden" name="users_id" value="<?php echo (@$user ? $user->get_id() : '')?>" />
	<?php echo XSRF::html()?>
	<table class="form_table" style="width: auto;">
        <tr>
            <td colspan="2">
                <div class="AlertSet_info">
                    <ul>
                        <li>Password must be at least 10 characters long.</li>
                    </ul>
                </div>
            </td>
        </tr>
		<?php
		if($verb!='member_edit')
		{
			?>
			<tr>
				<th align="left" >Role:</th>
				<td>
					<select name="roles_id">
						<?php
						$rolesManager = new Roles();					
						$roles = $rolesManager->loadAll();

						$options='';
						foreach($roles as $i=>$role)
						{
							$options .= '<option value="'.$role->get_id().'"';
							if(!empty($user) && $user->get_role()->get_id()==$role->get_id() || empty($user) && $role->get_id()==3)
								$options .= ' selected';
							$options .= '>'.P::sanitize($role->get_role()).'</option>';
						}
						echo $options;
						?>
					</select>
				</td>
			</tr>
			<?php
		}
		?>
        <tr>
            <th align="left">First Name:</th>
            <td><input type="text" name="first_name" value="<?php P::rint(@$user ? $user->get_first_name() : '')?>" /></td>
        </tr>
        <tr>
            <th align="left">Last Name:</th>
            <td><input type="text" name="last_name" value="<?php P::rint(@$user ? $user->get_last_name() : '')?>" /></td>
        </tr>
		<tr>
			<th align="left">Email:</th>
			<td><input type="text" name="email" value="<?php P::rint(@$user ? $user->get_email() : '')?>" /></td>
		</tr>
		<tr>
			<th align="left">Password:</th>
			<td>
				<input type="password" name="password" />
				<?php
				if($verb=='edit' || $verb=='member_edit')
					echo '<small><em>Leave blank to keep the existing password.</em></small>';
				?>
			</td>
		</tr>
		<tr>
			<th align="left">Password&nbsp;Again:</th>
			<td><input type="password" name="password2" /></td>
		</tr>
        <?php
        if($verb!='member_edit')
        {
            ?>
            <tr>
                <th align="left">Nick Name:</th>
                <td><input type="text" name="nick_name" value="<?php P::rint(@$user->nick_name)?>" /></td>
            </tr>

            <tr>
                <th align="left">Phone Number:</th>
                <td><input type="text" name="phone" value="<?php P::rint(@$user->phone)?>" /></td>
            </tr>

            <tr>
                <th align="left">Address:</th>
                <td><input type="text" name="address" value="<?php P::rint (@$user ? $user->get_address() : '')?>" /></td>
            </tr>
            <tr>
                <th align="left">City:</th>
                <td><input type="text" name="city" value="<?php P::rint (@$user ? $user->get_city() : '')?>" /></td>
            </tr>
            <tr>
                <th align="left">State:</th>
                <td><input type="text" name="state" value="<?php P::rint (@$user ? $user->get_state() : '')?>" /></td>
            </tr>
            <tr>
                <th align="left">Zip:</th>
                <td><input type="text" name="zip" value="<?php P::rint(@$user ? $user->get_zip() : '')?>" /></td>
            </tr>

            <tr>
                <th align="left">Profile Picture:
                    <?
                    if (!empty($user))
                        if(($user->get_picture()))
                        {
                            ?>
                            <br /><img src="/show-picture.php?pictures_id=<?=$user->get_picture()->get_id()?>&w=120&h=80" alt="User Avatar"/>
                        <?
                        }
                    ?>
                </th>
                <td>
                    <input type="file" name="picture" />
                    <?php
                    if (!empty($user))
                        if(($user->get_picture()))
                            echo '<small><em>Leave blank to keep existing picture.</em></small>';
                    ?>
                </td>
            </tr>

        <?php
        }
        ?>
		<tr>
			<td>
				<input type="button" value="Cancel" onclick="<?php
					if($verb=='edit')
						echo 'tabset_'. $module .'.getTab(\''. $tab.$user->get_id() .'\').close();';
					else if($verb=='add')
						echo 'tabset_'. $module .'.getTab(\''. $tab .'\').reload();';
					else
						echo 'AlertSet.confirm(\'Are you sure? You will lose any unsaved data.\', function(){this.reset();}.bind(this.form));';
					?>" />
			</td>
			<td><input type="submit" value="Save" /></td>
		</tr>
	</table>
</form>

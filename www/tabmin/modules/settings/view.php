<?phprequire_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');include INCLUDE_ROOT.'/ajax_secure.inc.php';ob_start();$verb='view';if(!empty($_GET['settings_id'])){	$settings = new Settings();	$setting=$settings->loadById($_GET['settings_id']);    $pictures = NULL;    if( $setting )    {        $picturesManager = new Pictures();        $pictures=$picturesManager->loadByTags(            '',            $currentUser->get_id(),            NULL,            array($setting->get_id()),            NULL,            'pictures_to_settings.cover_photo DESC, pictures_to_settings.priority'        );    }}?><?php TemplateSet::begin('scripts'); ?><?php TemplateSet::end(); ?><?php TemplateSet::begin('body'); ?><?php	if(!$setting)		echo 'The setting you selected was not found in the database. They may have been deleted.';	else if( $setting->get_story()->get_id() != $currentStory->get_id() ||        $setting->get_users_id() !== $currentUser->get_id() )		echo 'You do not have permission to view this setting.';	else	{		?>			<div id="view">				<table class="view_table">					<tr id="header">						<th colspan="2">                            <div>                                <span><?php echo htmlentitiesUTF8($setting->get_full_name()) . ' '; ?></span>                                <div class="actions">                                    <a href="/settings/edit/<?=$setting->get_id()?>"><img src="/tabmin/icons/edit.png"/></a>                                    <a href="/settings/edit/<?=$setting->get_id()?>/?gallery"><img src="/tabmin/icons/gallery.png"/></a>                                    <?php                                    if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$setting->get_series_id()))                                    {                                        ?>                                        <form id="delete_form" action="/settings/ajax/" method="post" onsubmit="return false;">                                            <input type="hidden" name="verb" value="delete" />                                            <input type="hidden" name="settings_id" value="<?php echo $setting->get_id() ?>" />                                            <?php echo XSRF::html() ?>                                            <a href="javascript:" onclick="AlertSet.confirm('Are you sure you want to delete <?php echo $setting->get_full_name()?>?', function(){throb(); handleAjaxForm(document.getElementById('delete_form'), function(resp){ AlertSet.redirectWithAlert('/settings/list/', resp)}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;"">                                            <img src="/tabmin/icons/delete.png" />                                            </a>                                        </form>                                    <?php                                    }                                    ?>                                </div>                            </div>						</th>					</tr>                    <?php if( $setting->get_aliases() ) :	?>                        <tr>                            <th class="label">                                Also Known As                            </th>                            <td colspan="2" class="data">                                <span><?php echo implode('<br />',$setting->get_aliases_list()); ?></span>                            </td>                        </tr>                    <?php endif; ?>                    <?php                    if( isset($pictures) && count($pictures) > 0 )                    {                        ?>                        <tr id="image">                            <td colspan="2">                                <div class="photo-slider">                                    <span class="prev"> <a href="javascript:" onclick="slideSwitch(false);">&laquo;</a> </span>                                    <?php                                        foreach( $pictures as $i=>$picture )                                        {                                            $class = 'active-slide';                                            if( $i != 0 )                                                $class="inactive-slide";                                            ?>                                            <a id="photo-thumbnail<?=$picture->get_id()?>" data-picture_id="<?=$picture->get_id()?>" class="photo-thumbnail fancybox <?=$class?> slide" rel="group" href="/show-picture.php?pictures_id=<?=@$picture->get_id()?>">                                            </a>                                        <?php                                        }                                    ?>                                    <span class="next"> <a href="javascript:" onclick="slideSwitch(true);">&raquo;</a> </span>                                </div>                            </td>                        </tr>                        <?php                    }                    ?>										<?php						//Draw Module Form						$settingsManager = new Settings();						$settingsModel=$settingsManager->loadModel($currentStory->get_series()->get_id(), $setting->get_id());						$groups = $settingsModel->get_groups();						if( count($groups) > 0 )						{							foreach($groups as $group)							{								$fields = $group->get_fields();								if( count($fields) > 0 )								{									$show_group = false;									foreach($fields as $field)									{										if( $field->get_value() != '' )										{											$show_group = true;											break;										}									}																		if( $show_group )									{									?>										<tr class="subheader">											<th colspan="2">												<?=htmlentitiesUTF8($group->get_name());?>											</th>										</tr>										<?php										foreach($fields as $field)										{											if( $field->get_value() != '' )											{												?>												<tr>													<th class="label">														<?=htmlentitiesUTF8($field->get_name())?>													</th>													<td class="data">														<span><?=htmlentitiesUTF8($field->get_value());?></span>													</td>												</tr>												<?											}										}									}								}							}						}					?>				</table>                <div id="content">                    <div id="description" class="content_section">                        <h1>Description</h1>                        <?                        if( strlen($setting->get_content()) > 0 )                            echo ($setting->get_content());                        else                            echo '<em>No description has been added for this setting.</em>';                        ?>                    </div>                </div>			</div><!-- view -->		<?php	}?><?php TemplateSet::end() ?><?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
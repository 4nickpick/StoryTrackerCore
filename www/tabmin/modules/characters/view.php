<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start()

;$verb='view';
if(!empty($_GET['characters_id']))
{
	$charactersManager = new Characters();
	$character=$charactersManager->loadById($_GET['characters_id']);

    $pictures = NULL;
    if( $character )
    {
        $picturesManager = new Pictures();
        $pictures=$picturesManager->loadByTags(
            '',
            $currentUser->get_id(),
            array($character->get_id()),
            NULL,
            NULL,
            'pictures_to_characters.cover_photo DESC, pictures_to_characters.priority'
        );
    }
}
?>

<?php TemplateSet::begin('scripts'); ?>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
<?php
	if(!$character)
		echo 'The character you selected was not found in the database. They may have been deleted.';
	else if( $character->get_story()->get_id() != $currentStory->get_id() ||
        $character->get_users_id() !== $currentUser->get_id() )
		echo 'You do not have permission to view this character.';
	else
	{
		?>
			<div id="view">
				<table class="view_table">
					<tr id="header">
						<th colspan="2">
                            <div>
                                <span><?php echo htmlentitiesUTF8($character->get_full_name()) . ' '; ?></span>
                                <div class="actions">
                                    <a href="/characters/edit/<?=$character->get_id()?>"><img src="/tabmin/icons/edit.png"/></a>

                                    <a href="/characters/timeline/<?=$character->get_id()?>"><img src="/tabmin/icons/timeline.png"/></a>

                                    <a href="/characters/edit/<?=$character->get_id()?>/?gallery"><img src="/tabmin/icons/gallery.png"/></a>

                                    <?php
                                    if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$character->get_series_id()))
                                    {
                                        ?>
                                        <form id="delete_form" action="/characters/ajax/" method="post" onsubmit="return false;">
                                            <input type="hidden" name="verb" value="delete" />
                                            <input type="hidden" name="characters_id" value="<?php echo $character->get_id() ?>" />
                                            <?php echo XSRF::html() ?>
                                            <a href="javascript:" onclick="AlertSet.confirm('Are you sure you want to delete <?php echo $character->get_full_name()?>?', function(){throb(); handleAjaxForm(document.getElementById('delete_form'), function(resp){ AlertSet.redirectWithAlert('/characters/list/', resp)}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;"">
                                            <img src="/tabmin/icons/delete.png" />
                                            </a>
                                        </form>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
						</th>
					</tr>

                    <?php if( $character->get_aliases() ) :	?>
                        <tr>
                            <th class="label">
                                Also Known As
                            </th>
                            <td colspan="2" class="data">
                                <span>
                                    <?php
                                    foreach( $character->get_aliases_list() as $character_alias )
                                        echo htmlentitiesUTF8($character_alias) . '<br />';
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php
                    if( isset($pictures) && count($pictures) > 0 )
                    {
                        ?>
                        <tr id="image">
                            <td colspan="3">
                                <div class="photo-slider" >
                                    <span class="prev"> <a href="javascript:" onclick="slideSwitch(false);">&laquo;</a> </span>
                                    <?php
                                        foreach( $pictures as $i=>$picture )
                                        {
                                            $class = 'active-slide';
                                            if( $i != 0 )
                                                $class="inactive-slide";
                                            ?>
                                            <a id="photo-thumbnail<?=$picture->get_id()?>" data-picture_id="<?=$picture->get_id()?>" class="photo-thumbnail fancybox <?=$class?> slide" rel="group" href="/show-picture.php?pictures_id=<?=@$picture->get_id()?>">
                                            </a>
                                            <?php
                                        }
                                    ?>
                                    <span class="next"> <a href="javascript:" onclick="slideSwitch(true);">&raquo;</a> </span>
                                </div>
                            </td>
                        </tr>
                        <?php
                        }
                    ?>
					<?php
						//Draw Module Form
						$charactersManager = new Characters();
						$characterModel=$charactersManager->loadModel($currentStory->get_series()->get_id(), $character->get_id());
						$groups = $characterModel->get_groups();
						if( count($groups) > 0 )
						{
							foreach($groups as $group)
							{
								$fields = $group->get_fields();
								if( count($fields) > 0 )
								{
									$show_group = false;
									foreach($fields as $field)
									{
										if( $field->get_value() != '' )
										{
											$show_group = true;
											break;
										}
									}
									
									if( $show_group )
									{
									?>
										<tr class="subheader">
											<th colspan="3">
												<?=htmlentitiesUTF8($group->get_name())?>
											</th>
										</tr>
										<?php
										foreach($fields as $field)
										{
											if( $field->get_value() != '' )
											{
												?>
												<tr>
													<th class="label">
														<?=htmlentitiesUTF8($field->get_name())?>
													</th>
													<td colspan="2" class="data">
														<span><?=htmlentitiesUTF8($field->get_value())?></span>
													</td>
												</tr>
												<?
											}
										}
									}
								}
							}
						}
					?>
                    <?php
                        $relationship_types = array(
                            1=>'Allied',
                            2=>'Conflicted',
                            3=>'Romantic'
                        );

                        $loaded_relationships = NULL;

                        foreach($relationship_types as $i=>$relationship_type)
                        {
                            $relationships_loader = new RelationshipChartConnections();
                            $relationships = $relationships_loader->loadByCharacterAndType($character->get_id(), $i, false);
                            if( count($relationships) > 0 )
                            {
                                foreach($relationships as $relationship)
                                {
                                    $relationship_character = $relationship->get_node1()->get_characters_id() != $character->get_id() ?
                                    array(
                                        'id'=>$relationship->get_node1()->get_characters_id(),
                                        'name'=>$relationship->get_node1()->get_characters_name()
                                    ) :
                                    array(
                                        'id'=>$relationship->get_node2()->get_characters_id(),
                                        'name'=>$relationship->get_node2()->get_characters_name()
                                    );
                                    $loaded_relationships[$i][$relationship_character['id']] =
                                        $relationship_character['name'];
                                }
                            }
                        }

                        if( count($loaded_relationships) > 0 )
                        {
                            ?>
                            <tr class="subheader">
                                <th colspan="3">
                                    Relationships
                                </th>
                            </tr>
                            <?php
                            foreach($loaded_relationships as $i=>$loaded_relationship)
                            {
                                if( count($loaded_relationship) > 0 )
                                {
                                    ?>
                                    <tr>
                                        <th class="label">
                                            <?=htmlentitiesUTF8($relationship_types[$i])?>
                                        </th>
                                        <td colspan="2" class="data">
                                        <span>
                                            <?php
                                            foreach($loaded_relationship as $character_id=>$character_name)
                                            {
                                                echo '<a href="/characters/view/'.$character_id.'">'.$character_name.'</a>';
                                                if( end($loaded_relationship) !== $character_name )
                                                    echo ', ';
                                            }
                                            ?>
                                        </span>
                                        </td>
                                    </tr>
                                <?php
                                }
                            }
                        }
                        ?>
				</table>

                <div id="content">

                    <div id="bio" class="content_section">
                        <h1>Biography</h1>
                        <?
                        if( strlen($character->get_content()) > 0 )
                            echo ($character->get_content());
                        else
                            echo '<em>No biography has been added for this character.</em>';
                        ?>
                    </div>

                    <div id="relationships" class="content_section">
                        <h1>Relationships</h1>
                        <p>
                        <?php
                            $any_relationships_found = false;
                            foreach($relationship_types as $i=>$relationship_type)
                            {
                                $relationships_loader = new RelationshipChartConnections();
                                $relationships = $relationships_loader->loadByCharacterAndType($character->get_id(), $i, true);
                                if( count($relationships) > 0 )
                                {
                                    foreach($relationships as $relationship)
                                    {
                                        $any_relationships_found = true;
                                        ?>
                                        <h4>
                                            <?= $relationship->get_node1()->get_characters_id() != $character->get_id() ?
                                                htmlentitiesUTF8($relationship->get_node1()->get_characters_name()) :
                                                htmlentitiesUTF8($relationship->get_node2()->get_characters_name()) ;
                                            ?>
                                            <a href="/characters/relationships/?charts_id=<?=$relationship->get_chart()->get_id()?>">
                                                <small>see chart</small>
                                            </a>

                                        </h4>
                                        <?=($relationship->get_content())?>
                                    <?php
                                    }
                                }
                            }

                            if( !$any_relationships_found )
                                echo '<br /><strong>This character has no relationships.</strong>&nbsp;<a href="/characters/relationships/">Add a Relationship Chart</a>';

                        ?>
                        </p>
                    </div>

                    <div id="events" class="content_section">
                        <h1>Plot Events</h1>
                        <ul>
                            <?php
                                $event_loader = new PlotEvents();
                                $events = $event_loader->loadByCharacter($character->get_id(), true);
                                if( count($events) > 0 )
                                {
                                    foreach($events as $event)
                                    {
                                        ?>
                                        <li>
                                            <a href="/plot/view/<?=$event->get_id()?>">
                                                <?=($event->get_event()) ?>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                }
                            ?>
                        </ul>
                        <?php
                            if( count($events) <= 0 )
                            {
                                echo '<p><em>This character has not been attached to any events.</em></p>';
                            }
                        ?>
                    </div>

                </div>
			</div><!-- view -->
		<?php
	}
?>
<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
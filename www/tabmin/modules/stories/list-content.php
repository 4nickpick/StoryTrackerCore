<?php

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php
switch(@$_GET['sort'])
{
	case 'full_name':
		$sort='stories.name ';
	break;
	case 'priority':
	default:
		$sort='stories.priority ';
	break;
}

$loader = new Stories();
	
$parameters = array();
$parameters['users_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentUser->get_id());
$stories=$loader->searchLoadByParameters(@$_GET['s'], $parameters, $sort, '');
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
					
					Displaying <?php echo count($stories)?> Stories... <a href="/stories/add/">Add a Story</a>
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
			foreach($stories as $i=>$story)
			{
				$class='light';
				if($i%2==0)
					$class='dark';
				?>
				<tr id="stories_<?php echo $story->get_id()?>" class="<?php echo $class ?>">
					<td>
						<?php

                        $name = htmlentitiesUTF8($story->get_name());
                        if( $story->isPartOfSeries() )
                        {
                            $name = '<span class="series_name">' . htmlentitiesUTF8($story->get_series()->get_name()) . ':</span> ' .
                                htmlentitiesUTF8($story->get_name());
                        }

                        echo  $name ;
						?>
					</td>
					<td>
                        <?php if (@$_GET['sort'] != 'full_name' ? 'id="sortable"' : '') : ?>
                            <div class="sort action_button" title="Sort Story">
                                <?php
                                if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$story->get_series()->get_users_id()))
                                {
                                    ?>
                                    <img src="/tabmin/icons/sort.png" style="cursor:pointer;"/>
                                    <!--<a href="/stories/view/<?php echo $story->get_id()?>"><img src="/tabmin/icons/sort.png" /></a>-->
                                    <?php
                                }
                                ?>
                            </div>
                        <?php endif; ?>
						<div class="action_button" title="Edit Story">
							<?php
							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$story->get_series()->get_users_id()))
							{
								?>
								<a href="/stories/edit/<?php echo $story->get_id()?>"><img src="/tabmin/icons/edit.png" /></a>
								<?php
							}
							?>
						</div>
						<div class="action_button" title="Delete Story">
							<?php
							if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$story->get_series()->get_users_id()))
							{
								?>
								<form action="/stories/ajax/" method="post" onsubmit="AlertSet.confirm('Are you sure you want to delete <?php echo addslashes($story->get_name())?>?', function(){throb(); handleAjaxForm(this, function(){ window.location.reload();}, function(resp){AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;">
									<input type="hidden" name="verb" value="delete" />
									<input type="hidden" name="stories_id" value="<?php echo $story->get_id() ?>" />
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
	echo '<br /><strong>No Stories matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".</strong> <a href="/stories/add">Add a Story</a>';
}
else
{
	echo '<br /><strong>There are currently no Characters in the database.</strong>&nbsp;<a href="/characters/add">Add a Character</a>';
}
?>
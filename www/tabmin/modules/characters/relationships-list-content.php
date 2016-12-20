<?php// Relationship Charts Listrequire_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');include INCLUDE_ROOT.'/ajax_secure.inc.php';?><?phpswitch(@$_GET['sort']){	case 'name':		$sort='relationship_charts.name ';	break;	case 'priority':	default:		$sort='relationship_charts.priority ';	break;}$chartsManager = new RelationshipCharts();	$parameters = array();$parameters['series_id'] = array('type'=>'int', 'condition'=>'=', 'value'=>$currentStory->get_series()->get_id());$charts=$chartsManager->searchLoadByParameters(@$_GET['s'], $parameters, $sort, '');//$characters = $charactersManager->loadAll();//var_dump($characters);?><table class="info_table list_table">    <thead>        <tr>            <th colspan="7" align="left">                <div style="float:right">                    <form id="chartListAdd" action="/characters/ajax/" method="post">                        <input type="hidden" name="verb" value="chart_add" />                        <?php echo XSRF::html() ?>                        <a href="javascript:;" onclick="chartListAdd(document.getElementById('chartListAdd'))" >Add New Chart</a> |                        <a href="javascript:;" onclick="$('#relationships-list').hide();" >Close Window</a>                    </form>                </div>                &nbsp;            </th>        </tr>    </thead></table><?php$total_records=$chartsManager->getFoundRows();if($total_records > 0){	?>	<table id="sortable" class="info_table list_table">		<thead>			<tr>				<th>					Name				</th>				<th width="80">Actions</th>			</tr>		</thead>		<tbody>			<?php			foreach($charts as $i=>$chart)			{				$class='light';				if($i%2==0)					$class='dark';				?>				<tr id="charts_<?php echo $chart->get_id()?>" data-chart_name="<?php P::out($chart->get_name())?>" class="<?php echo $class ?>">					<td class="chart_name">						<span class="view"><?php Printer::printString ( $chart->get_name() ); ?></span>                        <span class="edit">                            <form id="chartEdit_<?php echo ( $chart->get_id() ); ?>" action="/characters/ajax/" method="post" onsubmit="return false;" class="chart_edit">                                <input type="hidden" name="verb" value="chart_edit" />                                <input type="hidden" name="charts_id" value="<?php Printer::printString ( $chart->get_id() ); ?>" />                                <input type="text" name="new_name" placeholder="Change Chart Name Here" value="<?php Printer::printString ( $chart->get_name() ); ?>" />                                <?php echo XSRF::html() ?>                                <input type="button" onclick="chartEdit(document.getElementById('chartEdit_<?php echo ( $chart->get_id() ); ?>'))" value="Save" />                                <input type="button" onclick="chartHideEdit('<?php echo $chart->get_id() ?>')" value="Cancel" />                            </form>                        </span>					</td>					<td>                        <div class="action_button sort" title="Sort Chart">                            <?php                            if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$chart->get_users_id()))                            {                                ?>                                <a href="javascript:;" onclick=""><img src="/tabmin/icons/sort.png" /></a>                                <?php                            }                            ?>                        </div>						<div class="action_button" title="Edit Chart Name">							<?php							if($currentUser->hasPermission($module, 'edit', $currentUser->get_id()==$chart->get_users_id()))							{								?>								<a href="javascript:;" onclick="chartShowEdit('<?php echo $chart->get_id()?>');"><img src="/tabmin/icons/edit.png" /></a>								<?php							}							?>						</div>						<div class="action_button" title="Delete Chart">							<?php							if($currentUser->hasPermission($module, 'delete', $currentUser->get_id()==$chart->get_users_id()))							{								?>								<form action="/characters/ajax/" method="post" onsubmit="AlertSet.confirm('Are you sure you want to delete <?php echo addslashes($chart->get_name()) ?>?', function(){ handleAjaxForm(this, function(){chartListUpdateContent()}, function(resp){console.log('delete failed.');AlertSet.clear().addJSON(resp).show();});}.bind(this)); return false;">									<input type="hidden" name="verb" value="chart_delete" />									<input type="hidden" name="charts_id" value="<?php echo $chart->get_id() ?>" />									<?php echo XSRF::html() ?>									<input type="image" src="/tabmin/icons/delete.png" />								</form>								<?php							}							?>						</div>											</td>				</tr>				<?php			}			?>		</tbody>	</table>	<?php}else if ( isset($_GET['s']) ){	echo '<br /><strong>No charts matched your search for "<em>' . htmlentitiesUTF8($_GET['s']) . '</em>".</strong>';}else{	echo '<br /><strong>There are currently no charts in the database.</strong>';}?>
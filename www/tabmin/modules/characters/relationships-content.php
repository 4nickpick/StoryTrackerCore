<?php

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<?php

$parameters = NULL;
$chartsManager = new RelationshipCharts();
$chart = $chartsManager->loadById(@$_GET['charts_id']);

if( $chart && $chart->get_id() > 0 )
{
	?>
    <p>
        <strong>HINT: Double-click a character's name to view their profile or delete them from the chart.</strong>
    </p>

	<div class="relationship_chart">
        <div id="chart_menu">
            <a href="javascript:" onclick="showQuickForm('#add-character-to-chart-dialog');">Add Characters</a>
        </div>

        <!-- #main houses the Relationship Chart App -->
        <div id="main"></div>
	</div>
    <?php
}
else
{
	echo '<br /><strong></strong>';
}
?>

<div id="add-character-to-chart-dialog" title="Add Characters To Chart" class="hidden-dialog quick-form">
    <?php include('quick-form.php'); ?>
</div>

<div id="edit-connection" title="Edit Relationship" class="hidden-dialog"></div>
<div id="edit-node" title="Edit Character" class="hidden-dialog"></div>
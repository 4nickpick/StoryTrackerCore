<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();
?>

<?php TemplateSet::begin('scripts'); ?>
    <script type="text/javascript" src="/tabmin/modules/characters/js/module.js"></script>
    <script type="text/javascript">
        chartListInit();
        chartInit();
    </script>
    <link href="/css/jsplumb.css" rel="stylesheet" type="text/css" />
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>

    <h1>Relationship Charts</h1>
    <input type="hidden" id="charts_id" name="charts_id" value="<?=@$_GET['charts_id']?>" >

    <div id="throbber" style="float:right; display:none;">
        <img src="/images/throbber.gif" />
    </div>

   <div id="relationship_chart_selector">
        <div onclick="$('#relationships-list').toggle();">
            <span id="chart_title">Select a Relationship Chart...</span>
            <img class="select_arrow" src="/images/ui/select_arrow.png" />
        </div>

        <div id="relationships-list">
            <?php include('relationships-list-content.php'); ?>
        </div>
    </div>

    <div id="relationships-content" class="relationships-content">
        <?php include('relationships-content.php'); ?>
    </div> <!-- relationships-content -->

<?php
$chart_loader = new RelationshipCharts();
$temp_chart = $chart_loader->loadById(@$_GET['charts_id']);
if( !empty($temp_chart) )
{
    ?>
    <script type="text/javascript">
        viewChart(<?=$temp_chart->get_id()?>, '<?=addslashes($temp_chart->get_name())?>');
    </script>
    <?php
}
?>

<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
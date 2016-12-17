<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();
?>

<?php TemplateSet::begin('scripts'); ?>
	<script type="text/javascript" src="/tabmin/modules/plot/js/module.js"></script>
	<script type="text/javascript">
		listInit();
	</script>

<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
	
<input type="text" id="s" placeholder="Find an Event By Name" value="<?php echo htmlentitiesUTF8(@$_GET['s']) ?>"
	onkeyup="listUpdateContent();" autocomplete="off" />	

<div id="throbber" style="float:right; display:none;">
	<img src="/images/throbber.gif" />
</div>

<div id="list-content" class="list-content">
	<?php include('list-content.php'); ?>
</div><!-- list-content -->

<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
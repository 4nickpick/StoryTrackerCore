<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();
?>

<?php TemplateSet::begin('scripts'); ?>
	<script type="text/javascript" src="/tabmin/modules/characters/js/module.js"></script>
	<script type="text/javascript">
		timelineListInit(<?=$_GET['characters_id']?>);
	</script>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
	
<input type="text" id="s" placeholder="Find an Event By Description" value="<?php echo htmlentitiesUTF8(@$_GET['s']) ?>"
	onkeyup="timelineListUpdateContent(<?=$_GET['characters_id']?>);" autocomplete="off" />

<div id="throbber" style="float:right; display:none;">
	<img src="/images/throbber.gif" />
</div>

<div id="timeline-content" class="timeline-content">
	<?php include('timeline-content.php'); ?>
</div><!-- timeline-list-content -->

<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
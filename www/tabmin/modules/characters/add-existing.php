<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();
?>

<?php TemplateSet::begin('scripts'); ?>
	<script type="text/javascript" src="/tabmin/modules/characters/js/module.js"></script>
	<script type="text/javascript">
		addExistingListInit();
	</script>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>

<h1>Add an Existing Character</h1>

<p>
    <input type="text" id="s" placeholder="Find a Character By Name or Alias" value="<?php echo @$_GET['s'] ?>"
           onkeyup="addExistingListUpdateContent();" autocomplete="off" />
</p>

<div id="throbber" style="float:right; display:none;">
	<img src="/images/throbber.gif" />
</div>

<div id="list-content" class="list-content">
	<?php include('add-existing-content.php'); ?>
</div><!-- list-content -->

<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
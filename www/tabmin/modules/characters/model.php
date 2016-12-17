<?php
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
ob_start();
?>

<?php TemplateSet::begin('scripts'); ?>
	<script type="text/javascript" src="/tabmin/modules/characters/js/module.js"></script>
	<script type="text/javascript">
		modelInit();
	</script>
<?php TemplateSet::end(); ?>

<?php TemplateSet::begin('body'); ?>
	
<h1>Manage Character Model</h1>	

<div id="throbber" style="float:right; display:none;">
	<img src="/images/throbber.gif" />
</div>

<div id="model-content" class="model-content">
	<?php include('model-content.php'); ?>
</div><!-- model-content -->

<?php TemplateSet::end() ?>
<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php');
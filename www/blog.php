<?php

/* 

	Story Tracker

	By Nicholas Pickering
	
	Track, Search, and Optimize your Science Fiction/Fantasy story

*/	

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

?>

<?php TemplateSet::begin('body') ?>
BLOG CONTENT
<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
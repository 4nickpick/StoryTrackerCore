<?php
ini_set('display_errors', true);
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='add';
include 'form.php';
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/../private/_global/libs/htmlpurifier-4.6.0/library/HTMLPurifier.auto.php';

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

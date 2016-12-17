<?php

require_once('/home3/npickeri/public_html/storytracker.net/private/includes/config.inc.php');

if( $is_dev )
{
    $mysqldump_root = 'C:\wamp\bin\mysql\mysql5.5.24\bin\\';
    $host = 'localhost';
    $user = 'root';
    $password = 'CxEr3dAbyjuFR2pX';
    $database_name = TABMIN_DB_NAME;
}
else
{
    $mysqldump_root = '';
    $host = TABMIN_DB_HOST;
    $user = TABMIN_DB_USERNAME;
    $password = TABMIN_DB_PASSWORD;
    $database_name = TABMIN_DB_NAME;
}

$command = $mysqldump_root.'mysqldump ' .
    ' --user=' . $user .
    ' --password=' . $password .
    ' ' . $database_name . ' > ' .
    '/home3/npickeri/public_html/storytracker.net/private/_backups/'. date('Y-m-d-h-i-s') . '.sql';

if( exec( $command ) != '' )
{
    echo 'backup failed.';
}
else
{
    echo 'backup success: ' . date('Y-m-d-h-i-s');
}
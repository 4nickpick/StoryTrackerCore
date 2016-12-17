<?php

/*
 *
 * Access the below url to generate a database backup
 * http://storytracker.net/db.php?key=GAZWNnUn
 *
 */

require_once('/home3/npickeri/public_html/storytracker.net/private/includes/config.inc.php');

if( $_GET['key'] == 'GAZWNnUn')
{
    include('/home3/npickeri/public_html/storytracker.net/private/includes/db.php');
}
else
{
    echo 'backup failed - invalid auth';
}
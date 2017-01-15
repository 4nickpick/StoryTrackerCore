<?php
if(!in_array($_SERVER['REMOTE_ADDR'], array('50.159.213.30', '127.0.0.1', '::1'))) {
    echo 'You do not belong here.';
    exit();
}
<?php
if($_SERVER['HTTPS']=='off') 
{
	header("Location:https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']?"?".$_SERVER['QUERY_STRING']:""));
}
?>
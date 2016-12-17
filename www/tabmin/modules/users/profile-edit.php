<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';

$verb='member_edit';
$user = NULL;
if(!empty($currentUser))
    $user = $currentUser;
?>

<?php TemplateSet::begin('body') ?>

    <h2>Edit Your Profile</h2>
    <br />
<?php
if($user)
    include 'form.php';
else
    echo 'The user you selected was not found in the database. They may have been deleted.';
?>

    <br />
    <br />

    <h2>Account Actions</h2>
    <br />

    <a href="javascript:;"
       onclick="AlertSet.confirm('This action may take a few minutes. Are you sure you want to export all of your data?', function(resp) { exportAccountData() }, function() {});">Export all of my data</a>
    <img id="throbber" style="display:none" src="/images/throbber.gif" />
    <p>You'll receive an email with the text content in your Story Tracker account. Pictures cannot be included with the export at this time.<br />
    This action may take a few minutes, please be patient. </p>
<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
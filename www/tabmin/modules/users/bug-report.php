<?php

// Bug Report Form

require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
include INCLUDE_ROOT.'/ajax_secure.inc.php';
?>

<p id="instructions">
    Please describe in <strong>as much detail as possible</strong> the problem you are experiencing,
    or the feature you'd like to request.
</p>

<form id="bug_report_form" action="/users/ajax/" method="post" class="tabmin_form" autocomplete="off" enctype="multipart/form-data"
      onsubmit="return submitBugReport(this)">
    <input type="hidden" name="verb" value="bug-report" />
    <input type="hidden" name="current-user-id" value="<?=$currentUser->get_id()?>" />
    <table class="form_table">
        <tbody style="display: block !important;">
            <tr>
                <th>Name:</th>
                <td>
                    <input type="text" name="name" disabled="disabled" value="<?=htmlentitiesUTF8($currentUser->get_first_name() . ' ' . $currentUser->get_last_name()) ?>"/>
                </td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>
                    <input type="text" name="email" disabled="disabled" value="<?=htmlentitiesUTF8($currentUser->get_email()) ?>"/>
                </td>
            </tr>
            <tr style="display: none;">
                <th>Current Page:</th>
                <td>
                    <input type="text" name="current_page" disabled="disabled" value="<?=htmlentitiesUTF8($_SERVER['REQUEST_URI'])?>"/>
                </td>
            </tr>
            <tr style="display: none;">
                <th>Browser:</th>
                <td>
                    <input type="text" name="browser" disabled="disabled" value="<?=htmlentitiesUTF8($_SERVER['HTTP_USER_AGENT'])?>"/>
                </td>
            </tr>
            <tr>
                <th>Describe Problem:</th>
                <td>
                    <textarea name="problem" ></textarea>
                </td>
            </tr>
        </tbody>
    </table>


</form>

<div id="results"></div>
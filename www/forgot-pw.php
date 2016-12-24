<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

$showForm = true;
if( @$_POST['verb'] == 'forgotpw' )
{
    if(!empty($_POST['email']))
    {	$usersManager = new Users();
        if($usersManager->sendforgotPasswordLink($_POST['email']))
        {
            //$json['success'] = true;
            $msg = ('You have been sent an email with further instructions. Please follow the link in the email to reset your password. If the email does not appear in your inbox, please check any spam folders. ');
            $showForm = false;
        }
        else
            $msg = ('We were not able to send the email to the specified address. Please make sure that the email address you are using is the same address you have provided and try again.');
    }
    else
        $msg = ('You must enter your email address.');
}
else if( @$_POST['verb'] == 'resetpw' )
{
    if($_POST['password'] == $_POST['confirm_password'] && !empty($_POST['password']))
    {
        $usersManager = new Users();
        if($usersManager->setPassword($_POST['key'], $_POST['password']))
        {
            $json['success'] = true;
            $msg = ('Your password has been changed. You may now <a href="/log-in/" title="Log In">log in.</a>');
        }
        else
            $msg = ('There was an error changing your password.');
    }
    else
        $msg = ('Passwords do not match.');
}
?>

<?php TemplateSet::begin('body') ?>

<?
if( !isset($_GET['key'] ) ){ ?>
    <h1>Forgot Password</h1>
    <p>Forgot your password? Enter the email address that you used to sign up below: </p>
    <p><strong><?=@$msg ?></strong></p>

    <?=XSRF::html()?>
    <div style="width:70%; margin: 0 auto;">
        <form class="signup-form" action="" method="post"  enctype="multipart/form-data">
            <input type="hidden" name="verb" value="forgotpw" />
            <table>
                <tr>
                    <td>
                        <?=AlertSet::html()?>
                    </td>
                </tr>
                <?
                if($showForm)
                {
                    ?>
                    <tr>
                        <td colspan="2"><input type="text" name="email" placeholder="Your Email"/></td>
                    </tr>
                    <tr>
                        <td style="width:50%;">&nbsp;</td>
                        <td><input type="submit" value="Submit" class="button" /></td>
                    </tr>
                <?
                }
                ?>
            </table>
        </form>
    </div>
<? }
else if( @$json['success'] != 'true' ) {
    ?>
    <?= (isset($_GET['setpw']))  ? '<h1>Set your password</h1>' : '<h1>Reset your password</h1>' ?>
    <p><strong><?=@$msg ?></strong></p>

    <div style="width:70%; margin: 0 auto;">
        <form class="signup-form" action="" method="post"  enctype="multipart/form-data">
            <input type="hidden" name="verb" value="resetpw" />
            <table>
                <tr>
                    <td colspan="2">
                        <input type="password" name="password" id="password" placeholder="New Password"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="password" name="confirm_password" id="confirm" placeholder="New Password Again"/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;">

                    </td>
                    <td>
                        <input type="hidden" name="key" id="key" value="<?=htmlentitiesUTF8($_GET['key'])?>" />
                        <input type="submit" value="Submit" />
                    </td>
                </tr>
            </table>
        </form>
    </div>

<?
}
else
{
    ?>
        <h1>Password Reset</h1>
        <p><strong><?=@$msg ?></strong></p>
    <?php
}
?>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>


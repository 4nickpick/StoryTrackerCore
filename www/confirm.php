<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');


$account_confirmed = false;
if( isset($_GET['key']) )
{
    $loader = new Users();
    if( $loader->confirmAccount(($_GET['key'])) )
    {
        $account_confirmed = true;
    }
}

?>

<?php TemplateSet::begin('body') ?>
	
   <?
   	if( !$account_confirmed ){ ?>
		<h1>Confirm Account</h1>
    <p>You have been sent an email with a link to confirm your account. You must confirm your account to continue. </p>
    <p><strong><?=@$msg ?></strong></p>

    <? } 
	else {
	?>
    	<h1>Account Confirmed!</h1>
        <p>
            Your account has been confirmed. You may now log in.
        </p>

        <h2>Log In</h2>
        <p>
            <form action="index.php" method="post" class="confirm-login-form" autocomplete="off">
                <input type="hidden" value="login" name="verb" />
                <?=XSRF::html()?>
                <table>
                    <tr>
                        <td colspan="2">
                            <input id="email" name="email" type="text" placeholder="Email Address" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input id="password" name="password" type="password" placeholder="Password" />
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%;">
                            &nbsp;
                        </td>
                        <td>
                            <input type="submit" value="Log In" />
                        </td>
                    </tr>
                </table>

            </form>
        </p>
    <?
	}
	?>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>




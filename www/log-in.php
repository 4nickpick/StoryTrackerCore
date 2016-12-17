<?php

/*

	Story Tracker

	By Nicholas Pickering

	Track, Search, and Optimize your Science Fiction/Fantasy story

*/

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

?>

<?php TemplateSet::begin('body') ?>
    <?php echo AlertSet::html()?>
    <div style="width:70%; margin:80px auto 0;">
        <form class="signup-form" action="" method="post">
            <?=XSRF::html()?>
            <input type="hidden" name="verb" value="login" />
            <h2>
                Log In
            </h2>
            <table>
                <tr>
                    <td colspan="2">
                        <input type="text" name="email" placeholder="Email"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="password" name="password" placeholder="Password"/>
                    </td>
                </tr>
                <tr>
                    <td style="width:50%;">
                        &nbsp;
                    </td>
                    <td>
                        <input type="submit" value="Log In" />
                        <a href="?forgotpw">Forgot your password?</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
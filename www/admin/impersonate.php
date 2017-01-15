<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
require_once('ip_secure.inc.php');

$userManager = new Users();

$users = $userManager->loadAll();

echo AlertSet::html();
?>
<h1>Impersonate User</h1>
<form method="post" action="">
    <input type="hidden" name="verb" value="impersonate" />
    <?php echo XSRF::html() ?>
    <select name="impersonate_users_id">
        <option value="">Select a customer...</option>
        <?php
        if(!empty($users)) {
            foreach($users as $user) {

                switch($user->get_account_type()) {
                    case UserAccountTypes::FULL_PAID:
                        $status = '$';
                        break;
                    case UserAccountTypes::LIFETIME_MEMBER:
                        $status = '*';
                        break;
                    case UserAccountTypes::LIMITED_UNPAID:
                        $status = '';
                        break;
                }
                ?>
                <option value="<?php echo $user->get_id() ?>"><?php echo $status . ' ' . $user->get_first_name() . ' ' . $user->get_last_name() . ' (' . $user->get_email() . ')'; ?></option>
                <?php
            }
        }
        ?>
    </select>

    <input type="submit" value="Impersonate"/>
</form>
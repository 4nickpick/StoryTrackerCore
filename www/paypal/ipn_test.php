<?php
$user_email = '2nickpick@gmail.com';
?>

<form target="_new" method="post" action="http://storytracker.local/paypal/ipn_subscribe.php">
    <input type="text" name="st_user_email" value="<?php echo $user_email ?>"/>
    <input type="hidden" name="txn_type" value="subscr_payment"/>

    <!-- code for other variables to be tested ... -->

    <input type="submit" value="Subscribe"/>
</form>

<br /><br />

<form target="_new" method="post" action="http://storytracker.local/paypal/ipn_subscribe.php">
    <input type="text" name="st_user_email" value="<?php echo $user_email ?>"/>
    <input type="hidden" name="txn_type" value="subscr_cancel"/>

    <!-- code for other variables to be tested ... -->

    <input type="submit" value="Cancel Subscription"/>
</form>

<br /><br />

<form target="_new" method="post" action="http://storytracker.local/paypal/ipn_subscribe.php">
    <input type="text" name="st_user_email" value="<?php echo $user_email ?>"/>
    <input type="hidden" name="txn_type" value="subscr_eot"/>

    <!-- code for other variables to be tested ... -->

    <input type="submit" value="End of Term"/>
</form>
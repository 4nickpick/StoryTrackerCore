<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

// define array to store PayPal request
// as key-value pair
$post_vars = array();

// read post from PayPal into local array
while (list ($key, $value) = each ($HTTP_POST_VARS)) {
    $post_vars[] = $key;
}

// add a 'cmd' parameter to the parameter list that is POSTed
// back, as required by PayPal
$req = 'cmd=_notify-validate';

// append each parameter posted by the PayPal
// as name value pair to the "req" variable
for ($var = 0; $var < count ($post_vars); $var++) {
    $post_var_key = $post_vars[$var];
    $post_var_value = $$post_vars[$var];
    $req .= "&" . $post_var_key . "=" . urlencode ($post_var_value);
}

// post the request back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen ($req) . "\r\n\r\n";

// open file pointer to the paypal server

$fp = fsockopen ("www.paypal.com", 80, $errno, $errstr, 30);
if (!$fp) {
    // HTTP error
    // log an error
} else {

    // POST the data using the file pointer created above
    fputs ($fp, $header . $req);

    while (!feof($fp)) {

        // read the response from the PayPal server
        $res = fgets ($fp, 1024);

        // check if the request has been VERIFIED by PayPal
        // if it is, then you can proceed further
        // if it is INVALID, then abort the process
        if (strcmp ($res, "VERIFIED") == 0) {

            // get the value stored in the "custom" field
            // (username) in a local variable
            $email = $HTTP_POST_VARS["st_user_email"];
            $user = (new Users())->exists($email);

            // check if the username sent with the PayPal IPN request exists in the database
            if((!empty($user)) && $user instanceof User ) {

                $userManager = new Users();

                // check the transaction type for the subscription sent by PayPal
                // and take action accordingly
                if(isset($HTTP_POST_VARS["txn_type"]) &&
                    strtolower($HTTP_POST_VARS["txn_type"]) == "subscr_payment") {

                    switch($user->get_account_type()) {
                        case UserAccountTypes::LIFETIME_MEMBER:
                            // well, this is a mistake
                            try {
                                UserController::sendMail(ERROR_REPORTING_EMAIL, '(ERROR) A Lifetime Member attempted to upgrade their account!', var_export($post_vars, true));
                            }
                            catch (Exception $e) {
                                throw new Exception("A lifetime member attempted to upgrade their account, and I couldn't notify you via email.". var_export($post_vars, true));
                            }
                            break;
                        case UserAccountTypes::LIMITED_UNPAID:
                            // we have a new paying customer!
                            try {
                                $user->add_account_type_change(UserAccountTypes::FULL_PAID, "Paypal IPN new customer automated");
                                UserController::sendMail(ERROR_REPORTING_EMAIL, '(PAYMENT) We have a new paid member!', var_export($post_vars, true));
                                $userManager->sendAccountTypeChangeNewMember($user->get_email());
                            }
                            catch (Exception $e) {
                                throw new Exception("A member attempted to upgrade to Pro Member, but something went wrong. I possibly couldn't notify you via email.". var_export($post_vars, true));
                            }

                            break;
                        case UserAccountTypes::FULL_PAID:
                            // we have a recurring customer, wowza!

                            try {
                                $user->add_account_type_change(UserAccountTypes::FULL_PAID, "Paypal IPN customer renewed account");
                                UserController::sendMail(ERROR_REPORTING_EMAIL, '(PAYMENT) We have a renewed subscribing member!', var_export($post_vars, true));
                                $userManager->sendAccountTypeChangeRenewed($user->get_email());
                            }
                            catch (Exception $e) {
                                throw new Exception("A member attempted to upgrade to Pro Member, but something went wrong. I possibly couldn't notify you via email." . var_export($post_vars, true));
                            }

                            break;
                        default:
                            // what the heck happened, they don't have an account type? need to investigate
                            try {
                                UserController::sendMail(ERROR_REPORTING_EMAIL, '(ERROR) A Member without an account type attempted to upgrade their account!', var_export($post_vars, true));
                            }
                            catch (Exception $e) {
                                throw new Exception("A lifetime member attempted to upgrade their account, and I couldn't notify you via email." . var_export($post_vars, true));
                            }
                    }

                } else if(isset($HTTP_POST_VARS["txn_type"]) &&
                    strtolower($HTTP_POST_VARS["txn_type"]) == "subscr_eot") {

                    // user did not pay their subscription fee
                    // log an error
                    try {
                        $user->add_account_type_change(UserAccountTypes::LIMITED_UNPAID, "Paypal IPN customer did not renew");
                        UserController::sendMail(ERROR_REPORTING_EMAIL, '(NO PAYMENT) Customer chose not to pay subscription fee', var_export($post_vars, true));
                        $userManager->sendAccountTypeChangeDowngraded($user->get_email());
                    } catch (Exception $e) {
                        throw new Exception("Customer chose not to pay subscription fee, and I couldn't notify you via email." . var_export($post_vars, true));
                    }
                } else if(isset($HTTP_POST_VARS["txn_type"]) &&
                        strtolower($HTTP_POST_VARS["txn_type"]) == "subscr_cancel") {

                        // user did not pay their subscription fee
                        // log an error
                        try {
                            $user->add_account_type_change(UserAccountTypes::LIMITED_UNPAID, "Paypal IPN customer cancelled their subscription");
                            UserController::sendMail(ERROR_REPORTING_EMAIL, '(NO PAYMENT) Customer actively cancelled their subscription', var_export($post_vars, true));
                            $userManager->sendAccountTypeChangeCancelled($user->get_email());
                        }
                        catch (Exception $e) {
                            throw new Exception("Customer actively cancelled their subscription, and something went wrong. " . var_export($post_vars, true));
                        }

                } else {

                    // incorrect transaction type
                    // log an error
                    try {
                        UserController::sendMail(ERROR_REPORTING_EMAIL, '(ERROR) Something tried to hit the Paypal IPN that was invalid!', var_export($post_vars, true));
                    }
                    catch (Exception $e) {
                        throw new Exception("Something tried to hit the Paypal IPN that was invalid, and I couldn't notify you via email." . var_export($post_vars, true));
                    }

                }

            }

        } else if (strcmp ($res, "INVALID") == 0) {

            // an INVALID transaction
            // log an error

            try {
                UserController::sendMail(ERROR_REPORTING_EMAIL, '(ERROR) Something weird happened in the Paypal IPN!', var_export($post_vars, true));
            }
            catch (Exception $e) {
                throw new Exception("Something weird happened in the Paypal IPN, and I couldn't notify you via email." . var_export($post_vars, true));
            }
        }

    }

}
<?php
error_reporting(-1);
ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

ErrorSet::$display = true;

class PaypalIPN
{
    /**
     * @var bool $use_sandbox     Indicates if the sandbox endpoint is used.
     */
    private $use_sandbox = false;
    /**
     * @var bool $use_local_certs Indicates if the local certificates are used.
     */
    private $use_local_certs = true;

    /** Production Postback URL */
    const VERIFY_URI = 'https://ipnpb.paypal.com/cgi-bin/webscr';
    /** Sandbox Postback URL */
    const SANDBOX_VERIFY_URI = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';


    /** Response from PayPal indicating validation was successful */
    const VALID = 'VERIFIED';
    /** Response from PayPal indicating validation failed */
    const INVALID = 'INVALID';

    /** Response from PayPal indicating validation failed */
    private $myPost = [];


    /**
     * Sets the IPN verification to sandbox mode (for use when testing,
     * should not be enabled in production).
     * @return void
     */
    public function useSandbox()
    {
        $this->use_sandbox = true;
    }

    /**
     * Sets curl to use php curl's built in certs (may be required in some
     * environments).
     * @return void
     */
    public function usePHPCerts()
    {
        $this->use_local_certs = false;
    }


    /**
     * Determine endpoint to post the verification data to.
     * @return string
     */
    public function getPaypalUri()
    {
        if ($this->use_sandbox) {
            return self::SANDBOX_VERIFY_URI;
        } else {
            return self::VERIFY_URI;
        }
    }


    /**
     * Verification Function
     * Sends the incoming post data back to PayPal using the cURL library.
     *
     * @return bool
     * @throws Exception
     */
    public function verifyIPN()
    {
        if ( ! count($_POST)) {
            throw new Exception("Missing POST Data");
        }

        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);

        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                // Since we do not want the plus in the datetime string to be encoded to a space, we manually encode it.
                if ($keyval[0] === 'payment_date') {
                    if (substr_count($keyval[1], '+') === 1) {
                        $keyval[1] = str_replace('+', '%2B', $keyval[1]);
                    }
                }
                $this->myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        // Build the body of the verification post request, adding the _notify-validate command.
        $req = 'cmd=_notify-validate';
        $get_magic_quotes_exists = false;
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($this->myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Post the data back to PayPal, using curl. Throw exceptions if errors occur.
        $ch = curl_init($this->getPaypalUri());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // This is often required if the server is missing a global cert bundle, or is using an outdated one.
        if ($this->use_local_certs) {
            curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "/cert/cacert.pem");
        }
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);
        $res = curl_exec($ch);
        if ( ! ($res)) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: [$errno] $errstr");
        }

        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
        if ($http_code != 200) {
            throw new Exception("PayPal responded with http code $http_code");
        }

        curl_close($ch);

        // Check if PayPal verifies the IPN data, and if so, return true.
        if ($res == self::VALID) {
            return true;
        } else {
            return false;
        }
    }

    function handlePaypalRequest() {

        $userManager = (new Users());

        // get the value stored in the "custom" field
        // (username) in a local variable
        $post_vars = $this->myPost;
        $email = $post_vars["st_user_email"];
        $user_id = $userManager->exists($email);

        if(is_int($user_id) && intval($user_id) > 0) {
            $user = $userManager->loadById($user_id);
        }

        // check if the username sent with the PayPal IPN request exists in the database
        if((!empty($user)) && $user instanceof User ) {

            $userManager = new Users();

            // check the transaction type for the subscription sent by PayPal
            // and take action accordingly
            if(isset($post_vars["txn_type"]) &&
                strtolower($post_vars["txn_type"]) == "subscr_payment") {

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

            } else if(isset($post_vars["txn_type"]) &&
                strtolower($post_vars["txn_type"]) == "subscr_eot") {

                // user did not pay their subscription fee
                // log an error
                try {
                    if($user->get_account_type() != UserAccountTypes::LIMITED_UNPAID) {
                        $user->add_account_type_change(UserAccountTypes::LIMITED_UNPAID, "Paypal IPN customer did not renew");
                        UserController::sendMail(ERROR_REPORTING_EMAIL, '(NO PAYMENT) Customer chose not to pay subscription fee', var_export($post_vars, true));
                        $userManager->sendAccountTypeChangeDowngraded($user->get_email());
                    }
                    else {
                        UserController::sendMail(ERROR_REPORTING_EMAIL, '(ERROR) (NO PAYMENT) An EOT occurred, but the user was already a LIMITED_UNPAID', var_export($post_vars, true));
                    }
                } catch (Exception $e) {
                    throw new Exception("Customer chose not to pay subscription fee, and I couldn't notify you via email." . var_export($post_vars, true));
                }
            } else if(isset($post_vars["txn_type"]) &&
                strtolower($post_vars["txn_type"]) == "subscr_cancel") {

                // user did not pay their subscription fee
                // log an error
                try {
                    if($user->get_account_type() != UserAccountTypes::LIMITED_UNPAID) {
                        $user->add_account_type_change(UserAccountTypes::LIMITED_UNPAID, "Paypal IPN customer cancelled their subscription");
                        UserController::sendMail(ERROR_REPORTING_EMAIL, '(NO PAYMENT) Customer actively cancelled their subscription', var_export($post_vars, true));
                        $userManager->sendAccountTypeChangeCancelled($user->get_email());
                    }
                    else {
                        UserController::sendMail(ERROR_REPORTING_EMAIL, '(ERROR) (NO PAYMENT) User intended to cancel their membership, but they were LIMITED_UNPAID', var_export($post_vars, true));
                    }
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
    }
}

$pp = new PaypalIPN();
$pp->useSandbox();

$verified = $pp->verifyIPN();

if($verified || true) {
    $pp->handlePaypalRequest();
}

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
header("HTTP/1.1 200 OK");
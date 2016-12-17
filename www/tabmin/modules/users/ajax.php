<?php
include($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');
if(@$_POST['verb']!='forgotpw' && @$_POST['verb']!='resetpw' && @$_POST['verb']!='sign-up'&& @$_POST['verb']!='contact' && @$_POST['verb']!='feedback')
	include INCLUDE_ROOT.'/ajax_secure.inc.php';
ErrorSet::setAJAX(true);
$properties = array
				(
					'id'=>@$_POST['users_id'],
					'role'=>array
					(
						'id'=>@$_POST['roles_id']
					),
					'email'=>trim(@$_POST['email']),
					'first_name'=>trim(@$_POST['first_name']),
					'last_name'=>trim(@$_POST['last_name']),
					'password'=>@$_POST['password'],
					'password2'=>@$_POST['password2'],
					'nick_name'=>@$_POST['nick_name'],
                    'referral'=>@$_POST['referral'],
					'phone'=>@$_POST['phone'],
					'address'=>@$_POST['address'],
					'city'=>@$_POST['city'],
					'state'=>@$_POST['state'],
					'zip'=>@$_POST['zip']
				);

if (isset($_FILES['picture']))
	$properties['picture_file'] = @$_FILES['picture'];
$controller = new UserController($properties, $currentUser, @$module);

$controller->setAllRequired(array(
		'first_name'=>'First Name',
		'password'=>'Password',
		'password2'=>'Repeated Password',
		'email'=>'Email',
		));

$module = 'users';
switch(@$_POST['verb'])
{
	case 'add':			
		$controller->add();
	break;
	case 'edit':		
		$controller->removeRequired('password');
		$controller->removeRequired('password2');	
		$controller->update();
	break;
	case 'member_edit':
		$controller->removeRequired('password');
		$controller->removeRequired('password2');

        $controller->removeProperty('address');
        $controller->removeProperty('city');
        $controller->removeProperty('state');
        $controller->removeProperty('zip');
        $controller->removeProperty('phone');
        $controller->removeProperty('nick_name');
        $controller->removeProperty('role');

        $controller->update();
	break;
	case 'delete':			
		$controller->delete();
	break;
	case 'sign-up':
		$controller->setCheckPermissions(false);

        $resp = recaptcha_check_answer(RECAPTCHA_PRIVATE, $_SERVER['REMOTE_ADDR'],$_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
        if( !$resp->is_valid )
            AlertSet::addValidation('Security code was entered incorrectly.');

		if( AlertSet::$success && $controller->signUp() )
        {
            AlertSet::$success = true;
            mail(ERROR_REPORTING_EMAIL, 'New User Sign Up - ' . $_POST['email'], 'Email: ' . $_POST['email'] .
                ' -- First Name: ' . $_POST['first_name'] . ' -- Referral: ' . $_POST['referral'] . ' -- ID: ' . $controller->get_tobject()->get_id(), true);
        }
	break;
	case 'contact':

        $resp = recaptcha_check_answer(RECAPTCHA_PRIVATE, $_SERVER['REMOTE_ADDR'],$_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
        if( !$resp->is_valid )
            AlertSet::addValidation('Security code was entered incorrectly.');

        if( AlertSet::$success )
        {
            $usersManager = new Users();

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: ' . WEBSITE_EMAIL ."\r\n";

            $html = '<strong>Request Access: </strong>' .  htmlentitiesUTF8(@$_POST['request_access']) . '<br />';
            $html .= '<strong>Name: </strong>' .  htmlentitiesUTF8($_POST['thename']) . '<br />';
            $html .= '<strong>Email: </strong>' .  htmlentitiesUTF8($_POST['email']). '<br />';
            $html .= '<strong>Message: </strong>' .  htmlentitiesUTF8(@$_POST['message']). '<br />';
            $html .= '<strong>Request: </strong>' .  htmlentitiesUTF8(@$_POST['request']). '<br />';
            if( mail(ERROR_REPORTING_EMAIL, 'StoryTracker Contact Form Submission', $html, $headers) )
            {
                AlertSet::addSuccess('Thank you for your request. You should expect an email response from us shortly.
                    In the meantime, please share us on Facebook and follow us on Twitter using the social media buttons above! ');
            }
            else
            {
                AlertSet::addError('Your message could not be sent at this time.');
            }
        }

        $json['success'] = AlertSet::$success;
        $json['alerts'] = AlertSet::$alerts;

        echo json_encode($json);
        die();
	break;
	case 'feedback':

        $resp = recaptcha_check_answer(RECAPTCHA_PRIVATE, $_SERVER['REMOTE_ADDR'],$_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
        if( !$resp->is_valid )
            AlertSet::addValidation('Security code was entered incorrectly.');

        if( AlertSet::$success )
        {
            $usersManager = new Users();

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: ' . WEBSITE_EMAIL ."\r\n";

            $html = '<strong>Name: </strong>' .  htmlentitiesUTF8($_POST['name']) . '<br />';
            $html .= '<strong>Email: </strong>' .  htmlentitiesUTF8($_POST['email']) . '<br />';
            $html .= '<strong>Encounter any Problems?: </strong>' .  htmlentitiesUTF8($_POST['problems']) . '<br />';
            $html .= '<strong>Plan to use StoryTracker?: </strong>' .  htmlentitiesUTF8($_POST['opinion']). '<br />';
            $html .= '<strong>Questions/Comments: </strong>' .  htmlentitiesUTF8($_POST['comments']). '<br />';
            if( mail(ERROR_REPORTING_EMAIL, 'StoryTracker Feedback Form Submission', $html, $headers) )
            {
                AlertSet::addSuccess('Your message has been received.');
            }
            else
            {
                AlertSet::addError('Your message could not be sent at this time.');
            }
        }

        $json['success'] = AlertSet::$success;
        $json['alerts'] = AlertSet::$alerts;

        echo json_encode($json);
        die();
	break;
	case 'forgotpw':			
		$controller->forgotPassword();
	break;
	case 'resetpw':
		$controller->addProperty('key', $_POST['key']);			
		$controller->resetPassword();
	break;
	case 'export':
        $properties = array(
            'id'=>@$currentUser->get_id(),
        );
        $controller = new UserController($properties, $currentUser, @$module);
		$controller->export();
	break;
	case 'bug-report':
        $properties = array(
            'user'=>array('id'=>$currentUser->get_id()),
            'current_page'=>$_POST['current_page'],
            'browser'=>$_POST['browser'],
            'problem'=>$_POST['problem']
        );

        $bug_report = new BugReport($properties);

        if( $bug_report->add() )
        {
            AlertSet::addSuccess('Bug Report added successfully.');
        }
        else
        {
            AlertSet::addError('There was a problem submitting your Bug Report. Please Try Again.');
        }

	break;
    default:
        AlertSet::addError('Verb not recognized.');
    break;
}


echo $controller->get_json();
?>
<?
class UserController extends Controller
{
	
	protected function loadTobject($properties)
	{
		$this->tobject = new User($properties);
	}
	
	public function add()
	{
        if ($this->properties['email'] != $this->properties['email2'])
            AlertSet::addValidation('Email must match');
		if ($this->properties['password'] != $this->properties['password2'])
			AlertSet::addValidation('Password must match');
        else if(strlen($this->properties['password']) < 10)
            AlertSet::addValidation('Password must be at least 10 characters.');

        $this->tobject->set_role(3);
        if( AlertSet::$success )
		    parent::add();
	}

    public function signUp()
    {
        if ($this->properties['password'] != $this->properties['password2'])
            AlertSet::addValidation('Password must match');
        else if(strlen($this->properties['password']) < 10)
            AlertSet::addValidation('Password must be at least 10 characters.');

        $loader = new Users();
        if( $loader->exists($this->properties['email']))
        {
            AlertSet::addValidation("That email address is already in use.
                If you already have an account, please use the \"Forgot Password\" link.");
        }

        $this->tobject->set_role(3);
        if( AlertSet::$success ) {
            if( parent::add() )
            {
                $this->sendAccountConfirmationEmail();
                return AlertSet::$success;
            }
        }

        return false;
    }

    public function update()
	{		
		if (!empty($this->properties['password']) && ($this->properties['password'] != $this->properties['password2']))
			AlertSet::addValidation('Password must match');
        else if(!empty($this->properties['password']) && strlen($this->properties['password']) < 10)
            AlertSet::addValidation('Password must be at least 10 characters.');
		if (empty($this->properties['nick_name']))
		{
			$this->removeProperty('nick_name', true);				
		}
		$this->processPicture();	
		
		if( parent::update() )
        {
            AlertSet::clear();
            AlertSet::addSuccess('Profile edited successfully.');
        }
	}

    public function forgotPassword()
    {
        $this->sendSecurityEmail('forgot_password');
    }

    public function sendAccountConfirmationEmail()
	{
        $this->sendSecurityEmail('confirmation');
	}

    private function sendSecurityEmail($verb)
    {
        if(!empty($this->properties['email']))
        {
            $usersManager = new Users();

            $success = false;
            $success_message = '';
            switch($verb)
            {
                case 'forgot_password':
                    $success = $usersManager->sendforgotPasswordLink($this->properties['email']);
                    $success_message = 'You have been sent an email from '.SITE_NAME.' with further instructions. Please follow the link in the email to reset your password. If the email does not appear in your inbox, please check any spam folders.';
                    break;
                case 'confirmation':
                    $success = $usersManager->sendConfirmAccountLink($this->properties['email']);
                    $success_message = 'You have been sent an email from '.SITE_NAME.' with further instructions. Please follow the link in the email to confirm your account. If the email does not appear in your inbox, please check any spam folders.';
                    break;
            }
            if($success)
            {
                $this->json['success'] = true;
                AlertSet::addSuccess($success_message);
            }
            else
                AlertSet::addError('We were not able to send the email to the specified address. Please make sure that the email address you are using is the same address you have provided and try again.');
        }
        else
            AlertSet::addValidation('You must enter your email address.');
    }


    public function confirmAccount()
    {
        if(!empty($this->properties['email']))
        {
            $usersManager = new Users();
            if($usersManager->sendConfirmAccountLink($this->properties['email']))
            {
                $this->json['success'] = true;
                AlertSet::addSuccess('You have been sent an email from '.SITE_NAME.' with further instructions. Please follow the link in the email to confirm your account. If the email does not appear in your inbox, please check any spam folders.');
            }
            else
                AlertSet::addError('We were not able to send the email to the specified address. Please make sure that the email address you are using is the same address you have provided and try again.');
        }
        else
            AlertSet::addValidation('You must enter your email address.');
    }
	
	public function forgotPasswordAdmin()
	{
		$usersManager = new Users();
		$user = $usersManager->loadById(intval($this->properties['id']));
		if(!empty($user->email))
		{
			if($usersManager->sendforgotPasswordLink($user->email))
			{
				$this->json['success'] = true;
				AlertSet::addSuccess('You have sent a forgot password email to this user.');
			}
			else
				AlertSet::addError('We were not able to send the email to the specified address. Please make sure that the email address you are using is valid and try again.');
		}
		else
			AlertSet::addValidation('User must have an email address to send forgot password link.');
		
	}
	
	public function resetPassword()
	{
		if($this->properties['password'] == $this->properties['password2'] && !empty($this->properties['password']))
		{
			$usersManager = new Users();
			if($usersManager->setPassword($this->properties['key'], $this->properties['password']))
			{
				$this->json['success'] = true;
				AlertSet::addSuccess('Your password has been changed. You may now log in.');
			}
			else
				AlertSet::addError('There was an error changing your password.');
		}
		else
			AlertSet::addError('Passwords do not match.');
	}

    public function export()
    {
        return $this->get_tobject()->export();
    }
	
}
?>
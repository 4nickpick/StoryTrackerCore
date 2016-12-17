<?php

global $currentUser, $currentStory, $modules;

$is_dev = getenv('HOME') == 'C:\Users\thenickpick' ;

error_reporting(-1);
ini_set('display_errors', false);

#define('MOBILE_SITE_URL', 'm.site_name.com');
define('LOGGED_IN_HOME', '../stories/list/');

if(!isset($config_exclusions))
	$config_exclusions=array('wordpress');

if(!in_array('session', $config_exclusions))
	session_start();
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('America/New_York'); 

if( $is_dev )
    $config_xml_file = '/config-dev.xml';

if(empty($config_xml_file))
	$config_xml_file=dirname(dirname(__FILE__)).'/config.xml';
else
	$config_xml_file=dirname(dirname(__FILE__)).'/'.$config_xml_file;
$config_xml = simplexml_load_file($config_xml_file);


foreach($config_xml->constant as $constant)
	define((string)$constant['name'], (string)$constant);
define('MODULE_ROOT_TRANSLATED', '/'.str_replace(DOCUMENT_ROOT, '', MODULE_ROOT));

foreach($config_xml->class as $class)
	require_once(CLASS_ROOT.$class['file']);
define('TABMIN', (preg_match('/^\/tabmin\//', $_SERVER['PHP_SELF'])!=0));


if(!in_array('wordpress', $config_exclusions))
{
	if(!defined('WP_USE_THEMES') && defined('WP_ROOT') && !TABMIN)
	{
		define('WP_USE_THEMES', false);
		require WP_ROOT.'wp-load.php';
	}
}

require_once GLOBAL_ROOT.'classes/ErrorSet.inc.php';
require_once GLOBAL_ROOT.'classes/Tabmin.inc.php';
require_once GLOBAL_ROOT.'classes/AlertSet.inc.php';
require_once GLOBAL_ROOT.'classes/Validator.inc.php';
require_once GLOBAL_ROOT.'classes/Format.inc.php';
require_once GLOBAL_ROOT.'classes/Display.inc.php';
require_once GLOBAL_ROOT.'classes/XSRF.inc.php';
require_once GLOBAL_ROOT.'classes/TemplateSet.inc.php';
require_once GLOBAL_ROOT.'util.inc.php';
require_once GLOBAL_ROOT.'pdo.inc.php';
require_once GLOBAL_ROOT.'recaptcha.inc.php';
require_once GLOBAL_ROOT.'class_sendmail.inc.php';
require_once GLOBAL_ROOT.'classes/XMLMailThing.inc.php';

try
{
	Tabmin::$db = new PDO('mysql:host='.TABMIN_DB_HOST.';dbname='.TABMIN_DB_NAME.';charset=utf8', TABMIN_DB_USERNAME, TABMIN_DB_PASSWORD);
	Tabmin::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	Tabmin::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}
catch (Exception $e)
{
    //var_dump($e);
	die('can not create db connection');
	//error handler here
}

ErrorSet::$display=$is_dev;
ErrorSet::$email=NULL;

if(get_magic_quotes_gpc())
{
	$_GET=stripslashes_deep($_GET);
	$_POST=stripslashes_deep($_POST);
	$_COOKIE=stripslashes_deep($_COOKIE);
}
$usersManager = new Users();
if(@$_POST['verb']=='login')
{
	$_SESSION['done'] = true;	
	if(XSRF::valid())
	{
		$user = $usersManager->login($_POST['email'], $_POST['password']);
		
		if($user)
		{
			
			$logged_in=true;
			$_SESSION['tabmin_users_id'] = $user->get_id();

			if($user->get_role()->get_developer())
				$_SESSION['tabmin_developer']=1;

            header('Location: ' . LOGGED_IN_HOME);
            exit();
		}
		else
		{
			$logged_in=false;
			$login_failed_msg='<small style="color:red;">Login failed.</small>';
			AlertSet::addError("Login failed.");
            AlertSet::save();
            header('Location: /log-in/');
            exit();
		}		
	}
	else
		AlertSet::addError(XSRF::GENERIC_ERROR);
		
}
if(@$_POST['verb']=='switch-user')
{
	if(@$_SESSION['tabmin_developer'])
	{		
		$_SESSION['tabmin_users_id']=intval($_POST['users_id']);
	}
}

if(isset($_GET['logout']))
{
	unset($_SESSION['currentStory']);
	unset($_SESSION['tabmin_users_id']);
	unset($_SESSION['done']);
	unset($_SESSION['tabmin_developer']);
	AlertSet::addSuccess('You have been logged out');
	header('Location: /feedback');
	exit();
}

if(isset($_GET['forgotpw']))
{	
	header('Location: ../forgot-pw.php');
	exit();
}

if(!empty($_SESSION['tabmin_users_id']))
{
	if($currentUser=$usersManager->loadById($_SESSION['tabmin_users_id']))
	{
		$permissionsManager = new Permissions();
		$permissionsManager->PermissionsByRole($currentUser->get_role()->get_id(), @$_SESSION['tabmin_developer']);
		$currentUser->set_permissions($permissionsManager);
	}
}

if(!empty($currentUser))
{
	$modules=Tabmin::load($config_xml->modules, $currentUser);
	$module=false;
	$tab=false;
	
	$module_name=preg_replace('/^(.*?\/)+/', '', dirname($_SERVER['SCRIPT_NAME']));
	foreach($modules as $a_module)
	{
		if($a_module->name==$module_name)
		{
			$module=$a_module;
			break;
		}
	}
	
	if($module)
	{
		$tab_name=basename($_SERVER['SCRIPT_NAME']);
		foreach($module->tabs as $a_tab)
		{
			if($a_tab->file==$tab_name)
			{
				$tab=$a_tab;
				break;
			}
		}
	}
    else
    {
        if( DEBUG )
            Console::add('Not in a module: Make sure config.xml is setup properly and permissions have been set.');
    }

    $storiesLoader = new Stories();
    $storiesCount = $storiesLoader->getCountByUser($currentUser->get_id());


    //kick users to story module if they have no stories
    if( $storiesCount <= 0 )
    {
        if(
            ( $module->name != 'stories' && $module->name != 'users' ) ||
            ( $module->name == 'stories' && (!in_array(@$tab->name, array('add', 'preview', 'ajax', NULL, ''))) ) ||
            ( $module->name == 'users' && (!in_array(@$tab->name, array('member_edit')) && !in_array(@$_POST['verb'], array('member_edit', 'export', 'bug-report'))))
        )
        {
            AlertSet::addValidation('You must have at least one story to work with other modules.');
            Console::add('You must have at least one story to work with other modules.');
            AlertSet::save();

            $location = '/stories/preview';
            header('Location: ' . $location);
            exit();
        }
    }

    $currentStory = NULL;
    if( isset($_SESSION['currentStory']) )
        $currentStory = $storiesLoader->loadById($_SESSION['currentStory']);

    if( $currentStory == NULL )
    {
        if(
            ( $module->name != 'stories' && $module->name != 'users' ) ||
            ( $module->name == 'stories' && (!in_array(@$tab->name, array('add', 'preview', 'ajax', 'list', NULL, ''))) ) ||
            ( $module->name == 'users' && (!in_array(@$tab->name, array('member_edit')) && !in_array(@$_POST['verb'], array('member_edit', 'export', 'bug-report'))) )
        )
        {
            AlertSet::addValidation('You must select a story to continue.');
            Console::add('You must select a story to continue.');
            AlertSet::save();

            $location = '/stories/list/';
            header('Location: ' . $location);
            exit();
        }
    }
}
?>

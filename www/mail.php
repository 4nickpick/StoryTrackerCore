<?php

ini_set('display_errors', true);
require('../private/includes/config.inc.php');

//$headers  = 'MIME-Version: 1.0' . "\r\n";
//$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
//$headers .= 'From: ' . WEBSITE_EMAIL ."\r\n";
//
//$html = '<strong>Name: </strong>Nick<br />';
//$html .= '<strong>Email: </strong>npickering@gmail.com<br />';
//$html .= '<strong>Encounter any Problems?: </strong>99 problems<br />';
//$html .= '<strong>Plan to use StoryTracker?: </strong>Yup<br />';
//$html .= '<strong>Questions/Comments: </strong>No questions<br />';
//
//if( mail('4nickpick@gmail.com', 'StoryTracker Feedback Form Submission', $html, $headers) )
//{
//    echo 'Your message has been received.';
//}
//else
//{
//    echo 'Your message could not be sent at this time.';
//}

//$userController = new UserController(array('email'=>'4nickpick@gmail.com'), null, 'users');
//$sent = $userController->sendAccountConfirmationEmail();
//var_export(AlertSet::$alerts);


UserController::sendMail('4nickpick@gmail.com', 'This is my test. 1', 'Here is a body');


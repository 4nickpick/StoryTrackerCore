<?php

/* 

	Story Tracker

	By Nicholas Pickering
	
	Track, Search, and Optimize your Science Fiction/Fantasy story

*/	

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

$beta_requests_open = true;
$registrations_open = false;

if( isset($currentUser) )
{
    header('Location: /stories/list');
    exit();
}
?>

<?php TemplateSet::begin('banner') ?>
    <div id="banner"></div>
<?php TemplateSet::end() ?>

<?php TemplateSet::begin('title') ?> - Plot and Character Development Application for Fiction Writers<?php TemplateSet::end() ?>

<?php TemplateSet::begin('body') ?>

    <div class="social_media">
        <div class="fb-like" data-href="http://storytracker.net" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>

        <a href="https://twitter.com/thenickpick" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @thenickpick</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    </div>

    <div class="clear"></div>

    <div class="headline">
        <h1 class="tagline">Complete Story Organization Tool for Fiction Writers</h1>
        <h4>Never lose another detail of your creative world again...</h4>
    </div>

    <div class="home-left with-background">

        <h2>
            What is Story Tracker?
        </h2>
        <p>
            StoryTracker is a character and plot management tool, designed to help fiction authors get their stories organized
            in a professional, useful way.
        </p>
        <p>
            The benefits of using Story Tracker include:
        </p>

        <ul id="benefits">
            <li>Quickly explore your characters' histories, motives, and relationships</li>
            <li>Trace plot lines to find holes, clichés or inconsistencies</li>
            <li>Complete Privacy. None of your work is accessible to other users and we will never share any of your account data or work with anyone.</li>
            <li>A simple, intuitive interface that helps you find what you need quickly. </li>
            <li class="green">A better final product when your work is ready to be released.</li>
        </ul>

        <p>
            All accounts are <strong>100% private</strong>.
        </p>
        <p>
            Backups of your data are taken <strong>daily</strong>, so sleep easy.
        </p>

        <h2>
            Join Today
        </h2>
        <p>
            Story Tracker is currently in <strong>closed beta</strong> phase.
            Fill out the form to the right to start using Story Tracker today, and get special treatment
            when we open our doors to the public. See you there!
        </p>
        <p>
            Have a question or concern before trying out Story Tracker? Send us a message on our <a href="/contact">Contact page</a>
        </p>

    </div>

    <div class="home-right with-background">
        <?php
        if( $beta_requests_open )
        {
            ?>
            <h2>Request Access to Story Tracker Beta</h2>
            <p>Fill out the simple form below and we'll get back to you soon!</p>
            <form class="contact-form" action="/tabmin/modules/users/ajax.php" method="post" onsubmit="return handleAjaxForm(this, function(resp){
        AlertSet.addJSON(resp).add(
            new AlertSet.Button('Back to Home', function() {
                goTo('/');
            })
        ).show();
        }, function(resp) {AlertSet.addJSON(resp).show();})" autocomplete="off" enctype="multipart/form-data">
                <?=XSRF::html()?>
                <input type="hidden" name="verb" value="contact" />
                <input type="hidden" name="request_access" value="true" />
                <table>
                    <tr>
                        <td>
                            <input type="text" name="thename" placeholder="First Name*"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="email" placeholder="Your Email Address*"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="recaptcha" colspan="2">
                            <div class="recaptcha_wrapper">
                                <?=recaptcha_get_html(RECAPTCHA_PUBLIC);?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>
                                We will never sell your email address or send you spam.
                                <a href="/privacy" target="_blank">View our Privacy Policy</a>
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                        <td>
                            <input class="call-to-action" type="submit" value="Let's Get Started! &raquo;" />
                        </td>
                    </tr>
                </table>
            </form>
        <?php
        }
        else if( $registrations_open )
        {
            ?>
            <form class="signup-form" action="/tabmin/modules/users/ajax.php" method="post" onsubmit="return handleAjaxForm(this, function(resp){ location.href='/confirm'; }, function(resp) {AlertSet.addJSON(resp).show();})" autocomplete="off" enctype="multipart/form-data">
                <?=XSRF::html()?>
                <input type="hidden" name="verb" value="sign-up" />
                <table>
                    <tr>
                        <td>
                            <input type="text" name="first_name" placeholder="First Name"/>
                        </td>
                        <td>
                            <input type="text" name="last_name" placeholder="Last Name"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="text" name="email" placeholder="Your Email"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="text" name="email2" placeholder="Re-enter Email"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="password" name="password" placeholder="New Password"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="password" name="password2" placeholder="Re-enter Password"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="recaptcha" colspan="2">
                            <div class="recaptcha_wrapper">
                                <?=recaptcha_get_html(RECAPTCHA_PUBLIC);?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                        <td>
                            <input type="submit" value="Sign Up!" />
                        </td>
                    </tr>
                </table>
            </form>
        <?php
        }
        else
        {
            echo '<div class="AlertSet_info"><ul><li>Interested in participating in our beta? Submit a request through <a href="/contact">our contact form.</a></li></ul></strong></p>';
        }
        ?>
    </div>

<?php TemplateSet::end() ?>

<?php TemplateSet::begin('scripts_footer') ?>

    <script src="/js/jssor-slideshow/js/jssor.slider.mini.js"></script>
    <script>
        jQuery(document).ready(function ($) {
            var options = {
                $AutoPlay: true,
                $SlideDuration: 1000,
                $FillMode: 2,
                $BulletNavigatorOptions: {
                    $Class: $JssorBulletNavigator$,
                    $ChanceToShow: 2
                }
            };
            var jssor_slider1 = new $JssorSlider$('slider1_container', options);
        });
    </script>

<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
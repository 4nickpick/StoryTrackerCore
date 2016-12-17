<?php

/* 

	Story Tracker

	By Nicholas Pickering
	
	Track, Search, and Optimize your Science Fiction/Fantasy story

*/

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

$beta_requests_open = false;
$registrations_open = true;

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
            <li class="green">A better final product when your work is ready to be released.</li>
        </ul>

        <br />
        <h2>
            Your Work is Safe with Us!
        </h2>

        <p>
            All of your account data is <strong>100% private</strong>. You own all the content you provide to Story Tracker,
            and are the only user who has access to that content. We will never misuse or abuse your content.
        </p>
        <p>
            Backups of your data are taken <strong>daily</strong>. This means if there's an utter catastrophe,
            we'll be able to restore your data from the day before. We intend to increase the frequency of these backups as we grow larger.
        </p>
        <p>
            If Story Tracker ever kicks the bucket, we'll provide a minimum of two weeks notice so that you can properly
            move your data out of our system. A tool has been provided once you log in to download an archive of all your account data.
        </p>
        <p>
            You may wish to review our <a href="/privacy">Privacy Policy</a> for more information. If you have a specific question
            about how your data is used, feel free to <a href="/contact">Contact Us</a>.
        </p>

        <h2>
            Join Us Now and Become an Honorary Member
        </h2>
        <p>
            Story Tracker is currently in <strong>open beta</strong> phase.
        </p>
        <p>
            <a name="pro"></a>
            Users who sign up before we transition out of beta, will <strong>become Honorary Members, with a free lifetime Pro account.</strong>
        </p>
        <p>
            When Story Tracker transitions out of beta into our first release, we intend to follow a <strong>freemium</strong> model.
            Anyone will be able to add their first story, with access to all of our features, completely for free. Users can then upgrade to a Pro
            account (pricing to be determined) to add as many stories as they want.
        </p>
        <p>
            Honorary Members will be given a lifetime Pro account, as thanks for helping us shape our future. We greatly appreciate your feedback and responses.
        </p>
        <p>
            Fill out the form to the right to start using Story Tracker now!
        </p>
        <p>
            Have a question or concern before trying out Story Tracker? Send us a message on our <a href="/contact">Contact Us</a> page.
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
            <h2>Register For Your Free Account</h2>
            <p>Sign up now and become an Honorary Member, with a <a href="#pro">free lifetime Story Tracker Pro account</a>!</p>
            <p><strong>* denotes required</strong></p>
            <form class="contact-form" action="/tabmin/modules/users/ajax.php" method="post" onsubmit="return handleAjaxForm(this, function(resp){ location.href='/confirm'; }, function(resp) {AlertSet.addJSON(resp).show();})" autocomplete="off" enctype="multipart/form-data">
                <?=XSRF::html()?>
                <input type="hidden" name="verb" value="sign-up" />
                <table>
                    <tr>
                        <td>
                            <input type="text" name="first_name" placeholder="First Name*"/>
                        </td>
                    <tr>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="last_name" placeholder="Last Name"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="email" placeholder="Your Email*" autocomplete="off"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" name="password" placeholder="New Password* 10 character min" autocomplete="off" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" name="password2" placeholder="Re-enter Password*" autocomplete="off"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="referral" placeholder="How did you hear about us?" autocomplete="off"/>
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
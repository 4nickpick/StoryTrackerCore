<?php

/* 

	Story Tracker

	By Nicholas Pickering
	
	Track, Search, and Optimize your Science Fiction/Fantasy story

*/	

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

$registrations_open = true;

if( isset($currentUser) )
{
    header('Location: /stories/list');
    exit();
}
?>

<?php TemplateSet::begin('body') ?>

    <div class="social_media">
        <div class="fb-like" data-href="http://storytracker.net" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>

        <a href="https://twitter.com/thenickpick" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @thenickpick</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    </div>

    <div class="clear"></div>


    <div class="home-left">

        <div class="slideshow_container" style="background-color: rgba(0,0,0,.8); padding: 10px;">
            <div class="slideshow" style="width: 50%;">
                <div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 600px; height: 300px;">
                    <div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 580px; height: 300px;">
                        <div><img u="image" src="/images/slides/character.png" /></div>
                        <div><img u="image" src="/images/slides/relationships.png" /></div>
                    </div>
                </div>
            </div>
        </div>
        <br />
        <h2>
            Write Your Story
        </h2>
        <p>
            StoryTracker is a character and plot management tool, designed for fiction authors to easily find
            plot holes and character inconsistencies during any phase of the writing process.
        </p>

        <h3>
            Why Story Tracker?
        </h3>
        <p>
            Use StoryTracker to manage all of the data that makes up your story in a quick, <em>searchable</em>
            way - so that you never lose a piece of content in the abyss of folders on your desktop, or notebooks
            on your shelf.
        </p>

        <h3>
            We Need You!
        </h3>
        <p>
            StoryTracker needs authors willing to beta test and give their honest opinions about us!<br />
            Interested in helping out? Submit a simple request through <a href="/contact/">our contact form</a>, or contact me
            via <a target="_blank" href="http://twitter.com/thenickpick">Twitter</a>.
        </p>

        <p>
            If you have questions or concerns, <a href="/contact/">contact us</a>.
        </p>
    </div>


    <div class="home-right">
        <h2>
            Sign Up
        </h2>
        <?php
        if( $registrations_open )
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
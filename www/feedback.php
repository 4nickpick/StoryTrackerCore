<?php

/* 

	Story Tracker

	By Nicholas Pickering
	
	Track, Search, and Optimize your Science Fiction/Fantasy story

*/	

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

?>

<?php TemplateSet::begin('body') ?>

    <div class="social_media">
        <div class="fb-like" data-href="http://storytracker.net" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>

        <a href="https://twitter.com/thenickpick" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @thenickpick</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script></div>

    <div class="home-left">
        <h2>
            Thank you for using StoryTracker!
        </h2>
        <p>
            Please take a moment to let us know about your experience.
        </p>
        <p>
            <strong><em>This quick feedback form is entirely optional, </em></strong> but will greatly help us make Story Tracker better.
        </p>
        <div>
            <h2>
                Help us Grow!
            </h2>
            <p>
                Take the time to like and share us on Facebook, or follow us on Twitter!
            </p>
            <div class="fb-like" data-href="http://storytracker.net" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>

            <a href="https://twitter.com/thenickpick" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false">Follow @thenickpick</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        </div>
    </div>
    <div class="home-right">
        <form class="contact-form" action="/tabmin/modules/users/ajax.php" method="post" onsubmit="return handleAjaxForm(this, function(resp){
            AlertSet.addJSON(resp).add(
                new AlertSet.Button('Back to Home', function() {
                    goTo('/');
                })
            ).show();
            }, function(resp) {AlertSet.addJSON(resp).show();})" autocomplete="off" enctype="multipart/form-data">
            <?=XSRF::html()?>
            <input type="hidden" name="verb" value="feedback" />
            <h2>
                Give us some feedback!
            </h2>
            <table>
                <tr>
                    <td colspan="2">
                        <input name="name" placeholder="Your Name"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input name="email" placeholder="Your Email Address"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea name="problems" placeholder="Did you encounter any problems while using Story Tracker?"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea name="opinion" placeholder="Do you plan to use Story Tracker to organize your stories? Why or Why Not?"></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <textarea name="comments" placeholder="Have any questions or comments? Let us know your thoughts here."></textarea>
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
                        <input type="submit" value="Send Feedback" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
<?php TemplateSet::end() ?>

<?php TemplateSet::display($_SERVER['DOCUMENT_ROOT'].'/template.php'); ?>
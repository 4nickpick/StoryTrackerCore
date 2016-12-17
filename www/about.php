<?php

/* 

	Story Tracker

	By Nicholas Pickering
	
	Track, Search, and Optimize your Science Fiction/Fantasy story

*/	

ini_set('display_errors', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/../private/includes/config.inc.php');

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

    <div class="about">

        <div class="headshot">
            <img src="/images/Nick-Pickering.jpeg" />
            <p>
                <strong>Nick Pickering</strong>, <br />
                creator of Story Tracker<br />
                <a href="mailto:nick@storytracker.net">nick@storytracker.net</a>
            </p>
        </div>

        <h2>
            About Us
        </h2>
        <p>
            I'm Nicholas Pickering, a software developer in Jacksonville, FL.
        </p>
        <p>
            I fell in love with writer culture after my wife began working on her first novel.
        </p>
        <p>
            Together, we came up with the concept for Story Tracker:
            a web application to solve the analytical and practical issues of writing a novel,
            and getting out of the way when creative forces need control.
        </p>

        <p>
            We'd love to get your thoughts on what we've done here.
        </p>

        <p>
            <a href="/#signup">Sign up</a> for a free account and let us know what you think.
        </p>

        <p>
            Look forward to seeing you inside!
        </p>

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
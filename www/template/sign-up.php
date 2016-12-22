<div class="home-right with-background">
    <?php
    // see beta directory for beta sign-ups, etc
    if( $registrations_open == true )
    {
        ?>
        <h2>Track Your First Story For Free</h2>
        <p>Get started for free, <strong>no credit card necessary</strong>. </p>
        <form class="pure-form pure-form-stacked"
              action="/tabmin/modules/users/ajax.php"
              method="post"
              onsubmit=""
              autocomplete="off"
              enctype="multipart/form-data">
            <fieldset>
                <input type="hidden" name="verb" value="sign-up" />
                <?=XSRF::html()?>
                <div class="pure-g">
                    <div class="pure-u-1 pure-u-md-1-2">
                        <label for="first_name">First Name <strong>*</strong></label>
                        <input type="text" id="first_name" name="first_name" class="pure-u-23-24" placeholder="First Name*"/>
                    </div>
                    <div class="pure-u-1 pure-u-md-1-2">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="pure-u-23-24" placeholder="Last Name"/>
                    </div>
                    <div class="pure-u-1 pure-u-md-1-2">
                        <label for="last_name">Email <strong>*</strong></label>
                        <input type="text" id="email" name="email" class="pure-u-23-24" placeholder="Your Email*" autocomplete="off"/>
                    </div>
                    <div class="pure-u-1 pure-u-md-1-2">
                        <label for="last_name">How did you hear about us?</label>
                        <input type="text" id="referral" name="referral" class="pure-u-23-24" placeholder="How did you hear about us?" autocomplete="off"/>
                    </div>
                    <div class="pure-u-1 pure-u-md-1-2">
                        <label for="last_name">New Password <strong>*</strong> <small>(10 character minimum)</small></label>
                        <input type="password" id="password" name="password" class="pure-u-23-24" placeholder="New Password* 10 character min" autocomplete="off" />
                    </div>
                    <div class="pure-u-1 pure-u-md-1-2">
                        <label for="last_name">Re-enter Password <strong>*</strong></label>
                        <input type="password" id="password2" name="password2" class="pure-u-23-24" placeholder="Re-enter Password*" autocomplete="off"/>
                    </div>
                    <div class="pure-u-1 pure-u-md-1-1">
                        <button
                            class="g-recaptcha pure-button button-success"
                            data-sitekey="<?php echo RECAPTCHA_PUBLIC; ?>"
                            data-callback="">
                            Get Started For Free &raquo;
                        </button>
                    </div>
                    <div class="pure-u-1 pure-u-md-1-1">
                        <p><small><strong>
                                    We will never sell your email address or send you spam.
                                    <a href="/privacy" target="_blank">View our Privacy Policy</a>
                                </strong></small></p>
                    </div>
                </div>
            </fieldset>

        </form>
        <?php
    }
    else
    {
        echo '<div class="AlertSet_info"><ul><li>Registrations are temporarily closed. Please check back soon.</li></ul></div>';
    }
    ?>
</div>
    <?php global $currentUser, $currentStory, $module, $tab; ?>

    <?php
    $stories_items = array(
        array('label'=>'List', 'img'=>'list.png', 'url'=>'/stories/list/', 'caption'=>'manage your tales'),
        array('label'=>'Add', 'img'=>'add.png', 'url'=>'/stories/add/', 'caption'=>'add a new story'),
    ); ?>
    <?php
    $characters_items = array(
        array('label'=>'List', 'img'=>'list.png', 'url'=>'/characters/list/', 'caption'=>'manage your cast'),
        array('label'=>'Add', 'img'=>'add.png', 'url'=>'/characters/add/', 'caption'=>'add a new character'),
        /*array('label'=>'Family Tree', 'img'=>'family_tree.png', 'url'=>'/characters/family-tree/', 'caption'=>'parents, siblings, and babies'), */
        array('label'=>'Relationships', 'img'=>'relationships.png', 'url'=>'/characters/relationships/', 'caption'=>'track your romances and conflicts'),
        array('label'=>'Model', 'img'=>'model.png', 'url'=>'/characters/model/', 'caption'=>'what makes a character?'),
    ); ?>
    <?php
    $settings_items = array(
        array('label'=>'List', 'img'=>'list.png', 'url'=>'/settings/list/', 'caption'=>'manage your worlds'),
        array('label'=>'Add', 'img'=>'add.png', 'url'=>'/settings/add/', 'caption'=>'add a new location'),
        array('label'=>'Model', 'img'=>'model.png', 'url'=>'/settings/model/', 'caption'=>'what makes a location?'),
    ); ?>
    <?php
    $plot_items = array(
        array('label'=>'List', 'img'=>'list.png', 'url'=>'/plot/list/', 'caption'=>'manage your outline'),
        array('label'=>'Add', 'img'=>'add.png', 'url'=>'/plot/add/', 'caption'=>'add a new event'),
        array('label'=>'Synopsis', 'img'=>'synopsis.png', 'url'=>'/plot/synopsis/', 'caption'=>'tweak your elevator pitch')
    ); ?>
    <?php
    $inspiration_items = array(
        array('label'=>'Photo Gallery', 'img'=>'photo_gallery.png', 'url'=>'/photos/list/', 'caption'=>'manage your eye candy'),
    ); ?>




    <div class="nav top">
        <span class="img-wrapper"><a href="/"><img src="/images/logo-beta.png" /></a></span>
        <span class="top-center-menu">
            <?php if( isset($currentUser) ) : ?>
                <?php
                $loader = new Stories();
                $stories = $loader->loadByCurrentUser($currentUser->get_id());
                ?>

                <span class="current-story-label">Working on: </span>
                <select class="story_menu" name="story" onchange="loadStory(this.value, '<?=$module?>', '<?=$tab?>');">
                    <?php if( !($currentStory instanceof Story) ) : ?>
                        <option value="" selected="selected">
                            --
                        </option>
                    <?php endif; ?>
                    <?php
                    if( count($stories) > 0 )
                    {
                        foreach($stories as $story)
                        {
                            ?>
                            <option
                                value="<?=$story->get_id()?>"
                                <?php
                                if( $currentStory instanceof Story ){
                                    if( $currentStory->get_id() == $story->get_id() ) {
                                        echo 'selected="selected"';
                                    }
                                }
                                ?>>
                                <?=htmlentitiesUTF8($story->get_name())?>
                            </option>
                        <?php
                        }
                    }
                    ?>
                    <option value="" style="font-style: italic;">Add a Story +</option>
                </select>
            </span>
            <span class="top-right-menu">
                <a href="javascript:" onclick="toggleProfileMenu()">
                    <img src="/images/ui/profile.png" style="width: 16px;"/>
                </a>
                <div id="profile-menu" class="profile-menu">
                    <ul>
                        <li onclick="goTo('/users/profile-edit/');">
                            <span>Your Account</span>
                        </li>
                        <li onclick="goTo('/?logout');">
                            <span>Log Out</span>
                        </li>
                    </ul>
                </div>
                <div class="clear"></div>
            <?php else: ?>
                &nbsp;
            </span>
            <span class="top-right-menu">
                <a href="/log-in/">Log In</a>
            </span>
        <?php endif; ?>
    </div>
	<div class="nav bottom">
        <div class="bottom-left-menu">
            &nbsp;
        </div>

        <div class="bottom-center-menu">
            <?php if( isset($currentUser) ) : ?>
                <ul>
                    <li id="stories"><a href="#">Stories</a></li>
                    <?php if( $currentStory instanceof Story ) : ?>
                        <li id="characters"><a href="#">Characters</a></li>
                        <li id="settings"><a href="#">Settings</a></li>
                        <li id="plot"><a href="#">Plot</a></li>
                        <li id="inspiration"><a href="#">Inspiration</a></li>
                    <?php endif; ?>
                </ul>

                <div id="submenu_stories" class="submenu">
                    <ul>
                        <?php
                        foreach($stories_items as $i=>$fields)
                        {
                            ?>
                            <li <?php echo $i == count($stories_items)-1 ? 'class="last"' : '' ?> onclick="goTo('<?php echo $fields['url']?>');">
                                <img src="/images/ui/<?php echo $fields['img']?>" />
                                <span class="submenu_header"><?php echo htmlentitiesUTF8($fields['label'])?></span>
                                <div class="submenu_content"><?php echo htmlentitiesUTF8($fields['caption'])?></div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>


                <div id="submenu_characters" class="submenu">
                    <ul>
                        <?php
                        foreach($characters_items as $i=>$fields)
                        {
                            ?>
                            <li <?php echo $i == count($characters_items)-1 ? 'class="last"' : '' ?> onclick="goTo('<?php echo $fields['url']?>');">
                                <img src="/images/ui/<?php echo $fields['img']?>" />
                                <span class="submenu_header"><?php echo htmlentitiesUTF8($fields['label'])?></span>
                                <div class="submenu_content"><?php echo htmlentitiesUTF8($fields['caption'])?></div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>

                <div id="submenu_settings" class="submenu">
                    <ul>
                        <?php
                        foreach($settings_items as $i=>$fields)
                        {
                            ?>
                            <li <?php echo $i == count($settings_items)-1 ? 'class="last"' : '' ?> onclick="goTo('<?php echo $fields['url']?>');">
                                <img src="/images/ui/<?php echo $fields['img']?>" />
                                <span class="submenu_header"><?php echo htmlentitiesUTF8($fields['label'])?></span>
                                <div class="submenu_content"><?php echo htmlentitiesUTF8($fields['caption'])?></div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>

                <div id="submenu_plot" class="submenu">
                    <ul>
                        <?php
                        foreach($plot_items as $i=>$fields)
                        {
                            ?>
                            <li <?php echo $i == count($plot_items)-1 ? 'class="last"' : '' ?> onclick="goTo('<?php echo $fields['url']?>');">
                                <img src="/images/ui/<?php echo $fields['img']?>" />
                                <span class="submenu_header"><?php echo htmlentitiesUTF8($fields['label'])?></span>
                                <div class="submenu_content"><?php echo htmlentitiesUTF8($fields['caption'])?></div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>

                <div id="submenu_inspiration" class="submenu">
                    <ul>
                        <?php
                        foreach($inspiration_items as $i=>$fields)
                        {
                            ?>
                            <li <?php echo $i == count($inspiration_items)-1 ? 'class="last"' : '' ?> onclick="goTo('<?php echo $fields['url']?>');">
                                <img src="/images/ui/<?php echo $fields['img']?>" />
                                <span class="submenu_header"><?php echo htmlentitiesUTF8($fields['label'])?></span>
                                <div class="submenu_content"><?php echo htmlentitiesUTF8($fields['caption'])?></div>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            <?php else: ?>
                <ul>
                    <li id="home"><a href="/#signup" style="cursor:pointer;">Sign Up</a></li>
                    <li id="about"><a href="/about" style="cursor:pointer;">About Us</a></li>
                    <li id="contact"><a href="/contact" style="cursor:pointer;">Contact</a></li>
                </ul>
            <?php endif; ?>
        </div>

        <div class="bottom-right-menu">
            &nbsp;
        </div>
    </div>
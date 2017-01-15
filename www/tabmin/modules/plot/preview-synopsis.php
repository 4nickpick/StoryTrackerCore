<?php
    global $currentStory;
?>

<div class="preview-message">
    <h2>Your Plot Synopsis is empty!</h2>
    <h4>The Plot Synopsis is an important piece of content used to entice potential readers and editors. </h4>

    <p>
    <?php
    if($currentStory->isEditable()) {
        ?>
            <a href="/stories/edit/<?=$currentStory->get_id()?>?synopsis">Get Started on your Plot Synopsis</a>
        <?php
    } else {
        echo '<em>This story is <strong>locked</strong>. You must renew your account to manage your plot synopsis.</em>';
    }
    ?>
    </p>
</div>
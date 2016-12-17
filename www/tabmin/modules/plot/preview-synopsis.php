<?php
    global $currentStory;
?>

<div class="preview-message">
    <h2>Your Plot Synopsis is empty!</h2>
    <h4>The Plot Synopsis is an important piece of content used to entice potential readers and editors. </h4>
    <p>
        <a href="/stories/edit/<?=$currentStory->get_id()?>?synopsis">Get Started on your Plot Synopsis</a>
    </p>
</div>
<?php
$hitPayWallStoryLocked = false;
if(!empty($currentUser)) {
    if($currentUser->get_account_type() == UserAccountTypes::LIMITED_UNPAID) {
        if(empty($currentStory) || !$currentStory->isEditable()) {
            $hitPayWallStoryLocked = true;
        }
    }
}

if($hitPayWallStoryLocked) {
    if(!empty($module) && !empty($module_name)) {
        header('Location: /' . $module_name . '/list/');
        exit();
    } else {
        header('Location: /stories/list/');
        exit();
    }
}
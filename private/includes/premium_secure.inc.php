<?php
if(!empty($currentUser)) {
    if($currentUser->get_account_type() == UserAccountTypes::LIMITED_UNPAID) {
        $stories = (new Stories())->loadByCurrentUser($currentUser->id);
        if( count( $stories ) >= 1 ) {
            $_SESSION['hitPayWall'] = true;
            header('Location: /stories/list/');
            exit();
        }
    }
}
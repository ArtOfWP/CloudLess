<?php

/**
 * @param $user
 * @param $capability
 * @param $data
 * @return bool|mixed
 */
function clmvc_user_can($user, $capability, $data) {
    if(user_can($user, $capability)) {
        return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
            [user_can($user, $capability), $data]);
    }
    return false;
}


/**
 * @param string $capability
 * @param mixed $data
 * @return bool|mixed
 */
function cl_current_user_can($capability, $data) {
    if(current_user_can($capability)) {
        return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
            [user_can(wp_get_current_user(), $capability), $data]);
    }
    return false;
}
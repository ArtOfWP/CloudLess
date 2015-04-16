<?php

/**
 * @param $user
 * @param $capability
 * @param $data
 * @return bool|mixed
 */
function clmvc_user_can($user, $capability, $data) {
    return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
        [user_can($user, $capability), $data]);
}


/**
 * @param string $capability
 * @param mixed $data
 * @return bool|mixed
 */
function cl_current_user_can($capability, $data) {
    return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
        [user_can(wp_get_current_user(), $capability), $data]);
}
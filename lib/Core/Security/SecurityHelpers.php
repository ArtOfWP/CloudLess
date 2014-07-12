<?php

/**
 * @param $userId
 * @param $capability
 * @param $data
 * @return bool|mixed
 */
function clmvc_user_can($userId, $capability, $data) {
    if(user_can($userId, $capability)) {
        return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
            [user_can($userId, $capability), $data]);
    }
    return false;
}
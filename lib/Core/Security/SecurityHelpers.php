<?php

/**
 * Filter:clmvc-user-can-{$capability}, [current_user_can(), $data, $user]
 * @param $user
 * @param $capability
 * @param $data
 *
 * @return bool|mixed
 */
function clmvc_user_can($user, $capability, $data)
{
    return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
        [user_can($user, $capability), $data, $user]);
}

/**
 * @deprecated
 * @param $capability
 * @param null $data
 * @return bool|mixed
 */
function cl_current_user_can($capability, $data = null)
{
    return clmvc_current_user_can($capability, $data);
}

/**
 * Filter:clmvc-user-can-{$capability}, [current_user_can(), $data, $current_user]
 * @param string $capability
 * @param mixed $data
 *
 * @return bool|mixed
 */
function clmvc_current_user_can($capability, $data = null)
{
    return CLMVC\Events\Filter::run("clmvc-user-can-{$capability}",
        [current_user_can($capability), $data, wp_get_current_user()]);
}

/**
 * Filter:clmvc-user-can-{$action}-{$capability}, [current_user_can(), $data, $current_user]
 * @param string $action
 * @param string $capability
 * @param mixed $data
 *
 * @return bool|mixed
 */
function clmvc_current_user_can_do_action($action, $capability, $data = null)
{
    return CLMVC\Events\Filter::run("clmvc-user-can-{$action}-{$capability}",
        [current_user_can($capability), $data, wp_get_current_user()]);
}

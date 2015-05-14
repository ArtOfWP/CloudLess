<?php

namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Interfaces\ISecurity;

/**
 * Class WpSecurity.
 */
class WpSecurity implements ISecurity
{
    /**
     * @param $nonce
     * @param bool $action
     *
     * @return mixed
     */
    public function verifyNonce($nonce, $action = false)
    {
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * @param bool $action
     *
     * @return mixed
     */
    public function createNonce($action = false)
    {
        return wp_create_nonce($action);
    }

    /**
     * @return mixed
     */
    public function getCurrentUser()
    {
        global $current_user;
        get_currentuserinfo();

        return $current_user;
    }

    /**
     * @param $action
     *
     * @return mixed
     */
    public function currentUserCan($action)
    {
        return current_user_can($action);
    }

    /**
     * @return mixed
     */
    public function currentUserIsLoggedIn()
    {
        return is_user_logged_in();
    }

    /**
     * @param $role
     *
     * @return mixed
     */
    public function currentUserIsInRole($role)
    {
        return current_user_can($role);
    }

    /**
     * @return WpSecurity
     */
    public static function instance()
    {
        return new self();
    }

    public function isAdmin()
    {
        // TODO: Implement isAdmin() method.
    }
}

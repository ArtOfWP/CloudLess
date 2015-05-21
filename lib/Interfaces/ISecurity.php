<?php

namespace CLMVC\Interfaces;

/**
 * Class ISecurity.
 */
interface ISecurity
{
    /**
     * @param string $nonce
     * @param string $action
     *
     * @return mixed
     */
    public function verifyNonce($nonce, $action = '');

    /**
     * @param bool $action
     *
     * @return mixed
     */
    public function createNonce($action = false);

    /**
     * @return mixed
     */
    public function getCurrentUser();

    /**
     * @param string $action
     *
     * @return mixed
     */
    public function currentUserCan($action);

    /**
     * @return mixed
     */
    public function currentUserIsLoggedIn();

    /**
     * @param $role
     *
     * @return mixed
     */
    public function currentUserIsInRole($role);

    /**
     * @return void
     */
    public function isAdmin();

    /**
     * @return \CLMVC\ViewEngines\WordPress\WpSecurity
     */
    public static function instance();
}

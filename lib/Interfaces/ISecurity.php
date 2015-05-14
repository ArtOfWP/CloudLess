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
     * @param $action
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

    public function isAdmin();

    /**
     * @return mixed
     */
    public static function instance();
}

<?php
namespace CLMVC\Interfaces;

/**
 * Class ISecurity
 */
interface ISecurity{
    /**
     * @param $nonce
     * @param bool $action
     * @return mixed
     */
    function verifyNonce($nonce,$action=false);

    /**
     * @param bool $action
     * @return mixed
     */
    function createNonce($action=false);

    /**
     * @return mixed
     */
    function getCurrentUser();

    /**
     * @param $action
     * @return mixed
     */
    function currentUserCan($action);

    /**
     * @return mixed
     */
    function currentUserIsLoggedIn();

    /**
     * @param $role
     * @return mixed
     */
    function currentUserIsInRole($role);

    function isAdmin();

    /**
     * @return mixed
     */
    static function instance();
}

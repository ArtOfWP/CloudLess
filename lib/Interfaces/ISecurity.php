<?php
namespace CLMVC\Interfaces;

/**
 * Class ISecurity
 */
interface ISecurity{
    /**
     * @param string $nonce
     * @param string $action
     * @return mixed
     */
    function verifyNonce($nonce,$action='');

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

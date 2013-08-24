<?php

/**
 * Class WpSecurity
 */
class WpSecurity implements ISecurity{
    /**
     * @param $nonce
     * @param bool $action
     * @return mixed
     */
    function verifyNonce($nonce,$action=false){
		return wp_verify_nonce($nonce,$action);
	}

    /**
     * @param bool $action
     * @return mixed
     */
    function createNonce($action=false){
		return wp_create_nonce($action);
	}

    /**
     * @return mixed
     */
    function getCurrentUser(){
		global $current_user;
	    get_currentuserinfo();
		return $current_user;
	}

    /**
     * @param $action
     * @return mixed
     */
    function currentUserCan($action){
		return current_user_can($action);
	}

    /**
     * @return mixed
     */
    function currentUserIsLoggedIn(){
		return is_user_logged_in();
	}

    /**
     * @param $role
     * @return mixed
     */
    function currentUserIsInRole($role){
		return current_user_can($role);
	}

    /**
     * @return WpSecurity
     */
    static function instance(){
		return new WpSecurity();
	}
}
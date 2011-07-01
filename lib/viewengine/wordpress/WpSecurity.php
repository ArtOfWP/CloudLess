<?php
class WpSecurity implements ISecurity{
function verifyNonce($nonce,$action=false){
		return wp_verify_nonce($nonce,$action);
	}
	function createNonce($action=false){
		return wp_create_nonce($action);
	}
	function getCurrentUser(){
		global $current_user;
	    get_currentuserinfo();
		return $current_user;
	}
	function currentUserCan($action){
		return current_user_can($action);
	}
	function currentUserIsLoggedIn(){
		return is_user_logged_in();
	}
	function currentUserIsInRole($role){
		return current_user_can($role);
	}
	static function instance(){
		return new WpSecurity();
	}
	//TODO: deprecated since 11.6
	function verify_nonce($nonce,$action=false){
		return $this->verifyNonce($nonce,$action);
	}
	function create_nonce($action=false){
		return $this->createNonce($action);
	}
	function get_current_user(){
		return $this->getCurrentUser();
	}/*
	function get_user($userId){
		return get_userdata(userId);
	}*/
	function current_user_can($action){
		return $this->currentUserCan($action);
	}
	function current_user_is_logged_in(){
		return $this->currentUserIsLoggedIn();
	}
	function current_user_is_in_role($role){
		return $this->currentUserIsInRole($role);
	}
}
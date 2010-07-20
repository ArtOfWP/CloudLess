<?php
class WpSecurity implements ISecurity{
	function verify_nonce($nonce,$action=false){
		return wp_verify_nonce($nonce,$action)==1;
	}
	function create_nonce($action=false){
		return wp_create_nonce($action);
	}
	function get_current_user(){
		global $current_user;
	    get_currentuserinfo();
		return $current_user;
	}
	function get_user($userId){
		return get_userdata(userId);
	}
	function current_user_can($action){
		return current_user_can($action);
	}
	function current_user_is_logged_in(){
		return is_user_logged_in();
	}
	function current_user_is_in_role($role){
		return current_user_can($role);
	}
	static function instance(){
		return new WpSecurity();
	}
}
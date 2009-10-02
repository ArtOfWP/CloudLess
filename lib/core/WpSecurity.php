<?php
class WpSecurity{
	static function get_current_user(){
		global $current_user;
	    get_currentuserinfo();
		return $current_user;
	}
	static function get_user($userid){
		return get_userdata(userid);
	}
	static function current_user_can($action){
		return current_user_can($action);
	}
	static function current_user_is_logged_in(){
		return is_user_logged_in();
	}
	static function current_user_is_in_role($role){
		return current_user_can($role);
	}
}
<?php
interface ISecurity{
	function verify_nonce($nonce,$action=false);
	function create_nonce($action=false);
	function get_current_user();
	function current_user_can($action);
	function current_user_is_logged_in();
	function current_user_is_in_role($role);
	static function instance();
}

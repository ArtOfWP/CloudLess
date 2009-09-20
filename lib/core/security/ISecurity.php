<?php
interface ISecurity{
	function get_current_user();
	function current_user_can($action);
	function current_user_is_logged_in();
	function current_user_is_in_role($role);
}
?>
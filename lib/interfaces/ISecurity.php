<?php
interface ISecurity{
	function verifyNonce($nonce,$action=false);
	function createNonce($action=false);
	function getCurrentUser();
	function currentUserCan($action);
	function currentUserIsLoggedIn();
	function currentUserIsInRole($role);
	static function instance();
}

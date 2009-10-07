<?php
class SecurityFilter implements IFilter{
	private $useraction;
	private $nonce;
	function SecurityFilter($useraction,$nonce){
		$this->useraction=$useraction;
	}
	function perform($controller,$data){
		if($nonce)
			check_admin_referer($nonce);
		$wps = new WpSecurity();
		if($wps->current_user_is_logged_in())
			if($wps->current_user_can($this->useraction))
				return true;
			else{
				die('You cannot perform this action');
				exit;
			}
		else
			auth_redirect();
		return false;
	}
}
?>
<?php
class SecurityFilter implements IFilter{
	private $useraction;
	function SecurityFilter($useraction){
		$this->useraction=$useraction;
	}
	function perform($controller,$data){
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
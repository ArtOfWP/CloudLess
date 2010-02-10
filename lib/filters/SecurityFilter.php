<?php

class SecurityFilter implements IFilter{
	private $useraction;
	private $nonce;
	function SecurityFilter($useraction,$nonce){
		$this->useraction=$useraction;
	}
	function perform($controller,$data){
		$s = Security::create();	
		if($this->nonce)
			$s->check_nounce($this->nonce);
		if($s->current_user_is_logged_in())
			if($s->current_user_can($this->useraction))
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
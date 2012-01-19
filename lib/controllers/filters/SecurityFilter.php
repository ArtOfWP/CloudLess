<?php
if(!interface_exists('IFilter'))
	include('IFilter.php');
class SecurityFilter implements IFilter{
	private $useraction;
	private $nonce_base;
	private $refererPage;
	function __construct($useraction=false,$nonce_base=false,$referer_page=false){
		$this->useraction=$useraction;
		$this->nonce_base=$nonce_base;
		$this->refererPage=$referer_page;
	}
	function perform($controller,$data){
		$s = Security::create();
		if($this->nonce_base){
			$nonce=array_key_exists_v('_asnonce',$controller->values);
			if($nonce){
				$verified_nonce=$s->verifyNonce($nonce,$this->nonce_base);
				if(!$verified_nonce)
					return false;
			}
			else
				return false;
		}
		if($s->currentUserIsLoggedIn())
			if(!$this->useraction || $s->currentUserCan($this->useraction))
				return true;
			else{
				$controller->RenderText('You cannot perform this action');
			}
		return false;
	}
}
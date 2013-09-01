<?php
namespace CLMVC\Controllers\Filters;
use CLMVC\Interfaces\IFilter;

/**
 * Class SecurityFilter
 * Used together with a controller to limit access to the actions.
 */
class SecurityFilter implements IFilter{
	private $useraction;
	private $nonce_base;

    /**
     * @param bool $useraction The action to verify
     * @param bool $nonce_base The base of the nonce, usually name ot the project, plugin etc.
     */
    function __construct($useraction=false,$nonce_base=false) {
		$this->useraction=$useraction;
		$this->nonce_base=$nonce_base;
	}

    /**
     * Performs the security check, verify nonce and if user can perform action.
     * @param BaseController $controller
     * @param $data
     * @return bool
     */
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
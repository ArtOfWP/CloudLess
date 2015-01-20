<?php
namespace CLMVC\Controllers\Filters;
use CLMVC\Controllers\BaseController;
use CLMVC\Core\Security\Security;
use CLMVC\Interfaces\IFilter;

/**
 * Class SecurityFilter
 * Used together with a controller to limit access to the actions.
 */
class SecurityFilter implements IFilter{
	private $useraction;
	private $nonce_base;

    /**
     * @param string $useraction The action to verify
     * @param string $nonce_base The base of the nonce, usually name ot the project, plugin etc.
     */
    function __construct($useraction='',$nonce_base='') {
		$this->useraction=$useraction;
		$this->nonce_base=$nonce_base;
	}

    /**
     * Performs the security check, verify nonce and if user can perform action.
     * @param BaseController $controller
     * @param $data
     * @param string $action
     * @return bool
     */
    function perform($controller,$data, $action = ''){
		$s = Security::create();
		if($this->nonce_base){
			$nonce=array_key_exists_v('_asnonce',$controller->values);
			if($nonce){
				$verified_nonce=$s->verifyNonce($nonce,$this->nonce_base);
				if(!$verified_nonce)
					return false;
			} else
				return false;
		}
		if($s->currentUserIsLoggedIn()) {
            if (!$this->useraction || $s->currentUserCan($this->useraction))
                return true;
        }
        $controller->getRenderer()->RenderText('You cannot perform this action');
		return false;
	}
}
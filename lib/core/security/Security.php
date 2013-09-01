<?php
namespace CLMVC\Core\Security;
/**
 * Class Security
 * SHorthand Factory for creating ISecurity implementation for current view engine
 */
class Security {
    /**
     * @return ISecurity
     */
    static function create(){
		return ViewEngine::createSecurity();
	}
}
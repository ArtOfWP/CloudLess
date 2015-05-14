<?php

namespace CLMVC\Core\Security;

use CLMVC\Interfaces\ISecurity;
use ViewEngine;

/**
 * Class Security
 * SHorthand Factory for creating ISecurity implementation for current view engine.
 */
class Security
{
    /**
     * @return ISecurity
     */
    public static function create()
    {
        return ViewEngine::createSecurity();
    }

    public static function isAdmin()
    {
        return self::create()->isAdmin();
    }
}

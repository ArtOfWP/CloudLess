<?php

namespace CLMVC\Interfaces;

use CLMVC\Controllers\BaseController;

/**
 * Class IFilter
 * Filter to be used with Controllers.
 */
interface IFilter
{
    /**
     * @param BaseController $controller
     * @param mixed          $data
     * @param string         $action     Contains action if called during executeAction. Return is used to terminate action if performed beforeAction. Return false to terminate.
     *
     * @return mixed
     */
    public function perform($controller, $data, $action = '');
}

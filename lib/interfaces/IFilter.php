<?php
namespace CLMVC\Interfaces;
/**
 * Class IFilter
 * Filter to be used with Controllers
 */
interface IFilter{
    /**
     * @param BaseController $controller
     * @param mixed $data
     * @return mixed
     */
    function perform($controller,$data);
}
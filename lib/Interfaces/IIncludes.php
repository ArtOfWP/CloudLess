<?php
namespace CLMVC\Interfaces;
use CLMVC\Core\Includes\FrontInclude;

/**
 * Class IIncludes
 */
interface IIncludes
{
    /**
     * @param FrontInclude $include
     * @return IIncludes
     */
    function register( FrontInclude $include );

    /**
     * @param $handle
     * @return IIncludes
     */
    function deregister($handle);

    /**
     * @param string $location
     * @param string $handle
     * @return IIncludes
     */
    function enqueue($location, $handle);

    /**
     * @param string $location
     * @param string $handle
     * @return IIncludes
     */
    function dequeue($location,$handle);

    /**
     * @param string $handle
     * @return bool
     */
    function isRegistered($handle);

    /**
     * @param string $handle
     * @return bool
     */
    function isEnqueued($handle);

    /**
     */
    function init();

    /**
     * @param $location
     * @return FrontInclude[]
     */
    function getEnqueued($location);

    function registerIncludes();

    function getRegistered($handle = '');
}

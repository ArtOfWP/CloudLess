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
     * @return bool
     */
    function register( FrontInclude $include );

    /**
     * @param $handle
     * @return bool
     */
    function deregister($handle);

    /**
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function enqueue($location, $handle);

    /**
     * @param string $location
     * @param string $handle
     * @return bool
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
}

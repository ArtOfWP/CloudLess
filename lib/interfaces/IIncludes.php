<?php
namespace CLMVC\Interfaces;

/**
 * Class IIncludes
 */
interface IIncludes
{
    /**
     * @param FrontInclude $include
     * @return mixed
     */
    function register( FrontInclude $include );

    /**
     * @param $handle
     * @return bool
     */
    function deregister($handle);

    /**
     * @param string $location
     * @param FrontInclude $include
     * @return bool
     */
    function enqueue($location,FrontInclude $include);

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
}

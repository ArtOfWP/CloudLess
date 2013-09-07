<?php
namespace CLMVC\ViewEngines\Standard;
use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Interfaces\IIncludes;


class CLMVCFrontIncludes implements IIncludes {

    /**
     * @param FrontInclude $include
     * @return mixed
     */
    function register(FrontInclude $include)
    {
        // TODO: Implement register() method.
    }

    /**
     * @param $handle
     * @return bool
     */
    function deregister($handle)
    {
        // TODO: Implement deregister() method.
    }

    /**
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function enqueue($location, $handle)
    {
        // TODO: Implement enqueue() method.
    }

    /**
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function dequeue($location, $handle)
    {
        // TODO: Implement dequeue() method.
    }

    /**
     * @param string $handle
     * @return bool
     */
    function isRegistered($handle)
    {
        // TODO: Implement isRegistered() method.
    }

    /**
     * @param string $handle
     * @return bool
     */
    function isEnqueued($handle)
    {
        // TODO: Implement isEnqueued() method.
    }

    /**
     */
    function init()
    {
        // TODO: Implement init() method.
    }
}
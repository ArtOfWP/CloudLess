<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Andreas
 * Date: 2013-08-26
 * Time: 22:00
 * To change this template use File | Settings | File Templates.
 */

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
     * @param FrontInclude $include
     * @return bool
     */
    function enqueue($location, FrontInclude $include)
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
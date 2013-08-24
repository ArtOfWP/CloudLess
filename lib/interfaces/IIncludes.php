<?php

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
     * @return mixed
     */
    function deregister($handle);

    /**
     * @param $location
     * @param FrontInclude $include
     * @return mixed
     */
    function enqueue($location,FrontInclude $include);

    /**
     * @param $location
     * @param $handle
     * @return mixed
     */
    function dequeue($location,$handle);

    /**
     * @param $handle
     * @return mixed
     */
    function isRegistered($handle);

    /**
     * @param $handle
     * @return mixed
     */
    function isEnqueued($handle);

    /**
     * @return mixed
     */
    function init();
}

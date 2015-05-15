<?php

namespace CLMVC\Interfaces;

use CLMVC\Core\Includes\FrontInclude;

/**
 * Class IIncludes.
 */
interface IIncludes
{
    /**
     * @param FrontInclude $include
     *
     * @return IIncludes
     */
    public function register(FrontInclude $include);

    /**
     * @param $handle
     *
     * @return IIncludes
     */
    public function deregister($handle);

    /**
     * @param string $location
     * @param string $handle
     *
     * @return IIncludes
     */
    public function enqueue($location, $handle);

    /**
     * @param string $location
     * @param string $handle
     *
     * @return IIncludes
     */
    public function dequeue($location, $handle);

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function isRegistered($handle);

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function isEnqueued($handle);

    /**
     */
    public function init();

    /**
     * @param $location
     *
     * @return FrontInclude[]
     */
    public function getEnqueued($location);

    public function registerIncludes();

    /**
     * @param string $handle
     * @return FrontInclude
     */
    public function getRegistered($handle);

    /**
     * @return FrontInclude[]
     */
    public function getAllRegistered();
}

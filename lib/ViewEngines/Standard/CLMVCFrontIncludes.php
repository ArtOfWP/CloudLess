<?php

namespace CLMVC\ViewEngines\Standard;

use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Interfaces\IIncludes;

abstract class CLMVCFrontIncludes implements IIncludes
{
    /**
     * @var FrontInclude[handle]
     */
    private $registered;
    /**
     * @param FrontInclude $include
     *
     * @return mixed
     */
    public function register(FrontInclude $include)
    {
        $this->registered[$include->getHandle()] = $include;
    }

    /**
     * @param $handle
     *
     * @return bool
     */
    public function deregister($handle)
    {
        unset($this->registered[$handle]);
    }

    /**
     * @param string $location
     * @param string $handle
     *
     * @return bool
     */
    abstract public function enqueue($location, $handle);

    /**
     * @param string $location
     * @param string $handle
     *
     * @return bool
     */
    public function dequeue($location, $handle)
    {
        // TODO: Implement dequeue() method.
    }

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function isRegistered($handle)
    {
        // TODO: Implement isRegistered() method.
    }

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function isEnqueued($handle)
    {
        // TODO: Implement isEnqueued() method.
    }

    /**
     */
    public function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * @param $location
     *
     * @return FrontInclude[]
     */
    public function getEnqueued($location)
    {
        // TODO: Implement getEnqueued() method.
    }

    public function registerIncludes()
    {
    }

    public function getRegistered($handle = '')
    {
        if ($handle) {
            return $this->registered[$handle];
        }

        return $this->registered;
    }
}

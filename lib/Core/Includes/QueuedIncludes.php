<?php

namespace CLMVC\Core\Includes;

/**
 * Base class for including styles and scripts. Only for internal consumption.
 *
 */
abstract class QueuedIncludes
{
    private $queue;
    private $queued;

    public function __construct()
    {
        $this->queue = array();
        $this->queued = array();
    }

    public function add($location, FrontInclude $include)
    {
        $dependency = $include->getDependency();
        $registered = $this->getRegisteredIncludes();
        foreach ($dependency as $handle) {
            if (!in_array($handle, $this->queued[$location]) && isset($registered[$handle])) {
                $this->queue[$location][] = $registered[$handle];
                $this->queued[$location][] = $handle;
            }
        }
        $this->queued[$location][] = $include->getHandle();
        $this->queue[$location][] = $include;
    }

    /**
     * @return FrontInclude[]
     */
    abstract public function getRegisteredIncludes();
    abstract public function render($array);

    /**
     * @param string $tagFormat
     */
    protected function renderIncludeTag($tagFormat) {
        /**
         * @var FrontInclude[]
         */
        $queue = $this->getQueue('frontend');
        $array = array();
        foreach ($queue as $include) {
            $array[] = sprintf($tagFormat, $include->getSrc());
        }

        return $array;
    }
    /**
     * @param string $location
     *
     * @return array
     */
    public function getQueue($location)
    {
        return $this->queue[$location];
    }

    /**
     * @param string $location
     *
     * @return FrontInclude[]
     */
    public function getQueued($location)
    {
        return $this->queued[$location];
    }
}

<?php
namespace CLMVC\Core\Includes;
use CLMVC\Core\Container;
use CLMVC\Interfaces\IIncludes;
/**
 * Class StyleIncludes
 */
class StyleIncludes implements IIncludes
{
    private $styleInclude;

    /**
     * Inject a style handler for includes
     * @param IIncludes $iStyleInclude
     */
    function __construct(IIncludes $iStyleInclude=NULL){
        if($iStyleInclude)
            $this->styleInclude=$iStyleInclude;
        else
            $this->styleInclude=Container::instance()->fetch('IStyleInclude');
        $this->styleInclude->init();
    }

    /**
     * Register a include
     * @param FrontInclude $include
     * @return bool|void
     */
    public function register(FrontInclude $include) {
        return $this->styleInclude->register($include);
    }

    /**
     * Deregister a resource using its handle
     * @param string $handle
     * @return bool
     */
    function deregister($handle) {
        return $this->styleInclude->deregister($handle);
    }

    /**
     * Enqueue a resource to be loaded
     * @param string $location where it should be loaded
     * @param FrontInclude $include
     * @return bool
     */
    function enqueue($location, FrontInclude $include) {
         return $this->styleInclude->enqueue($location,$include);
     }

    /**
     * Remove resource from queue
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function dequeue($location, $handle) {
        return $this->styleInclude->dequeue($location,$handle);
    }

    /**
     * Check if resource is queued
     * @param string $handle
     * @return bool
     */
    function isEnqueued($handle) {
        return $this->styleInclude->isEnqueued($handle);
    }

    /**
     * Check if resource is registered
     * @param string $handle
     * @return bool
     */
    public function isRegistered($handle) {
        return $this->styleInclude->isRegistered($handle);
    }

    /**
     * Initiate the include handler
     * @return bool
     */
    function init() {
        return $this->styleInclude->init();
    }
}

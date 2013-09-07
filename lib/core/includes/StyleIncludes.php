<?php
namespace CLMVC\Core\Includes;
use CLMVC\Core\Container;
use CLMVC\Interfaces\IIncludes;
/**
 * Class StyleIncludes
 */
class StyleIncludes implements IIncludes {
    static $instance;

    /**
     * @var IIncludes
     */
    private $styleInclude;

    /**
     * Instantiate a Container
     * @static
     * @return StyleIncludes
     */
    public static function instance() {
        if(!isset(self::$instance) && empty(self::$instance))
            self::$instance = new StyleIncludes();
        return self::$instance;
    }
    /**
     * Inject a style handler for includes
     * @param IIncludes $iStyleInclude
     */
    function __construct(IIncludes $iStyleInclude=NULL){
        if($iStyleInclude)
            $this->styleInclude=$iStyleInclude;
        else
            $this->styleInclude=Container::instance()->fetch('CLMVC\\Interfaces\\IStyleInclude');
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
     * @param string $handle
     * @return bool
     */
    function enqueue($location, $handle) {
         return $this->styleInclude->enqueue($location,$handle);
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

    function render($content, $location) {
        $this->getEnqueued($location);
    }

    /**
     * @param $location
     * @return FrontInclude[]
     */
    function getEnqueued($location) {
        return $this->styleInclude->getEnqueued($location);
    }
}

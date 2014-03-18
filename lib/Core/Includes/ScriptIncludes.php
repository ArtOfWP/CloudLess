<?php
namespace CLMVC\Core\Includes;
use CLMVC\Core\Container;
use CLMVC\Events\Hook;
use CLMVC\Interfaces\IIncludes;

/**
 * ScriptIncludes
 * Handler for JavaScript FrontIncludes
 */
class ScriptIncludes implements IIncludes {

    /**
     * @var IIncludes $scriptInclude
     */
    private $scriptInclude;
    static $instance;

    /**
     * Instantiate a Container
     * @static
     * @return ScriptIncludes
     */
    public static function instance() {
        if(!isset(self::$instance) && empty(self::$instance))
            self::$instance = new ScriptIncludes();
        return self::$instance;
    }

    /**
     * Inject a script handler for includes
     * @param IIncludes $iScriptInclude
     */
    function __construct(IIncludes $iScriptInclude = NULL){
        if($iScriptInclude)
            $this->scriptInclude=$iScriptInclude;
        else
            $this->scriptInclude=Container::instance()->fetch('CLMVC\\Interfaces\\IScriptInclude');
        $this->scriptInclude->init();
        Hook::register('scripts-register', array($this, 'registerIncludes'));
    }

    /**
     * Register a include
     * @param FrontInclude $include
     * @return ScriptIncludes
     */
    public function register(FrontInclude $include) {
        $this->scriptInclude->register($include);
        return $this->scriptInclude;
    }

    /**
     * Deregister a resource using its handle
     * @param string $handle
     * @return ScriptIncludes
     */
    function deregister($handle) {
        $this->scriptInclude->deregister($handle);
        return $this->scriptInclude;
    }

    /**
     * Enqueue a resource to be loaded
     * @param string $location where it should be loaded
     * @param $handle
     * @return ScriptIncludes
     */
    function enqueue($location, $handle) {
        if ($this->isRegistered($handle))
            $this->scriptInclude->enqueue($location, $handle);
        else
            trigger_error(sprintf('No script with the handle %s has been registered', $handle), E_USER_WARNING);
        return $this;
    }

    /**
     * Remove resource from queue
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function dequeue($location, $handle) {
        $this->scriptInclude->dequeue($location,$handle);
        return $this->scriptInclude;
    }

    /**
     * Check if resource is queued
     * @param string $handle
     * @return bool
     */
    function isEnqueued($handle) {
        return $this->scriptInclude->isEnqueued($handle);
    }

    /**
     * Check if resource is registered
     * @param string $handle
     * @return bool
     */
    public function isRegistered($handle) {
        return $this->scriptInclude->isRegistered($handle);
    }

    /**
     * Initiate the include handler
     * @return ScriptIncludes
     */
    function init() {
        return $this->scriptInclude->init();
    }

    /**
     * @param $location
     * @return FrontInclude[]
     */
    function getEnqueued($location) {
        return $this->scriptInclude->getEnqueued($location);
    }

    function registerIncludes() {
        $this->scriptInclude->registerIncludes();
    }

    function getRegistered($handle = '') {
        return $this->scriptInclude->getRegistered($handle);
    }
}

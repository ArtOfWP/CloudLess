<?php
/**
 * ScriptIncludes
 * Handler for JavaScript FrontIncludes
 */
class ScriptIncludes implements IIncludes {

    /**
     * @var IIncludes $scriptInclude
     */
    private $scriptInclude;

    /**
     * Inject a script handler for includes
     * @param IIncludes $iScriptInclude
     */
    function __construct(IIncludes $iScriptInclude = NULL){
        if($iScriptInclude)
            $this->scriptInclude=$iScriptInclude;
        else
            $this->scriptInclude=Container::instance()->fetch('IScriptInclude');
        $this->scriptInclude->init();
    }

    /**
     * Register a include
     * @param FrontInclude $include
     */
    public function register(FrontInclude $include) {
        $this->scriptInclude->register($include);
    }

    /**
     * Deregister a resource using its handle
     * @param string $handle
     * @return bool
     */
    function deregister($handle) {
        return $this->scriptInclude->deregister($handle);
    }

    /**
     * Enqueue a resource to be loaded
     * @param string $location where it should be loaded
     * @param FrontInclude $include
     */
    function enqueue($location, FrontInclude $include) {
         $this->scriptInclude->enqueue($location,$include);
    }

    /**
     * Remove resource from queue
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function dequeue($location, $handle) {
        return $this->scriptInclude->dequeue($location,$handle);
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
     * @return bool
     */
    function init() {
        return $this->scriptInclude->init();
    }
}

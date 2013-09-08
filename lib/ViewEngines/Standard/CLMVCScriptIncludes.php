<?php
namespace CLMVC\ViewEngines\Standard;

use CLMVC\Core\Includes\QueuedScripts;

class CLMVCScriptIncludes extends CLMVCFrontIncludes {

    public function __construct() {
        QueuedScripts::instance();
    }

    /**
     * @param string $location
     * @param string $handle
     * @return bool
     */
    function enqueue($location, $handle) {
        $include = $this->getRegistered($handle);
        QueuedScripts::instance()->add($location, $include );
    }
}
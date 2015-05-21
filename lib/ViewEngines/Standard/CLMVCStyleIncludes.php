<?php

namespace CLMVC\ViewEngines\Standard;

use CLMVC\Core\Includes\QueuedStyles;

class CLMVCStyleIncludes extends CLMVCFrontIncludes
{
    public function __construct()
    {
        QueuedStyles::instance();
    }

    /**
     * @param string $location
     * @param string $handle
     *
     * @return boolean|null
     */
    public function enqueue($location, $handle)
    {
        $include = $this->getRegistered($handle);
        QueuedStyles::instance()->add($location, $include);
    }
}

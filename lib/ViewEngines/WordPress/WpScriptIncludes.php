<?php

namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Core\Includes\FrontInclude;

class WpScriptIncludes extends WpFrontIncludes
{
    public function enqueueInclude(FrontInclude $script)
    {
        wp_enqueue_script($script->getHandle(), $script->getSrc(), $script->getDependency(), $script->getVersion(), $script->loadInFooter());
    }

    public function registerInclude(FrontInclude $script)
    {
        wp_register_script($script->getHandle(), $script->getSrc(), $script->getDependency(), $script->getVersion(), $script->loadInFooter());
    }

    public function dequeueInclude($includeHandle)
    {
        wp_dequeue_script($includeHandle);
    }

    public function deregisterInclude($includeHandle)
    {
        wp_deregister_script($includeHandle);
    }

    /**
     * @param $location
     *
     * @return FrontInclude[]
     */
    public function getEnqueued($location)
    {
    }

    public function getRegistered($handle = '')
    {
        // TODO: Implement getRegistered() method.
    }
}

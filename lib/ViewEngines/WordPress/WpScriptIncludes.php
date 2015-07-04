<?php

namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Core\Includes\FrontInclude;

class WpScriptIncludes extends WpFrontIncludes
{
    public function enqueueInclude(FrontInclude $script)
    {
        if(defined('WP_DEBUG') && WP_DEBUG) {
        $dependencies = $script->getDependency();
            $scripts = wp_scripts()->registered;
            foreach($dependencies as $dependency){
                if(!isset($scripts[$dependency]))
                    trigger_error(sprintf('Dependency failed for %s, No script with handle %s is registered', $script->getHandle(), $dependency), E_USER_WARNING);
            }
        }
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
}

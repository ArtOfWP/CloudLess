<?php
/**
 * User: andreas
 * Date: 2011-12-23
 * Time: 17:43
 */

class WpScriptIncludes extends WpFrontIncludes
{
    function enqueueInclude(FrontInclude $script)
    {
        wp_enqueue_script($script->getHandle(),$script->getSrc(),$script->getDependency(),$script->getVersion(),$script->getInFooter());
    }

    function registerInclude(FrontInclude $script)
    {
        wp_register_script($script->getHandle(),$script->getSrc(),$script->getDependency(),$script->getVersion(),$script->getInFooter());
    }

    function dequeueInclude($includeHandle)
    {
        wp_dequeue_script($includeHandle);
    }

    function deregisterInclude($includeHandle)
    {
        wp_deregister_script($includeHandle);
    }
}

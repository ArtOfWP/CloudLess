<?php

namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Core\Includes\FrontInclude;

/**
 * User: andreas
 * Date: 2011-12-27
 * Time: 17:51.
 */
class WpStyleIncludes extends WpFrontIncludes
{
    public function enqueueInclude(FrontInclude $style)
    {
        if(defined('WP_DEBUG') && WP_DEBUG) {
            $dependencies = $style->getDependency();
            $scripts = wp_styles()->registered;
            foreach($dependencies as $dependency){
                if(!isset($scripts[$dependency]))
                    trigger_error(sprintf('Dependency failed for %s, No style with handle %s is registered', $style->getHandle(), $dependency), E_USER_WARNING);
            }
        }
        wp_enqueue_style($style->getHandle(), $style->getSrc(), $style->getDependency(), $style->getVersion(), $style->loadInFooter());
    }

    public function registerInclude(FrontInclude $style)
    {
        echo $style->getHandle(),"<br />";
        wp_register_style($style->getHandle(), $style->getSrc(), $style->getDependency(), $style->getVersion(), $style->loadInFooter());
    }

    public function dequeueInclude($styleHandle)
    {
        wp_dequeue_style($styleHandle);
    }

    public function deregisterInclude($styleHandle)
    {
        wp_deregister_style($styleHandle);
    }
}

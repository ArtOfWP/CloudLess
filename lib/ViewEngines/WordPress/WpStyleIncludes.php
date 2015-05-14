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
        wp_enqueue_style($style->getHandle(), $style->getSrc(), $style->getDependency(), $style->getVersion(), $style->loadInFooter());
    }

    public function registerInclude(FrontInclude $style)
    {
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

    /**
     * @param $location
     *
     * @return FrontInclude[]
     */
    public function getEnqueued($location)
    {
        // TODO: Implement getEnqueued() method.
    }

    public function getRegistered($handle = '')
    {
        // TODO: Implement getRegistered() method.
    }
}

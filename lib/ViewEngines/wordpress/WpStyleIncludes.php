<?php
use CLMVC\Core\Includes\FrontInclude;

/**
 * User: andreas
 * Date: 2011-12-27
 * Time: 17:51
 */
class WpStyleIncludes extends WpFrontIncludes {
    function enqueueInclude(FrontInclude $style) {
        wp_enqueue_style($style->getHandle(),$style->getSrc(),$style->getDependency(),$style->getVersion(),$style->loadInFooter());
    }

    function registerInclude(FrontInclude $style) {
        wp_register_style($style->getHandle(),$style->getSrc(),$style->getDependency(),$style->getVersion(),$style->loadInFooter());
    }

    function dequeueInclude($styleHandle) {
        wp_dequeue_style($styleHandle);
    }

    function deregisterInclude($styleHandle) {
        wp_deregister_style($styleHandle);
    }

    /**
     * @param $location
     * @return FrontInclude[]
     */
    function getEnqueued($location) {
        // TODO: Implement getEnqueued() method.
    }
}
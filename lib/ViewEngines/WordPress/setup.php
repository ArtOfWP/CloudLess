<?php
use CLMVC\Core\Container;
use CLMVC\ViewEngines\WordPress\WpEngine;
use CLMVC\ViewEngines\WordPress\WpRendering;

define('CLOUDLESS_APP_DIR', WP_PLUGIN_DIR);

Container::instance()->fetchOrMake(WpEngine::class)->init();
Container::instance()->fetchOrMake(WpRendering::class)->init();

/**
 * @param string $app
 * @param string $url
 *
 * @return string
 */
if (!function_exists('clmvc_app_url')) {

    /**
     * @return string
     */
    function clmvc_app_url($app, $url)
    {
        return plugins_url($app.'/'.$url);
    }
}


<?php
namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Controllers\BaggedValues;
use CLMVC\Controllers\Render\RenderingEngines;
use CLMVC\Core\Container;
use CLMVC\Core\Http\Routes;
use CLMVC\Events\Filter;

/**
 * Class WpEngine
 * @package CLMVC\ViewEngines\WordPress
 */
class WpEngine
{
    public function init()
    {
        RenderingEngines::registerEngine('php', 'CLMVC\\Controllers\\Render\Engines\\PhpRenderingEngine');
        (new WpActionOverrides())->init();
        $this->setupConstants();
        $this->setupContainer();
        $this->setupWpContainer();
        if (is_admin()) {
            Filter::register('after_plugin_row', [$this, 'loadCloudlessFirst'], 10);
        }
        Filter::register('status_header', [$this, 'addHeader']);
    }

    public function setupConstants()
    {
        if (!defined('PACKAGE_PATH')) {
            if (defined('WP_PLUGIN_DIR')) {
                $tempPath = WP_PLUGIN_DIR . '/cloudless/';
            } elseif (defined('WP_CONTENT_DIR')) {
                $tempPath = WP_CONTENT_DIR . '/plugins/cloudless/';
            } else {
                $tempPath = ABSPATH . 'wp-content/plugins/cloudless/';
            }
            define('PACKAGE_PATH', $tempPath);
        }
    }

    public function setupContainer()
    {
        $container = Container::instance();
        $container->add('CLMVC\\Interfaces\\IScriptInclude', new WpScriptIncludes());
        $container->add('CLMVC\\Interfaces\\IStyleInclude', new WpStyleIncludes());
        $container->add('CLMVC\\Interfaces\\IOptions', 'CLMVC\\ViewEngines\\WordPress\\WpOptions', 'class');
        $container->add('CLMVC\\Interfaces\\IPost', 'CLMVC\\ViewEngines\\WordPress\\WpPost', 'class');
        $container->add('Routes', Routes::instance());
        $container->add(Routes::class, Routes::instance());
        $container->add('Bag', new  BaggedValues());
    }

    public function setupWpContainer()
    {
        global $wpdb;
        $container = Container::instance();
        $container->add('wpdb', $wpdb);
    }

    public function loadCloudlessFirst()
    {
        $plugin = plugin_basename(sl_file('AoiSora'));
        $active = get_option('active_plugins');
        if ($active[0] == $plugin) {
            return;
        }
        $place = array_search($plugin, $active);
        if ($place === false) {
            return;
        }
        array_splice($active, $place, 1);
        array_unshift($active, $plugin);
        update_option('active_plugins', $active);
    }

    public function addHeader($status_header)
    {
        global $clmvc_http_code;
        if ($clmvc_http_code) {
            header_remove('X-Powered-By');
            header_remove('X-Pingback');
            header_remove('Pragma');
            $description = get_status_header_desc($clmvc_http_code);
            $protocol = 'HTTP/1.0';
            $status_header = "$protocol $clmvc_http_code $description";
        }
        return $status_header;
    }
}
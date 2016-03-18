<?php
namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Controllers\Render\RenderedContent;
use CLMVC\Core\Container;
use CLMVC\Core\Http\Routes;
use CLMVC\Events\Filter;
use CLMVC\Events\Hook;

/**
 * Class WpRendering
 * @package CLMVC\ViewEngines\WordPress
 */
class WpRendering {
    /**
     * @var Routes
     */
    private $routes;

    public function __construct(Routes $routes){

        $this->routes = $routes;
    }
    public function init() {

        Hook::register('init', [$this, 'routeQuery'], 1);
        Hook::register('init', [$this, 'renderText'], 99999);

        add_filter('do_parse_request', [$this, 'disableParseRequest'], 9999999, 3);
        Filter::register('posts_request', [$this, 'disablePostQuery'],9999999);
        Filter::register('pre_handle_404', [$this,'disable404handling']);
        Hook::register('rendering-render', function($controllerName, $action){
            global $clmvc_template;
            $clmvc_template=[$controllerName, $action];
        });
        if(!current_theme_supports('cloudless'))
            Container::instance()->make(ThemeCompatibility::class);
        else
            Filter::register('template_include', [$this,'includeTemplate']);

        Filter::register('document_title_parts', [$this, 'setTitle']);
        Filter::register('wp_title', [$this, 'setTitle']);
    }

    /**
     * @param $true
     * @param $instance
     * @return bool
     */
    public function disableParseRequest($true, $instance) {
        if($this->routes->routeExists()) {
            $instance->query_vars = [];
            return false;
        } else {
            return $true;
        }
    }

    /**
     * @param $request
     * @return null
     */
    public function disablePostQuery($request) {
        if($this->routes->routeExists()) {
            global $wp_query;
            $wp_query->is_home = false;
            $wp_query->is_404 = false;
            $wp_query->is_page = false;
            return null;
        }
        return $request;
    }

    /**
     * @param $handle
     * @return bool
     */
    public function disable404handling($handle) {
        if ($this->routes->isRouted())
            return false;
        return $handle;
    }
    /**
     * Set the WordPress page title
     * @param $title
     * @param string $sep
     * @return string
     */
    public function setTitle($title, $sep ='') {
        $bag = Container::instance()->fetch('Bag');
        if (isset($bag->title)) {
            if (current_theme_supports('title-tag'))
                array_unshift($title, $bag->title);
            else
                $title=$bag->title.$sep;
        }
        return $title;
    }

    public function routeQuery() {
        /**
         * @var Routes $routes
         */
        $container = Container::instance();
        $routes = $container->fetch('Routes');
        $routes->routing();
    }

    /**
     *
     */
    public function renderText() {
        if (RenderedContent::hasRendered() && RenderedContent::endIt()) {
            RenderedContent::endFlush();
            exit;
        }
    }
    /**
     * @var \WP_Query $wp_query
     */
    public function overrideWpQuery($wp_query) {
        /**
         * @var Routes $routes
         */
        $container = Container::instance();
        $routes = $container->fetch('Routes');
        if(!$routes->isRouted()) {
            $wp_query->set('pagename', $_SERVER['REQUEST_URI']);
            $wp_query->is_page = false;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->current_post = -1;
            $wp_query->post_count = 0;
            $wp_query->found_posts = 0;
            $wp_query->is_404 = false;
        }
    }

    public function includeTemplate($original_template) {
        if (RenderedContent::hasRendered()) {
            if (RenderedContent::endIt()) {
                RenderedContent::endFlush();
                return '';
            } else {
                return $this->getTemplate($original_template);
            }
        } else {
            return $original_template;
        }
    }

    public function getTemplate($default = '') {
        global $clmvc_template;
        list($controller, $action) = $clmvc_template;
        $controller=strtolower($controller);
        $templates= [
            'templates'.DIRECTORY_SEPARATOR."$controller-$action.php",
            'templates'.DIRECTORY_SEPARATOR."$controller".DIRECTORY_SEPARATOR."$action.php",
            'layouts'.DIRECTORY_SEPARATOR."$controller.php",
            "layout-$controller.php",
            "template-$controller-$action.php",
            "$controller-$action.php",
            "$controller".DIRECTORY_SEPARATOR."$action.php",
            "$controller.php",
            "default-layout.php",
            'cloudless.php'
        ];

        foreach($templates as $template) {
            if (file_exists(get_stylesheet_directory() . DIRECTORY_SEPARATOR . $template))
                return get_stylesheet_directory() . DIRECTORY_SEPARATOR . $template;
        }
        return $default;
    }
}

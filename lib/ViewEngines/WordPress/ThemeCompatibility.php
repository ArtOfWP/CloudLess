<?php
namespace CLMVC\ViewEngines\WordPress;

use CLMVC\Controllers\Render\RenderedContent;
use CLMVC\Core\Container;
use CLMVC\Core\Http\Routes;
use CLMVC\Events\Hook;

/**
 * Class ThemeCompatibility
 * @package CLMVC\ViewEngines\WordPress
 */
class ThemeCompatibility
{
    /**
     * @var Routes
     */
    private $routes;

    /**
     * ThemeCompatibility constructor.
     * @param Routes $routes
     */
    public function __construct(Routes $routes)
    {
        $this->routes = $routes;
        add_action('template_redirect', [$this, 'override']);
    }

    /**
     * Overrides global $post and $wp_query with the rendered content from CloudLess.
     * Its done if is_main_query and CloudLess detected a correct and matching route.
     */
    public function override()
    {
        global $wp_query;
        if ($wp_query->is_main_query() && !$this->routes->isRouted() ||
            !$wp_query->is_main_query() && !$this->routes->isRouted() ||
            !$wp_query->is_main_query() && $this->routes->isRouted()
        )
            return;
        $bag = Container::instance()->fetch('Bag');
        $args = ['post_content' => RenderedContent::get(), 'post_title' => $bag->title, 'is_page' => true,
            'guid' =>$this->routes->getCurrentRoute()->getRoutePath()];
        $dummy = new DummyPost($args);
        $dummy->overrideWpQuery();
        remove_filter('the_content', 'wpautop');
        unset($dummy);
    }
}

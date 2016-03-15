<?php

namespace CLMVC\Core\Http;

use CLMVC\Controllers\BaseController;
use CLMVC\Core\Container;
use CLMVC\Helpers\Communication;

class Routes
{
    private static $prefix='';
    /**
     * @var Route[]
     */
    private $routes = array();
    private $priorityRoutes = array();
    private $routed = null;

    private static $self = null;
    public static function instance() {
        self::$self = self::$self ?: new Routes();
        return self::$self;
    }

    public static function group($prefix, $callback) {
        self::$prefix = $prefix;
        call_user_func($callback);
        self::$prefix = '';
        return self::instance();
    }

    public static function get($route, $callback, $params = array()) {
        return self::instance()->add(self::$prefix . '/' . ltrim($route,"/"), $callback, $params, 'get');
    }

    public static function delete($route, $callback, $params = array()) {
        return self::instance()->add(self::$prefix . '/' . ltrim($route,"/"), $callback, $params, 'delete');
    }

    public static function update($route, $callback, $params = array()) {
        return self::instance()->add(self::$prefix . '/' . ltrim($route,"/"), $callback, $params, 'put');
    }

    public static function create($route, $callback, $params = array()) {
        return self::instance()->add(self::$prefix . '/' . ltrim($route,"/"), $callback, $params, 'post');
    }

    public function add($route, $callback, $params = array(), $method = 'get', $priority = false)
    {
        $theRoute = new Route($route, $callback, $params, $method);
        if ($priority) {
            $this->priorityRoutes[] = $theRoute;
        } else {
            $this->routes[] = $theRoute;
        }
        return $this;
    }

    /**
     * Takes request uri and routes to controller.
     */
    public function routing()
    {
        $routes = array_merge($this->priorityRoutes, $this->routes);
        $uri = $_SERVER['REQUEST_URI'];
        $method = Communication::getMethod();
        /**
         * @var Route $route
         */
        foreach ($routes as $route) {
            if ($matches = $route->match($uri, $method)) {
                $params = $route->params($uri, $method);
                $array = $route->getCallback();
                $controller = str_replace('/', '\\', $array[0]);
                /**
                 * @var BaseController
                 */
                $ctrl = Container::instance()->make($controller);
                $ctrl->init();
                $action = $array[1];
                if ($action == ':action') {
                    $action = str_replace(':action', $matches['action'], $action);
                }
                try {
                    $ctrl->executeAction($action, $params);
                    $this->routed = true;
                } catch (\Exception $ex) {
                    $this->routed = false;
                }

                return $this->routed;
            }
        }
        $this->routed = false;

        return $this->routed;
    }

    /**
     * @return null|bool
     */
    public function isRouted()
    {
        return $this->routed;
    }
}
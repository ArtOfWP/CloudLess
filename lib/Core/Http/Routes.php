<?php

namespace CLMVC\Core\Http;

use CLMVC\Controllers\BaseController;
use CLMVC\Core\Container;
use CLMVC\Helpers\Communication;

/**
 * Class Routes
 * @package CLMVC\Core\Http
 */
class Routes
{
    private static $prefix = '';
    private static $self = null;
    /**
     * @var Route[]
     */
    private $routes = [];
    private $priorityRoutes = [];
    private $routed = null;

    /**
     * @param $prefix
     * @param $callback
     * @return Routes|null
     */
    public static function group($prefix, $callback)
    {
        self::$prefix = $prefix;
        call_user_func($callback);
        self::$prefix = '';
        return self::instance();
    }

    /**
     * @return Routes|null
     */
    public static function instance()
    {
        self::$self = self::$self ?: new Routes();
        return self::$self;
    }

    /**
     * @param $route
     * @param $callback
     * @param array $params
     * @return Routes
     */
    public static function get($route, $callback, $params = [])
    {
        return self::instance()->add(self::$prefix . '/' . ltrim($route, "/"), $callback, $params, 'get');
    }

    /**
     * @param $route
     * @param $callback
     * @param array $params
     * @param string $method
     * @param bool $priority
     * @return $this
     */
    public function add($route, $callback, $params = [], $method = 'get', $priority = false)
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
     * @param $route
     * @param $callback
     * @param array $params
     * @return Routes
     */
    public static function delete($route, $callback, $params = [])
    {
        return self::instance()->add(self::$prefix . '/' . ltrim($route, "/"), $callback, $params, 'delete');
    }

    /**
     * @param $route
     * @param $callback
     * @param array $params
     * @return Routes
     */
    public static function update($route, $callback, $params = [])
    {
        return self::instance()->add(self::$prefix . '/' . ltrim($route, "/"), $callback, $params, 'put');
    }

    /**
     * @param $route
     * @param $callback
     * @param array $params
     * @return Routes
     */
    public static function create($route, $callback, $params = [])
    {
        return self::instance()->add(self::$prefix . '/' . ltrim($route, "/"), $callback, $params, 'post');
    }

    /**
     * @return bool|null
     */
    public function routeExists()
    {
        $routes = array_merge($this->priorityRoutes, $this->routes);
        $uri = $_SERVER['REQUEST_URI'];
        $method = Communication::getMethod();
        /**
         * @var Route $route
         */
        foreach ($routes as $route) {
            if ($route->match($uri, $method)) {
                return $this->routed;
            }
        }
        $this->routed = false;

        return $this->routed;

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
            $matches = $route->match($uri, $method);
            if ($matches) {
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
<?php
namespace CLMVC\Core\Http;
use CLMVC\Controllers\BaseController;
use CLMVC\Helpers\Communication;

class Routes {
    /**
     * @var Route[]
     */
    private $routes = array();
    private $priorityRoutes = array();
    function add($route,$callback , $params = array(), $method = 'get', $priority = false) {
        $theRoute = new Route($route, $callback, $params, $method);
        if ($priority)
            $this->priorityRoutes[] = $theRoute;
        else
            $this->routes[] = $theRoute;
    }

    /**
     * Takes request uri and routes to controller.
     */
    function routing() {
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
                 * @var BaseController $ctrl
                 */
                $ctrl = new $controller(false);
                $ctrl->init();
                $action = $array[1];
                if ($action == ':action')
                    $action = str_replace(':action', $matches['action'], $action);
                $ctrl->executeAction($action, $params);
                break;
            }
        }
    }
}

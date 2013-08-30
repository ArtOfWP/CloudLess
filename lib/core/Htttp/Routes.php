<?php

class Routes {
    /**
     * @var ARoute[]
     */
    private $routes = array();
    function add($route,$callback , $params = array()) {
        $this->routes[] = new ARoute($route, $callback, $params);
    }

    function routing() {
        $uri = $_SERVER['REQUEST_URI'];
        foreach ($this->routes as $route) {
            if ($matches = $route->match($uri)) {
                $params = $route->params($uri);
                $array = $route->getCallback();
                require  CLOUDLESS_APP_DIR . $array[0] . 'Controller.php';
                $path = explode('/', $array[0]);
                $controller = $path[sizeof($path) - 1];
                $controller .= 'Controller';
                /**
                 * @var BaseController $controller
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
<?php
class Route {

    private $route;
    private $params;
    private $callback;

    function __construct($route, $callback, $params) {
        $this->params = $params;
        $this->route = $this->build($route, $params);
        $this->callback = $callback;
    }

    function match($uri) {
        preg_match($this->route, $uri, $matches);
        return $matches;
    }

    private function build($route, $params) {
        if (strpos($route, '*') === 0)
            $route = str_replace('*', '\/?', $route);
        $route = str_replace(':action', '(?<action>[a-zA-Z0-9_\+\-%]+)', $route);
        foreach ($params as $param => $condition) {
            if (is_numeric($param)) {
                $route = str_replace(":$condition", '(?<'.$condition.'>[a-zA-Z0-9_\+\-%]+)', $route);
            } else {
                $route = str_replace(":$param", "(?<$param>$condition)", $route);
            }
        }
        $route = str_replace('/', '\/', $route);
        $route = str_replace('\\\\', '\\', $route);
        $route = rtrim($route, '/') . '\/?';
        return "/$route/";
    }

    function params($uri) {
        $matches = $this->match($uri);
        $params = array();
        foreach ($this->params as $param => $condition) {
            if (is_numeric($param)) {
                $params[$condition] = $matches[$condition];
            } else {
                $params[$param] = $matches[$param];
            }
        }
        return $params;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
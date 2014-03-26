<?php
namespace CLMVC\Core\Http;

/**
 * Class Route
 * @package CLMVC\Core\Http
 */
class Route {

    private $route;
    private $method;
    private $params;
    private $callback;

    /**
     * @param $route
     * @param $callback
     * @param $params
     * @param $method
     */
    function __construct($route, $callback, $params, $method = 'get') {
        $this->params = $params;
        $this->method = $method;
        $this->route = $this->build($route, $params);
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param $uri
     * @param $method
     * @return mixed
     */
    public function match($uri, $method ='get') {
        if ($this->method != $method)
            return null;
        preg_match($this->route, $uri, $matches);
        return $matches;
    }

    /**
     * @param $route
     * @param $params
     * @return string
     */
    private function build($route, $params) {
        if (strpos($route, '*') === 0)
            $route = str_replace('*', '\/?', $route);
        $route = str_replace(':action', '(?<action>[a-zA-Z0-9_\+\-%\$]+)', $route);
        foreach ($params as $param => $condition) {
            if (is_numeric($param)) {
                $route = str_replace(":$condition", '(?<'.$condition.'>[a-zA-Z0-9_\+\-%\$]+)', $route);
            } else {
                $route = str_replace(":$param", "(?<$param>$condition)", $route);
            }
        }
        $route = str_replace('\\\\', '\\', $route);
        $route = preg_quote($route);
        $route = rtrim($route, "\\/") . '\/?';
        return "#$route#";
    }

    /**
     * @param $uri
     * @param string $method
     * @return array
     */
    function params($uri, $method = 'get') {
        $matches = $this->match($uri, $method);
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

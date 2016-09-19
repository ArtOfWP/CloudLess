<?php

namespace CLMVC\Core\Http;

/**
 * Class Route
 * @package CLMVC\Core\Http
 */
class Route
{
    private $route;
    private $method;
    private $params;
    private $callback;
    private $content_type;
    private $route_path;

    /**
     * @param string $route
     * @param string|array $callback
     * @param array $params
     * @param string $method
     * @param string $content_type
     */
    public function __construct($route, $callback, $params, $method = 'get', $content_type = '')
    {
        $this->params = $params;
        $this->method = strtolower($method);
        $this->route = $this->build($route, $params);
        $this->route_path = $route;
        $this->callback = $callback;
        $this->content_type = $content_type;
    }

    /**
     * @param $route
     * @param $params
     *
     * @return string
     */
    private function build($route, $params)
    {
        if (strpos($route, '*') === 0) {
            $route = str_replace('*', '\/?', $route);
        }
        $route = str_replace(':action', '(?<action>[a-zA-Z0-9_\+\-%\$\.]+)', $route);
        foreach ($params as $param => $condition) {
            if (is_numeric($param)) {
                $route = str_replace(":$condition", '(?J)(?<' . $condition . '>[a-zA-Z0-9_\+\-%\$\.]+)', $route);
                continue;
            }
            $route = str_replace(":$param", "(?<$param>$condition)", $route);
        }
        $route = str_replace('\\\\', '\\', $route);
        $route = '^' . rtrim($route, '\\/') . '\/?([\#\?].*)?$';

        return "#$route#";
    }

    /**
     * The regex versiong of the route
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param $uri
     * @param string $method
     *
     * @return array
     */
    public function params($uri, $method = 'get')
    {
        $matches = $this->match($uri, $method);
        $params = array();
        foreach ($this->params as $param => $condition) {
            if (is_numeric($param)) {
                $params[$condition] = $matches[$condition];
                continue;
            }
            $params[$param] = $matches[$param];
        }

        return $params;
    }

    /**
     * @param $uri
     * @param string $method
     * @param array $content_types
     * @return mixed
     */
    public function match($uri, $method = 'get', $content_types = [])
    {
        if ($this->method !== strtolower($method)) {
            return false;
        }
        if ($this->content_type && !in_array($this->content_type, $content_types)) {
            return false;
        }
        preg_match($this->route, $uri, $matches);

        return $matches;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * The pure route string without any regex parts
     * @return string
     */
    public function getRoutePath()
    {
        return $this->route_path;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($this->method);
    }
}

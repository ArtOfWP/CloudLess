<?php

namespace CLMVC\Controllers;

use CLMVC\Core\AoiSoraSettings;
use CLMVC\Events\Filter;

class Views
{
    private $controller;

    /**
     * @param BaseController $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $template
     *
     * @return string Empty string if path is not found.
     */
    public function findView($controller, $action, $template = 'php')
    {
        $viewPath = $this->controller->getViewPath();
        if (!is_array($viewPath)) {
            $viewPath = [$viewPath];
        }
        $viewPath = Filter::run('cl-view-path', [$viewPath, $this->controller, $controller, $action]);
        $viewPath = Filter::run("cl-view-path-{$controller}", [$viewPath, $this->controller, $controller, $action]);

        if ($viewPath) {
            if ($path = $this->viewPaths($viewPath, strtolower($controller).DIRECTORY_SEPARATOR.strtolower($action).'.'.$template)) {
                return $path;
            }
            if ($path = $this->viewPaths($viewPath, $controller.DIRECTORY_SEPARATOR.$action.'.'.$template)) {
                return $path;
            }
            if ($path = $this->viewPaths($viewPath, $controller.'-'.$action.'.'.$template)) {
                return $path;
            }
        }
        $apps = AoiSoraSettings::getApplications();
        $lc_controller = strtolower($controller);
        $lc_action = strtolower($action);
        foreach ($apps as $app) {
            $path = $app['path'];
            if (file_exists($path.VIEWS.$controller.'/'.$action.'.php')) {
                return $path.VIEWS.$controller.'/'.$action.'.php';
            }
            if (file_exists($path.VIEWS.$lc_controller.'/'.$lc_action.'.php')) {
                return $path.VIEWS.$lc_controller.'/'.$lc_action.'.php';
            }
        }

        return '';
    }

    private function viewPaths($paths, $view)
    {
        foreach ($paths as $path) {
            if (file_exists($path.DIRECTORY_SEPARATOR.trim($view, '/'))) {
                return $path.DIRECTORY_SEPARATOR.trim($view, '/');
            }
        }

        return false;
    }

    public function findLayout($template = 'php')
    {
        if ($this->controller->getViewPath()) {
            if ($path = $this->viewPaths($this->controller->getViewPath(), 'Layouts'.DIRECTORY_SEPARATOR.'default'.'.'.$template)) {
                return $path;
            }
        }

        return '';
    }
}

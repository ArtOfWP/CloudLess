<?php
namespace CLMVC\Controllers;


use CLMVC\Core\AoiSoraSettings;
use CLMVC\Events\Filter;

class Views {
    private $controller;

    /**
     * @param BaseController $controller
     */
    public function __construct($controller) {
        $this->controller = $controller;
    }
    /**
     * @param string $controller
     * @param string $action
     * @param string $template
     * @return string Empty string if path is not found.
     */
    public function findView($controller, $action, $template = 'php') {
        $viewPath = Filter::run("cl-view-path", [$this->controller->getViewPath(), $this->controller, $controller, $action]);
        $viewPath = Filter::run("cl-view-path-{$controller}", [$viewPath, $this->controller, $controller, $action]);
        if ($viewPath) {
            if (file_exists(rtrim($viewPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . '.' . $template))
                return rtrim($viewPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . '.' . $template;
            if (file_exists(rtrim($viewPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $controller . '-'. $action . '.' . $template))
                return rtrim($viewPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $controller . '-' . $action . '.' . $template;
        }
        $apps = AoiSoraSettings::getApplications();
        $lc_controller = strtolower($controller);
        $lc_action = strtolower($action);
        foreach ($apps as $app) {
            $path = $app['path'];
            if (file_exists($path . VIEWS . $controller . '/' . $action . '.php'))
                return $path . VIEWS . $controller . '/' . $action . '.php';
            if (file_exists($path . VIEWS . $lc_controller . '/' . $lc_action . '.php'))
                return $path . VIEWS . $lc_controller . '/' . $lc_action . '.php';
        }
        return '';
    }

    public function findLayout($template = 'php') {
        if ($this->controller->getViewPath()) {
            if (file_exists(rtrim($this->controller->getViewPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'default' . '.' . $template))
                return rtrim($this->controller->getViewPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'default' .  '.' . $template;
        }
        return '';
    }
}
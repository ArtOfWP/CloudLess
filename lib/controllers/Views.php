<?php
namespace CLMVC\Controllers;


use CLMVC\Core\AoiSoraSettings;

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
     * @return string Empty string if path is not found.
     */
    public function findView($controller, $action) {
        if ($this->controller->getViewPath()) {
            return rtrim($this->controller->getViewPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action . '.php';
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

    public function findLayout() {
        if ($this->controller->getViewPath()) {
            if (file_exists(rtrim($this->controller->getViewPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'default' . '.php'))
                return rtrim($this->controller->getViewPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . 'default' . '.php';
        }
        return '';
    }
}
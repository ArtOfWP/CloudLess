<?php
namespace CLMVC\Controllers;

use CLMVC\Events\View;

class Rendering {

    /**
     * @var bool if should render
     */
    private $render = true;
    private $views;
    /**
     * @var BaseController
     */
    private $controller;
    private $controllerName;
    public function __construct($controller) {
        $this->controller = $controller;
        $class = get_class($controller);
        $pos = strrpos($class, '\\');
        $className = substr($class, $pos + 1);
        $this->controllerName = str_replace('Controller', '', $className);
        $this->views = new Views($controller);
    }

    /**
     * @return mixed
     */
    private function getControllerName() {
        return $this->controllerName;
    }

    /**
     * Renders a controller and its action.
     * @param $controller
     * @param $action
     */
    public function Render($controller, $action) {
        $view = $this->views->findView($controller, $action);
        if ($view) {
            extract($this->getBag(), EXTR_REFS);
            $section = View::generate($controller . '-render-pre' . ucfirst($action), $this);
            ob_start();
            include($view);
            $viewcontent = $section . ob_get_contents();
            ob_end_clean();
            View::render($controller . '-render-post' . ucfirst($action), $this);
            $viewcontent .= $section;
        } else
            $viewcontent = 'Could not find view: ' . $view;
        $this->render = false;
        RenderedContent::set($viewcontent);
    }


    /**
     * Renders the current Controllers action
     * @param string $action The action to render
     */
    public function RenderToAction($action) {
        $this->Render($this->getControllerName(), $action);
    }

    /**
     * Renders a file.
     * @param string $filePath
     */
    public function RenderFile($filePath) {
        ob_start();
        if (file_exists($filePath)) {
            extract($this->getBag(), EXTR_REFS);
            include($filePath);
            $viewcontent = ob_get_contents();
        } else
            $viewcontent = 'Could not find view: ' . $$filePath;
        $this->render = false;
        ob_end_clean();
        RenderedContent::set($viewcontent);
    }

    /**
     * The text to render to screen.
     * @param string $text
     */
    public function RenderText($text) {
        $this->render = false;
        RenderedContent::set($text);
    }

    private function getBag() {
        return $this->controller->getBag();
    }

    public function renderedContent() {
        return RenderedContent::get();
    }

    public function canRender($state = null) {
        if (!is_null($state))
            $this->render = $state;
        return $this->render;
    }
}
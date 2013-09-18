<?php
namespace CLMVC\Controllers\Render;

use CLMVC\Controllers\BaseController;
use CLMVC\Controllers\Views;
use CLMVC\Events\Filter;
use CLMVC\Events\View;

class Rendering {

    /**
     * @var bool if should render
     */
    private $render = true;
    /**
     * @var Views
     */
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
        $view_path = $this->views->findView($controller, $action, $this->getTemplate());
        if ($view_path) {
            $tags = Filter::run('view-tags', array(array(), $this->controller));
            $section = View::generate($controller . '-render-pre' . ucfirst($action), $this->controller);
            $engine = RenderingEngines::getEngine($this->getTemplate(), $this->controller->getViewPath());
            $viewcontent = $section . $engine->render($view_path, $this->getBag());
            $section = View::generate($controller . '-render-post' . ucfirst($action), $this->controller);
            $viewcontent .= $section;
            $layout_path = $this->views->findLayout($this->getTemplate());
            if ($layout_path) {
                $engine = RenderingEngines::getEngine($this->getTemplate());
                $layout_content = $engine->render($layout_path, $this->getBag()+$tags, Filter::run("{$this->controllerName}-{$action}-blocks", array(array('view' => $viewcontent))));
            } else {
                $layout_content = $viewcontent;
            }
            $viewcontent = $layout_content;

        } else
            $viewcontent = 'Could not find view: ' . $view_path;
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
        return $this->controller->getBag()->toArray();
    }

    public function renderedContent() {
        return RenderedContent::get();
    }

    public function canRender($state = null) {
        if (!is_null($state))
            $this->render = $state;
        return $this->render;
    }

    /**
     * @param $layout
     * @param $tag
     * @param $viewcontent
     * @return string
     */
    public function replaceTag($layout, $tag, $viewcontent) {
        $viewPosStart = strpos($layout, $tag);
        if ($viewPosStart !== false) {
            $viewPosEnd = $viewPosStart + strlen($tag);
            $layout = substr($layout, 0, $viewPosStart) . $viewcontent . substr($layout, $viewPosEnd);
        }
        return $layout;
    }

    /**
     * @return mixed
     */
    public function getTemplate() {
        return $this->controller->getTemplateType();
    }
}

<?php
namespace CLMVC\Controllers\Render;

use CLMVC\Controllers\BaseController;
use CLMVC\Controllers\Views;
use CLMVC\Events\Filter;

class Rendering {

    /**
     * @var bool if should render
     */
    private $render = true;
    /**
     * @var Views
     */
    public $views;
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
     * @throws RenderException
     */
    public function Render($controller, $action) {
        $view_path = $this->views->findView($controller, $action, $this->getTemplate());
        if ($view_path) {
            $tags = Filter::run('view-tags', array(array(), $this->controller));
            $engine = RenderingEngines::getEngine($this->getTemplate(), $this->controller->getViewPath());
            $view_content = $engine->render($view_path, $this->getBag());
            $layout_path = $this->views->findLayout($this->getTemplate());
            if ($layout_path) {
                $engine = RenderingEngines::getEngine($this->getTemplate(), $view_path);
                $view_content = $engine->render($layout_path, $this->getBag()+$tags, Filter::run("{$this->controllerName}-{$action}-blocks", array(array('view' => $view_content))));
            }
            $this->render = false;
            RenderedContent::set($view_content);
            return;
        }
        $view_content = "Could not find view for $controller, $action in: " . $view_path;
        trigger_error($view_content,E_USER_WARNING);
    }


    /**
     * Renders the current Controllers action
     * @param string $action The action to render
     * @throws RenderException
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
            $viewcontent = 'Could not find view: ' . $filePath;
        $this->render = false;
        ob_end_clean();
        RenderedContent::set($viewcontent);
    }

    /**
     * The text to render to screen.
     * @param string $text
     * @param bool $end
     */
    public function RenderText($text, $end = false) {
        $this->render = false;
        RenderedContent::set($text);
        if ($end) {
            RenderedContent::endIt(true);
        }
    }

    /**
     * Encodes $data as JSON and flushes it and exists request.
     * @param $data
     */
    public function RenderJson($data) {
        global $aoisora_headers;
        if(!is_array($aoisora_headers))
            $aoisora_headers=[];
        $aoisora_headers[] = 'Content-Type: application/json; charset=UTF-8';
        $this->render = false;
        RenderedContent::set(json_encode($data, JSON_UNESCAPED_UNICODE));
        RenderedContent::endIt(true);
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

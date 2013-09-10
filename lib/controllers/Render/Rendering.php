<?php
namespace CLMVC\Controllers\Render;

use CLMVC\Controllers\BaseController;
use CLMVC\Controllers\Views;
use CLMVC\Core\Includes\ScriptIncludes;
use CLMVC\Core\Includes\StyleIncludes;
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
            if ('php' == $this->getTemplate()) {
                extract($this->getBag(), EXTR_REFS);
                if (!isset($title))
                    $title = '';
                ob_start();
                include($view_path);
                $viewcontent = $section . ob_get_contents();
                ob_end_clean();
            } else {
                $engine = RenderingEngines::getEngine($this->getTemplate());
                $viewcontent = $engine->render($view_path, $this->getBag());
            }
            View::render($controller . '-render-post' . ucfirst($action), $this->controller);
            $viewcontent .= $section;
            $layout_path = $this->views->findLayout($this->getTemplate());
            if ($layout_path) {
                if ('php' == $this->getTemplate()) {
                    extract($this->getBag(), EXTR_REFS);
                    if (!isset($title))
                        $title = '';
                    ob_start();
                    include $layout_path;
                    $layout_content = ob_get_contents();
                    ob_end_clean();
                    foreach ($tags as $tagTuple) {
                        list($tag, $content) = $tagTuple;
                        $layout_content = $this->replaceTag($layout_content, $tag, $content);
                    }
                    $layout_content = $this->replaceTag($layout_content, '{{view}}', $viewcontent);
                } else {
                    $engine = RenderingEngines::getEngine($this->getTemplate());
                    $layout_content = $engine->render($layout_path, $this->getBag(), Filter::run("{$this->controllerName}-{$action}-blocks", array(array('view' => $viewcontent))));
                    if (!file_exists(CLMVC_CACHE_PATH  . 'Layout' . DIRECTORY_SEPARATOR ))
                        mkdir(CLMVC_CACHE_PATH  . 'Layout', '755');
                    $cached_layout = CLMVC_CACHE_PATH  . 'Layout' . DIRECTORY_SEPARATOR . 'default.php';
                    if (!file_exists($cached_layout))
                        file_put_contents($cached_layout, $layout_content);
                    ob_start();
                    extract($this->getBag(), EXTR_REFS);
                    $vars = array();
                    foreach ($tags as $tagTuple) {
                        list($tag, $content) = $tagTuple;
                        $vars[trim($tag, "{{}}")] = $content;
                    }
                    extract($vars);
                    if (!isset($title))
                        $title = '';
                    include $cached_layout;
                    $layout_content = ob_get_contents();
                    ob_end_clean();
                }
            } else {
                $layout_content = '';
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

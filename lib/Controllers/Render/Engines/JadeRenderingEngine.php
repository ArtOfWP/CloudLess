<?php
namespace CLMVC\Controllers\Render\Engines;

use CLMVC\Interfaces\IRenderingEngine;
use Jade\Jade;

class JadeRenderingEngine implements IRenderingEngine {
    private $viewpaths;

    /**
     * @return string mixed
     */
    public function getFileTypeSupport() {
        return 'jade';
    }

    /**
     * Returns the rendered content
     * @param string $file_path
     * @param array $scope
     * @return string
     */
    public function render($file_path, $scope = array()) {
        $template = file_get_contents($this->viewpaths . DIRECTORY_SEPARATOR . $file_path . '.' . $this->getFileTypeSupport());
        $renderer = new Jade(true);
        $parsed = $renderer->render($template , $scope, array($this->viewpaths));
        ob_start();
        extract($scope, EXTR_REFS);
        eval('?>' . $parsed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function __construct($viewpaths) {

        $this->viewpaths = $viewpaths;
    }
}
<?php
namespace CLMVC\Controllers\Render\Engines;


use CLMVC\Interfaces\IRenderingEngine;
use Jade\Jade;

class JadeRenderingEngine implements IRenderingEngine {

    /**
     * @return string mixed
     */
    public function getFileTypeSupport() {
        return 'jade';
    }

    /**
     * Returns the rendered content
     * @param string $content
     * @param array $scope
     * @return string
     */
    public function render($content, $scope = array()) {
        $renderer = new Jade(true);
        return $renderer->render($content, $scope);
    }
}
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
     * @param string $file_path
     * @param array $scope
     * @param array $blocks
     * @return string
     */
    public function render($file_path, $scope = array(), $blocks = array()) {
        $template = file_get_contents($file_path);
        if (isset($blocks['view']))
            $template = str_replace('include view', $blocks['view'], $template);
        $renderer = new Jade(true);
        $parsed = $renderer->render($template , $scope);
        ob_start();
        extract($scope, EXTR_REFS);
        eval('?>' . $parsed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
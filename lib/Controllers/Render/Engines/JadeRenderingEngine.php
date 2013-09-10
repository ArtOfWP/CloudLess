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
        file_put_contents($file_path.'old', $template);
        foreach($blocks as $block => $content)
            $template = str_replace('block ' . $block, $content, $template);
        $renderer = new Jade(true);
        return $renderer->render($template , $scope);
    }
}
<?php

namespace CLMVC\Controllers\Render\Engines;

use CLMVC\Interfaces\IRenderingEngine;

class PhpRenderingEngine implements IRenderingEngine
{
    private $viewpaths;

    /**
     * @return string mixed
     */
    public function getFileTypeSupport()
    {
        return 'php';
    }

    /**
     * Returns the rendered content.
     *
     * @param string $filePath
     * @param array  $scope
     * @param array  $blocks
     *
     * @return string
     */
    public function render($filePath, $scope = array(), $blocks = array())
    {
        extract($scope, EXTR_REFS);
        extract($blocks, EXTR_REFS);
        if (!isset($title)) {
            $title = '';
        }
        ob_start();
        include $filePath;
        $viewcontent = ob_get_contents();
        ob_end_clean();

        return $viewcontent;
    }

    public function __construct($viewpaths)
    {
        $this->viewpaths = $viewpaths;
    }
}

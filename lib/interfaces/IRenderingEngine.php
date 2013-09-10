<?php
namespace CLMVC\Interfaces;

/**
 * Class IRenderingEngine
 * Defines an interface for rendering engines.
 * This are used in controllers to parse the layout and
 * views connected with a controller and action.
 * @package CLMVC\Interfaces
 */
interface IRenderingEngine {
    /**
     * @return string mixed
     */
    public function getFileTypeSupport();

    /**
     * Returns the rendered content
     * @param string $filePath
     * @param array $scope
     * @param array $blocks
     * @return string
     */
    public function render($filePath, $scope = array(), $blocks = array());
}
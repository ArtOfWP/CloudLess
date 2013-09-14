<?php
namespace CLMVC\Controllers\Render;

use CLMVC\Interfaces\IRenderingEngine;

class RenderingEngines {
    private static $engines = array();

    /**
     * @param string $fileType
     * @return IRenderingEngine
     * @throws RenderException
     */
    static function getEngine($fileType) {
        if (isset(self::$engines[$fileType])) {
            return new self::$engines[$fileType]();
        } else
            throw new RenderException(sprintf('No registered engines can handle %s.', $fileType));
    }

    /**
     * @param string $fileType
     * @param string $classPath
     */
    static function registerEngine($fileType, $classPath) {
        self::$engines[$fileType]= $classPath;
    }
}
<?php
namespace CLMVC\Controllers\Render;


use CLMVC\Interfaces\IRenderingEngine;

class RenderingEngines {
    private static $engines = array();
    static function getEngine($fileType) {
        if (isset(self::$engines[$fileType]))
            return self::$engines[$fileType];
        else
            throw new RenderException(sprintf('No registered engines can handle %s.', $fileType));
    }
    static function registerEngine(IRenderingEngine $engine) {
        self::$engines[$engine->getFileTypeSupport()]= $engine;
    }
}
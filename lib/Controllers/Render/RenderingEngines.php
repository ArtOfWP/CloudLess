<?php

namespace CLMVC\Controllers\Render;

use CLMVC\Interfaces\IRenderingEngine;

class RenderingEngines
{
    private static $engines = array();

    /**
     * @param string $fileType
     * @param string $viewPath
     *
     * @return IRenderingEngine
     *
     * @throws RenderException
     */
    public static function getEngine($fileType, $viewPath)
    {
        if (isset(self::$engines[$fileType])) {
            return new self::$engines[$fileType]($viewPath);
        } else {
            throw new RenderException(sprintf('No registered engines can handle %s.', $fileType));
        }
    }

    /**
     * @param string $fileType
     * @param string $classPath
     */
    public static function registerEngine($fileType, $classPath)
    {
        self::$engines[$fileType] = $classPath;
    }
}

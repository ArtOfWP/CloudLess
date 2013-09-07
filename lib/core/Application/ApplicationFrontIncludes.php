<?php
use CLMVC\Core\Includes\FrontInclude;
use CLMVC\Core\Includes\ScriptIncludes;
use CLMVC\Core\Includes\StyleIncludes;

class ApplicationFrontIncludes {
    private $script, $style;
    public function __construct() {
        $this->script = new ScriptIncludes();
        $this->style = new StyleIncludes();
    }

    public static function registerScript(FrontInclude $include) {
        self::instance()->getScriptIncludes()->register($include);
    }

    public static function registerStyle(FrontInclude $include) {
        self::instance()->getStyleIncludes()->register($include);
    }

    public static function enqueueScript($location, $handle) {
        self::instance()->getScriptIncludes()->enqueue($location, $handle);
    }

    public static function enqueueStyle($location, $handle) {
        self::instance()->getScriptIncludes()->enqueue($location, $handle);
    }

    private static function instance() {
        static $instance;
        if (is_null($instance)) {
            $instance = new ApplicationFrontIncludes();
        }
        return $instance;
    }

    public function getScriptIncludes() {
        return $this->script;
    }

    public function getStyleIncludes() {
        return $this->style;
    }
}
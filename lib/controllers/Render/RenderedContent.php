<?php
namespace CLMVC\Controllers\Render;

class RenderedContent {
    private static $renderedContent;
    private static $rendered = false;

    static function set($content) {
        self::$renderedContent = $content;
        self::$rendered = true;
    }
    static function get() {
        return self::$renderedContent;
    }
    static function flush() {
        echo self::get();
    }

    static function hasRendered() {
        return self::$rendered;
    }
}
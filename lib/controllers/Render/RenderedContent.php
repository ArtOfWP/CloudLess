<?php
namespace CLMVC\Controllers\Render;

class RenderedContent {
    static $renderedContent;

    static function set($content) {
        self::$renderedContent = $content;
    }
    static function get() {
        return self::$renderedContent;
    }
    static function flush() {
        echo self::get();
    }
}
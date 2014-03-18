<?php
namespace CLMVC\Controllers\Render;

class RenderedContent {
    private static $endIt;
    private static $renderedContent;
    private static $rendered = false;
    private static $renderedBlocks = array();

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

    static function endFlush() {
        self::flush();
        exit;
    }

    static function hasRendered() {
        return self::$rendered;
    }

    static function setBlock($block , $content) {
        self::$renderedBlocks[$block] = $content;
        self::$rendered = true;
    }
    static function getBlock($block) {
        return self::$renderedBlocks[$block];
    }

    static function flushBlock($block) {
        echo self::$renderedBlocks[$block];
    }

    static function endIt($end = null) {
        if (is_null($end))
            return self::$endIt;
        self::$endIt = $end;
    }
}
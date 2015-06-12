<?php

namespace CLMVC\Controllers\Render;

class RenderedContent
{
    private static $endIt;
    private static $renderedContent;
    private static $rendered = false;
    private static $renderedBlocks = array();

    /**
     * @param string $content
     */
    public static function set($content)
    {
        self::$renderedContent = $content;
        self::$rendered = true;
    }
    public static function get()
    {
        return self::$renderedContent;
    }
    public static function flush()
    {
        echo self::get();
        self::clear();
    }

    public static function endFlush()
    {
        self::flush();
        exit;
    }

    public static function hasRendered()
    {
        return self::$rendered;
    }

    public static function setBlock($block, $content)
    {
        self::$renderedBlocks[$block] = $content;
        self::$rendered = true;
    }
    public static function getBlock($block)
    {
        return self::$renderedBlocks[$block];
    }

    public static function flushBlock($block)
    {
        echo self::$renderedBlocks[$block];
        unset(self::$renderedBlocks[$block]);
    }

    public static function clear() {
        self::$renderedContent='';
        self::$renderedBlocks=[];
    }
    /**
     * @param boolean $end
     * @return bool
     */
    public static function endIt($end = false)
    {
        if (!$end) {
            return self::$endIt;
        }
        self::$endIt = $end;

        return $end;
    }
}

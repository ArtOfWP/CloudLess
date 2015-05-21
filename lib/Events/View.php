<?php

namespace CLMVC\Events;

/**
 * Class View.
 */
class View
{
    public static $ViewSections = array();

    /**
     * Register a section.
     *
     * @param $section
     * @param $callback
     * @param int $priority
     */
    public static function register($section, $callback, $priority = 100)
    {
        if (!isset(self::$ViewSections[$section]['handler'])) {
            if (!isset(self::$ViewSections)) {
                self::$ViewSections = array();
            }
            $id = generate_hash_for_array($callback);
            self::$ViewSections[$section][$priority][$id] = $callback;

            return;
        }
        $handler = self::$ViewSections[$section]['handler'];
        call_user_func($handler, $section, $callback);
    }

    /**
     * Register a handler for the section.
     *
     * @param string       $section
     * @param array|string $callback
     */
    public static function registerHandler($section, $callback)
    {
        self::$ViewSections[$section]['handler'] = $callback;
    }

    /**
     * Generate the section.
     *
     * @param string       $section
     * @param array|object $params
     * @param bool         $isArray
     *
     * @return string
     */
    public static function generate($section, $params = array(), $isArray = false)
    {
        $priorities = array_key_exists_v($section, self::$ViewSections, array());
        if (!$isArray && !is_array($params)) {
            $params = array($params);
        }

        $ob = self::runObStart($section, $priorities);
        if (self::hasCustomHandler($section)) {
            call_user_func_array($priorities['handler'].'_run', array($section, $params));
        } else {
            self::runFunctions($params, $priorities);
        }
        return self::getObContents($ob);
    }

    /**
     * Render the section.
     *
     * @param string       $section
     * @param array|object $params
     * @param bool         $isArray
     */
    public static function render($section, $params = array(), $isArray = false)
    {
        $sections = self::generate($section, $params, $isArray);
        if ($sections) {
            echo $sections;
        }
    }

    /**
     * Check if section is registered.
     *
     * @param string $section
     *
     * @return bool
     */
    public static function isRegistered($section)
    {
        return array_key_exists($section, self::$ViewSections);
    }

    /**
     * Checks if section has custom handler.
     *
     * @param string $section
     *
     * @return bool
     */
    public static function hasCustomHandler($section)
    {
        return isset(self::$ViewSections[$section]['handler']);
    }

    /**
     * @param $section
     * @param $priorities
     * @return bool
     */
    private static function runObStart($section, $priorities)
    {
        if (self::hasCustomHandler($section) || is_array($priorities)) {
            ob_start();
            return true;
        }
        return false;
    }

    /**
     * @param $ob
     * @return string
     */
    private static function getObContents($ob)
    {
        return $ob ? ob_get_clean() : '';
    }

    /**
     * @param $params
     * @param $priorities
     */
    private static function runFunctions($params, $priorities)
    {
        ksort($priorities);
        foreach ($priorities as $functions) {
            if (is_array($functions)) {
                foreach ($functions as $function) {
                    call_user_func_array($function, $params);
                }
            }
        }
    }
}

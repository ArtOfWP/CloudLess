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
            $id=generate_hash_for_array($callback);
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
        $priorities = array_key_exists_v($section, self::$ViewSections);
        if (self::hasCustomHandler($section)) {
            ob_start();
            if (!$isArray && !is_array($params)) {
                $params = array($params);
            }
            call_user_func_array($priorities['handler'].'_run', array($section, $params));
            $sections = ob_get_contents();
            ob_end_clean();

            return $sections;
        }

        if (is_array($priorities)) {
            ksort($priorities);
            ob_start();
            if (!$isArray && !is_array($params)) {
                $params = array($params);
            }
            foreach ($priorities as $functions) {
                if (is_array($functions)) {
                    foreach ($functions as $function) {
                        call_user_func_array($function, $params);
                    }
                }
            }
            $sections = ob_get_contents();
            ob_end_clean();

            return $sections;
        }

        return '';
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
}

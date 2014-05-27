<?php
namespace CLMVC\Events;
use CLMVC\Helpers;
/**
 * Class View
 */
class View {
    public static $ViewSections = array();

    /**
     * Register a section
     * @param $section
     * @param $callback
     * @param int $priority
     */
    static function register($section, $callback, $priority = 100)
    {
        if (!isset(self::$ViewSections[$section]['handler'])) {
            if (!isset(self::$ViewSections))
                self::$ViewSections = array();
            if (is_array($callback)) {
                $id  = is_string($callback[0])?
                    hash('md5', $callback[0] . $callback[1]):
                    hash('md5', get_class($callback[0]) . $callback[1]);
            } elseif (is_string($callback))
                $id = hash('md5', $callback);
            else
                $id = spl_object_hash($callback).time();
            self::$ViewSections[$section][$priority][$id] = $callback;
            return;
        }
        $handler = self::$ViewSections[$section]['handler'];
        call_user_func($handler, $section, $callback);
    }

    /**
     * Register a handler for the section
     * @param string $section
     * @param array|string $callback
     */
    static function registerHandler($section, $callback) {
        self::$ViewSections[$section]['handler'] = $callback;
    }

    /**
     * Generate the section
     * @param string $section
     * @param array|object $params
     * @param bool $isArray
     * @return string
     */
    static function generate($section, $params = array(), $isArray = false) {
        $priorities = array_key_exists_v($section, self::$ViewSections);
        if (self::hasCustomHandler($section)) {
            ob_start();
            if (!$isArray && !is_array($params))
                $params = array($params);
            call_user_func_array($priorities['handler'] . '_run',array($section,$params));
            $sections = ob_get_contents();
            ob_end_clean();
            return $sections;
        }

        if ($priorities)
            ksort($priorities);
        if (is_array($priorities)) {
            ob_start();
            if (!$isArray && !is_array($params))
                $params = array($params);
            foreach ($priorities as $functions) {
                if (is_array($functions))
                    foreach ($functions as $function) {
                        if (!is_callable($function)) {
                            if (is_array($function))
                                if (is_string($function[0]))
                                    $message = implode('::', $function);
                                else
                                    $message = get_class($function[0]) . '->' . $function[1];
                            else
                                $message = $function;
                            trigger_error('View cannot call ' . $message . ' it does not exist.', E_USER_WARNING);
                            continue;
                        }
                        call_user_func_array($function, $params);
                    }
            }
            $sections = ob_get_contents();
            ob_end_clean();
            return $sections;
        }
        return '';
    }

    /**
     * Render the section
     * @param string $section
     * @param array|object $params
     * @param bool $isArray
     */
    static function render($section, $params = array(), $isArray = false) {
        $sections = self::generate($section, $params, $isArray);
        if ($sections)
            echo $sections;
    }

    /**
     * Check if section is registered
     * @param string $section
     * @return bool
     */
    static function isRegistered($section) {
        return array_key_exists($section, self::$ViewSections);
    }

    /**
     * Checks if section has custom handler
     * @param string $section
     * @return bool
     */
    static function hasCustomHandler($section) {
        return isset(self::$ViewSections[$section]['handler']);
    }
}

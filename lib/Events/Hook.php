<?php

namespace CLMVC\Events;

/**
 * Class Hook.
 */
class Hook
{
    public static $Hooks = array();

    /**
     * Register a hook.
     *
     * @param string                       $hook
     * @param array|string|\Closure|object $callback
     * @param int                          $priority where in list it should
     */
    public static function register($hook, $callback, $priority = 100)
    {
        if (!isset(self::$Hooks[$hook]['handler'])) {
            $id=generate_hash_for_array($callback);
            self::$Hooks[$hook][$priority][$id] = $callback;

            return;
        }
        $handler = self::$Hooks[$hook]['handler'];
        call_user_func($handler, $hook, $callback, $priority);
    }

    /**
     * Register an custom handler for hook.
     *
     * @param $hook
     * @param $callback
     */
    public static function registerHandler($hook, $callback)
    {
        self::$Hooks[$hook]['handler'] = $callback;
    }

    /**
     * Run the hook.
     *
     * @param string       $hook
     * @param array|object $params
     * @param bool         $isArray
     */
    public static function run($hook, $params = array(), $isArray = false)
    {
        $priorities = array_key_exists_v($hook, self::$Hooks);
        if (is_array($priorities)) {
            ksort($priorities);
            if ($isArray || !is_array($params)) {
                $params = array($params);
            }
            foreach ($priorities as $functions) {
                foreach ($functions as $function) {
                    call_user_func_array($function, $params);
                }
            }
        }
    }


    /**
     * Check if hook is registered.
     *
     * @param string $hook
     *
     * @return bool
     */
    public static function isRegistered($hook)
    {
        return array_key_exists($hook, self::$Hooks);
    }

    /**
     * Check if hook has a custom handler.
     *
     * @param string $hook
     *
     * @return bool
     */
    public static function hasCustomHandler($hook)
    {
        return isset(self::$Hooks[$hook]['handler']);
    }
}

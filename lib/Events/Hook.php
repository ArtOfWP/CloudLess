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
     * @param string $hook
     * @param array|string|\Closure|object $callback
     * @param int $priority where in list it should
     * @param string $handle
     */
    public static function register($hook, $callback, $priority = 100, $handle = '')
    {
        if (!isset(self::$Hooks[$hook]['handler'])) {
            $id = $handle ? $handle : generate_hash_for_array($callback);
            self::$Hooks[$hook][$priority][$id] = $callback;

            return;
        }
        $handler = self::$Hooks[$hook]['handler'];
        call_user_func($handler, $hook, $callback, $priority);
    }

    /**
     * @param $hook
     * @param $callback
     * @param int $priority
     */
    public static function removeCallback($hook, $callback, $priority = 100) {
        $id = generate_hash_for_array($callback);
        unset(self::$Hooks[$hook][$priority][$id]);
    }

    /**
     * @param $hook
     * @param $handle
     * @param int $priority
     */
    public static function removeHandle($hook, $handle, $priority = 100) {
        $id = $handle;
        unset(self::$Hooks[$hook][$priority][$id]);
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
            unset(self::$Hooks[$hook]);
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

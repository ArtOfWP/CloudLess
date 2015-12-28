<?php

namespace CLMVC\Events;

/**
 * Class Filter.
 */
class Filter
{
    /**
     * @var array
     */
    public static $FilterSections = array();

    /**
     * Register callback for and filter.
     *
     * @param $filter
     * @param $callback
     * @param int $priority
     */
    public static function register($filter, $callback, $priority = 100)
    {
        if (!isset(self::$FilterSections[$filter]['handler'])) {
            if (!isset(self::$FilterSections)) {
                self::$FilterSections = array();
            }
            $id = generate_hash_for_array($callback);
            self::$FilterSections[$filter][$priority][$id] = $callback;

            return;
        }
        $handler = self::$FilterSections[$filter]['handler'];
        call_user_func($handler, $filter, $callback, $priority);
    }

    public static function unregister($filter, $callback, $priority = 100) {
        if (!isset(self::$FilterSections[$filter]['handler'])) {
            if (!isset(self::$FilterSections)) {
                self::$FilterSections = array();
            }
            $id = generate_hash_for_array($callback);
            unset(self::$FilterSections[$filter][$priority][$id]);
            return;
        }
    }
    /**
     * Register an handler for filter.
     *
     * @param $filter
     * @param $callback
     */
    public static function registerHandler($filter, $callback)
    {
        self::$FilterSections[$filter]['handler'] = $callback;
    }

    /**
     * Run the filter.
     *
     * @param $filter
     * @param array $params
     *
     * @return mixed
     */
    public static function run($filter, $params = array())
    {
        if (isset($params[0])) {
            $value = $params[0];
        } else {
            $value = '';
        }
        $priorities = array_key_exists_v($filter, self::$FilterSections);
        if (is_array($priorities)) {
            ksort($priorities);
            if (!is_array($params)) {
                $params = array($params);
            }
            foreach ($priorities as $functions) {
                foreach ($functions as $function) {
                    $value = call_user_func_array($function, $params);
                    $params[0] = $value;
                }
            }
        }
        return $value;
    }

    /**
     * Check if filter has been registered.
     *
     * @param $filter
     *
     * @return bool
     */
    public static function isRegistered($filter)
    {
        return array_key_exists($filter, self::$FilterSections);
    }

    /**
     * Check if an filter has an handler.
     *
     * @param $filter
     *
     * @return bool
     */
    public static function hasCustomHandler($filter)
    {
        return isset(self::$FilterSections[$filter]['handler']);
    }

    public static function reset() {
         self::$FilterSections = array();
    }
}

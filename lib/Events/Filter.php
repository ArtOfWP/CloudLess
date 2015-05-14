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
            if (is_array($callback)) {
                $id = is_string($callback[0]) ?
                    hash('md5', $callback[0].$callback[1]) :
                    hash('md5', get_class($callback[0]).$callback[1]);
            } elseif (is_string($callback)) {
                $id = hash('md5', $callback);
            } else {
                $id = spl_object_hash($callback).time();
            }
            self::$FilterSections[$filter][$priority][$id] = $callback;

            return;
        }
        $handler = self::$FilterSections[$filter]['handler'];
        call_user_func($handler, $filter, $callback, $priority);
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
        if ($priorities) {
            ksort($priorities);
        }
        if ($priorities) {
            ksort($priorities);
        }
        if (is_array($priorities)) {
            if (!is_array($params)) {
                $params = array($params);
            }
            foreach ($priorities as $functions) {
                foreach ($functions as $function) {
                    if (!is_callable($function)) {
                        if (is_array($function)) {
                            if (is_string($function[0])) {
                                $message = implode('::', $function);
                            } else {
                                $message = get_class($function[0]).'->'.$function[1];
                            }
                        } else {
                            $message = $function;
                        }
                        trigger_error('Filter cannot call '.$message.' it does not exist.', E_USER_WARNING);
                        continue;
                    }

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
}

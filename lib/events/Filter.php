<?php
class Filter
{
    public static $FilterSections;

    static function register($filter, $callback, $priority = 100)
    {
        if (!isset(self::$FilterSections[$filter]['handler'])) {
            if (!isset(self::$FilterSections))
                self::$FilterSections = array();
            if (is_array($callback)) {
                if (is_string($callback[0]))
                    $id = hash('md5', $callback[0] . $callback[1] . $priority);
                else
                    $id = hash('md5', get_class($callback[0]) . $callback[1] . $priority);
            } else
                $id = hash('md5', $callback . $priority);
            self::$FilterSections[$filter][$priority][$id] = $callback;
        } else {
            $handler = self::$FilterSections[$filter]['handler'];
            call_user_func($handler, $filter, $callback, $priority);
        }
    }

    static function registerHandler($filter, $callback)
    {
        self::$FilterSections[$filter]['handler'] = $callback;
    }

    static function run($filter, $params = array())
    {
        $value = $params[0];
        $priorities = array_key_exists_v($filter, self::$FilterSections);
        if ($priorities)
            ksort($priorities);
        if ($priorities)
            ksort($priorities);
        if (is_array($priorities)) {
            if (!is_array($params))
                $params = array($params);
            foreach ($priorities as $functions)

                foreach ($functions as $function) {
                    if(!is_callable($function)){
                                            if(is_array($function))
                                                if(is_string($function[0]))
                                                    $message=implode('::',$function);
                                                else
                                                    $message=get_class($function[0]).'->'.$function[1];
                                            else
                                                $message=$function;
                                            trigger_error('Filter cannot call '.$message.' it does not exist.',E_USER_WARNING);
                        continue;
                                        }

                    $value = call_user_func_array($function, $params);
                    $params[0] = $value;
                }
        }
        return $value;
    }

    static function isRegistered($filter)
    {
        return array_key_exists($filter, self::$FilterSections);
    }

    static function hasHandler($filter)
    {
        return isset(self::$FilterSections[$filter]['handler']);
    }
}

/*
 * deprecated since 11.6
 */
class FilterHelper
{
    static function registerFilter($filter, $callback, $priority = 100)
    {
        Filter::register($filter, $callback, $priority);
    }

    static function registerCustomHandler($filter, $callback)
    {
        Filter::registerHandler($filter, $callback);
    }

    static function runFilter($filter, $params = array())
    {
        return Filter::run($filter, $params);
    }

    static function run($filter, $params = array())
    {
        return Filter::run($filter, $params);
    }

    static function isRegistered($filter)
    {
        return Filter::isRegistered($filter);
    }

    static function hasCustomHandler($filter)
    {
        return Filter::hasHandler($filter);
    }
}
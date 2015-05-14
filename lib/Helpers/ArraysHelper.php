<?php

/**
 * Checks if an array has a specific value.
 *
 * @param string $key
 * @param $value
 * @param $haystack
 *
 * @return bool
 */
function array_key_has_value($key, $value, $haystack)
{
    foreach ($haystack as $k => $val) {
        if ($key == $k) {
            if (is_array($value)) {
                return in_array($val, $value);
            } else {
                return $val == $value;
            }
        }
    }

    return false;
}

/**
 * Checks if an array has a needle and if so returns its value.
 *
 * @param string $needle
 * @param array $haystack
 * @param mixed $default
 *
 * @return bool|mixed
 */
function array_key_exists_v($needle, $haystack, $default = false)
{
    if (!empty($haystack)) {
        foreach ($haystack as $key => $value) {
            if ($needle === $key) {
                return $value;
            }
        }
    }

    return $default;
}

/**
 * Searches an array to see if keys matching the search string exists if so returns all matching key value pairs.
 *
 * @param string $search
 * @param array $haystack
 *
 * @return array
 */
function array_search_key($search, $haystack)
{
    $array = array();
    foreach ($haystack as $key => $value) {
        $sub = stristr($key, $search);
        if ($sub) {
            $array[$key] = $value;
        }
    }

    return $array;
}

/**
 * Searches an array to see if prefix matching the search string exists if so returns all matching key value pairs with the prefix removed.
 *
 * @param string $prefix
 * @param array $haystack
 *
 * @return array
 */
function array_search_prefix($prefix, $haystack)
{
    $array = array();
    foreach ($haystack as $key => $value) {
        if (substr($key, 0, strlen($prefix)) === $prefix) {
            $array[substr($key, strlen($prefix))] = $value;
        }
    }

    return $array;
}

/**
 * Searches an array to see if values matching the search string exists if so returns all matching keys.
 *
 * @param string $search
 * @param array $haystack
 *
 * @return array
 */
function array_search_keys_value($search, $haystack)
{
    $array = array();
    foreach ($haystack as $key => $value) {
        $sub = strstr($value, $search);
        if ($sub) {
            $array[] = $key;
        }
    }

    return $array;
}

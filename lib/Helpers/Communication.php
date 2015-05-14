<?php

namespace CLMVC\Helpers;

/**
 * Class Communication.
 */
class Communication
{
    /**
     * Remove query string and other stuff from an url.
     *
     * @param $dirty_url
     *
     * @return string
     */
    public static function cleanUrl($dirty_url)
    {
        list($clean_url) = explode('?', htmlspecialchars(strip_tags($dirty_url), ENT_NOQUOTES));

        return $clean_url;
    }

    /**
     * Retrieves the request method. Checks for post key '_method'.
     *
     * @return string
     */
    public static function getMethod()
    {
        $tempMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get';
        if (strcasecmp($tempMethod, 'put') == 0) {
            return 'put';
        } elseif (strcasecmp($tempMethod, 'post') == 0) {
            if (isset($_POST['_method'])) {
                if (strcasecmp($_POST['_method'], 'put') == 0) {
                    return 'put';
                }
                if (strcasecmp($_POST['_method'], 'delete') == 0) {
                    return 'delete';
                }
            }

            return 'post';
        } elseif (strcasecmp($tempMethod, 'get') == 0) {
            return 'get';
        } elseif (strcasecmp($tempMethod, 'delete') == 0) {
            return 'delete';
        }

        return $tempMethod;
    }

    /**
     * Checks if query string has key value pair.
     *
     * @param string     $key
     * @param string|int $value
     *
     * @return bool
     */
    public static function QueryStringEquals($key, $value)
    {
        return array_key_has_value($key, $value, self::getQueryString());
    }

    /**
     * Returns the query string.
     *
     * @param string $key
     * @param string $default
     *
     * @return array
     */
    public static function getQueryString($key = null, $default = null)
    {
        global $wp_query;
        if (isset($wp_query) && !empty($wp_query->query_vars)) {
            $qs = $wp_query->query_vars;
        } else {
            $qs = $_GET;
        }
        if ($key) {
            $qs = array_key_exists_v($key, $qs, $default);
        }

        return $qs;
    }

    /**
     * Returns form values matching keys.
     *
     * @param array $keys
     * @param null  $data
     *
     * @return array
     */
    public static function getFormValues($keys = array(), $data = null)
    {
        $qs = $data ? $data : $_POST;
        if (!empty($keys) && is_array($keys)) {
            $values = array_intersect_key($qs, $keys);

            return $values;
        } elseif (is_string($keys)) {
            $data = array();
            foreach ($qs as $key => $value) {
                if (substr($key, 0, strlen($keys)) === $keys) {
                    $data[substr($key, strlen($keys))] = $value;
                }
            }

            return $data;
        }

        return $qs;
    }

    public static function getFormValue($key, $data = null)
    {
        $qs = $data ? $data : $_POST;
        if (isset($qs[$key])) {
            return $qs[$key];
        }

        return;
    }

    /**
     * Get upload contents from $_FILES matching keys.
     *
     * @param $keys
     *
     * @return array
     */
    public static function getUpload($keys)
    {
        $files = array_intersect_key($_FILES, $keys);

        return $files;
    }

    /**
     * Retrieve the referrer.
     *
     * @return mixed
     */
    public static function getReferer()
    {
        if (function_exists('wp_get_referer')) {
            return wp_get_referer();
        } else {
            return $_SERVER['HTTP_REFERER'];
        }
    }

    /**
     * Redirect to url with data.
     *
     * @param string            $url
     * @param string|array|bool $data
     */
    public static function redirectTo($url, $data = null)
    {
        $data = ltrim($data, '&');
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        if (strpos($url, '?') === false) {
            $redirect = $url.'?'.$data;
        } else {
            $redirect = $url.'&'.$data;
        }
        if (function_exists('wp_redirect')) {
            wp_redirect($redirect);

            return;
        }
        header('Location: '.$redirect);
        exit;
    }

    /**
     * Check if redirect should be used and if so return the redirect url.
     *
     * @return bool|mixed
     */
    public static function useRedirect()
    {
        return array_key_exists_v('_redirect', $_POST);
    }

    /**
     * See if a query string is in array.
     *
     * @param $string
     * @param $array
     *
     * @return bool
     */
    public static function queryStringIn($string, $array)
    {
        return in_array(self::getQueryString($string), $array);
    }
}

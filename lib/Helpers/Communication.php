<?php
namespace CLMVC\Helpers;

use CLMVC\Core\Data\Repo;
use CLMVC\Core\Debug;

/**
 * Class Communication
 */
class Communication
{
    /**
     * Remove query string and other stuff from an url
     * @param $dirty_url
     * @return string
     */
    static function cleanUrl($dirty_url)
    {
        list($clean_url) = explode('?', htmlspecialchars(strip_tags($dirty_url), ENT_NOQUOTES));
        return $clean_url;
    }

    /**
     * Retrieves the request method. Checks for post key '_method'
     * @return string
     */
    static function getMethod()
    {
        $tempMethod = $_SERVER['REQUEST_METHOD'];
        if (strcasecmp($tempMethod, 'put') == 0)
            return 'put';
        else if (strcasecmp($tempMethod, 'post') == 0) {
            if (isset($_POST['_method'])) {
                if (strcasecmp($_POST['_method'], 'put') == 0)
                    return 'put';
                if (strcasecmp($_POST['_method'], 'delete') == 0)
                    return 'delete';
            }
            return 'post';
        } else if (strcasecmp($tempMethod, 'get') == 0)
            return 'get';
        else if (strcasecmp($tempMethod, 'delete') == 0)
            return 'delete';
        return $tempMethod;
    }

    /**
     * Checks if query string has key value pair
     * @param string $key
     * @param string|int $value
     * @return bool
     */
    static function QueryStringEquals($key, $value)
    {
        return array_key_has_value($key, $value, self::getQueryString());
    }

    /**
     * Returns the query string
     * @param string $key
     * @param string $default
     * @return array
     */
    static function getQueryString($key = null, $default = null)
    {
        global $wp_query;
        if (isset($wp_query) && !empty($wp_query->query_vars))
            $qs = $wp_query->query_vars;
        else
            $qs = $_GET;
        if ($key)
            $qs = array_key_exists_v($key, $qs, $default);
        return $qs;
    }

    /**
     * Returns form values matching keys
     * @param array $keys
     * @param null $data
     * @return array
     */
    static function getFormValues($keys = array(), $data = null)
    {
        $qs = $data ? $data : $_POST;
        if ($keys && is_array($keys)) {
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

    static function getFormValue($key, $data = null)
    {
        $qs = $data ? $data : $_POST;
        if (isset($qs[$key]))
            return $qs[$key];
        return null;
    }

    /**
     * Get upload contents from $_FILES matching keys
     * @param $keys
     * @return array
     */
    static function getUpload($keys)
    {
        $files = array_intersect_key($_FILES, $keys);
        return $files;
    }

    /**
     * Retrieve the referrer
     * @return mixed
     */
    static function getReferer()
    {
        if (function_exists('wp_get_referer'))
            return wp_get_referer();
        else
            return $_SERVER['HTTP_REFERER'];
    }

    /**
     * Redirect to url with data
     * @param string $url
     * @param string|array|bool $data
     */
    static function redirectTo($url, $data = null)
    {
        $data = ltrim($data, "&");
        if (is_array($data))
            $data = http_build_query($data);
        if (strpos($url, '?') === false)
            $redirect = $url . "?" . $data;
        else
            $redirect = $url . "&" . $data;
        if (function_exists('wp_redirect')) {
            wp_redirect($redirect);
            return;
        }
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Check if redirect should be used and if so return the redirect url
     * @return bool|mixed
     */
    static function useRedirect()
    {
        return array_key_exists_v('_redirect', $_POST);
    }
    //TODO work in progress
    /**
     * Loads an object with properties matching the $_POST data
     * @param $class
     * @param bool $uploadSubFolder
     * @param $thumbnails
     * @param int $width
     * @param int $height
     * @return mixed
     */
    static function loadFromPost($class, $uploadSubFolder = false, $thumbnails, $width = 100, $height = 100)
    {
        if (is_string($class))
            $crudItem = new $class();
        else
            $crudItem = $class;
        $folder = '';
        if ($uploadSubFolder)
            $folder = stripslashes($uploadSubFolder) . '/';

        $properties = ObjectUtility::getPropertiesAndValues($crudItem);
        Debug::Message('LoadFromPost');
        //		Debug::Value('Uploaded',Communication::getUpload($properties));
        $propertyFormValues = Communication::getFormValues($properties);
        $propertyFormValues = array_map('stripslashes', $propertyFormValues);
        Debug::Value('Loaded properties/values for ' . get_class($crudItem), $propertyFormValues);
        $lists = array_search_key('_list', $propertyFormValues);
        Debug::Value('Loaded listvalues from post', $lists);
        ObjectUtility::setProperties($crudItem, $propertyFormValues);
        foreach ($lists as $method => $value) {
            Debug::Value($method, $value);
            $settings = ObjectUtility::getCommentDecoration($crudItem, str_ireplace("_list", "", $method) . 'List');
            $dbrelation = array_key_exists_v('dbrelation', $settings);
            Debug::Value($method, $dbrelation);
            $field = array_key_exists_v('field', $settings);
            $objects = array();
            if ($field == 'text') {
                $propertyFormValues = explode(',', trim($value, " ,."));
                if (sizeof($propertyFormValues) == 0)
                    continue;
                foreach ($propertyFormValues as $value2) {
                    if ($dbrelation && $field == 'text') {
                        $object = new $dbrelation;
                        $object->setName(trim($value2));
                        $object->save();
                        $objects[] = $object;
                    }
                }
            } else if ($dbrelation) {
                $value = is_array($value) ? $value :array($value);
                foreach ($value as $val) {
                    $object = Repo::getById($dbrelation, $val);
                    $objects[] = $object;
                }
            }

            ObjectUtility::addToArray($crudItem, str_ireplace("_list", "", $method), $objects);
        }
        return $crudItem;
    }

    /**
     * See if a query string is in array
     * @param $string
     * @param $array
     * @return bool
     */
    public static function queryStringIn($string, $array)
    {
        return in_array(self::getQueryString($string), $array);
    }
}

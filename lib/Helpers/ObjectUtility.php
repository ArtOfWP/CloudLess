<?php

namespace CLMVC\Helpers;

use CLMVC\Core\Debug;
use ReflectionMethod;
use ReflectionClass;

/**
 * Class ObjectUtility.
 */
class ObjectUtility
{
    /**
     * Retrieve properties from object.
     *
     * @param $object
     *
     * @return array
     */
    public static function getProperties($object)
    {
        $class = new ReflectionClass(get_class($object));
        $class->getDocComment();
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $properties = array();
        Debug::Value('Class', get_class($object));
        foreach ($methods as $method) {
            $name = $method->name;
            if (strpos($name, '__') === false && strpos($name, 'get') !== false && !$method->isStatic()) {
                $properties[] = substr($name, 3);
            }
        }

        return $properties;
    }

    /**
     * Retrieve array based properties. They end in list.
     *
     * @param $object
     *
     * @return array
     */
    public static function getArrayProperties($object)
    {
        $class = new ReflectionClass(get_class($object));
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $properties = array();
        foreach ($methods as $method) {
            if (strpos($method->name, 'List') !== false && !$method->isStatic()) {
                $property = str_replace('List', '', $method->name);
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * Retrive the array properties and their values.
     *
     * @param \CLMVC\Core\Data\ActiveRecordBase $object
     *
     * @return array
     */
    public static function getArrayPropertiesAndValues($object)
    {
        $class = new ReflectionClass(get_class($object));
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $properties = array();
        foreach ($methods as $method) {
            if (strpos($method->name, 'List') !== false) {
                $properties[str_replace('List', '', $method->name)] = $method->invoke($object);
            }
        }

        return $properties;
    }

    /**
     * Add values to array property. Method has 'add' prefix.
     *
     * @param $object
     * @param $method
     * @param $values
     */
    public static function addToArray($object, $method, $values)
    {
        Debug::Message('AddToArrayMethod');
        Debug::Value($method, $values);
        $method = new ReflectionMethod(get_class($object), 'add'.$method);
        foreach ($values as $value) {
            $method->invoke($object, $value);
        }
    }

    /**
     * Return the properties and their values.
     *
     * @param $object
     *
     * @return array key is property, value is the property value
     */
    public static function getPropertiesAndValues($object)
    {
        $class = new ReflectionClass(get_class($object));
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $properties = array();
        foreach ($methods as $method) {
            $name = $method->name;
            if (strpos($name, '__') === false && strpos($name, 'get') !== false && !$method->isStatic()) {
                $properties[substr($name, 3)] = $method->invoke($object);
            }
        }

        return $properties;
    }

    /**
     * Set properties values.
     *
     * @param $object
     * @param array $values key value pair. Key is property, value is property value
     */
    public static function setProperties($object, $values)
    {
        foreach ($values as $property => $value) {
            if (method_exists($object, 'set'.$property)) {
                $method = new ReflectionMethod(get_class($object), 'set'.$property);
                $method->invoke($object, $value);
            } else {
                $object->$property = $value;
            }
        }
    }

    /**
     * Get the class decoration.
     *
     * @param $object
     *
     * @return array
     */
    public static function getClassCommentDecoration($object)
    {
        $class = new ReflectionClass(get_class($object));
        $comment = $class->getDocComment();
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $settings = array();
        if (strlen($comment) > 4) {
            $temp = explode(',', $comment);
            foreach ($temp as $setting) {
                $x = explode(':', trim($setting));
                $settings[$x[0]] = $x[1];
            }
        }

        return $settings;
    }

    /**
     * Get method comment decoration.
     *
     * @param $object
     * @param $method
     *
     * @return array
     */
    public static function getCommentDecoration($object, $method)
    {
        $rmethod = new ReflectionMethod(get_class($object), $method);
        $comment = $rmethod->getDocComment();
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $settings = array();
        if (strlen($comment) > 4) {
            $temp = explode(',', $comment);
            foreach ($temp as $setting) {
                $x = explode(':', trim($setting));
                $settings[$x[0]] = isset($x[1]) ? $x[1] : true;
            }
        }

        return $settings;
    }
}

<?php

namespace CLMVC\Core;

/**
 * Class Container
 * This is a Inversion of Control Container.
 */
class Container
{
    private $values;
    private static $instance;

    /**
     * Create a container.
     */
    public function __construct()
    {
        $this->values = array();
    }

    /**
     * Add an object to be handle by container.
     *
     * @param string $key    unique value for object
     * @param mixed $object
     * @param string $type
     *
     * @throws \InvalidArgumentException thrown if key is not unique, i.e already added
     */
    public function add($key, $object, $type = 'object')
    {
        if (isset($this->values[strtolower($key)])) {
            throw new \InvalidArgumentException('The key is not unique');
        }
        $this->values[strtolower($key)] = array($object,$type);
    }

    /**
     * Remove an object.
     *
     * @param string $key
     */
    public function remove($key)
    {
        if (isset($this->values[strtolower($key)])) {
            unset($this->values[strtolower($key)]);
        }
    }

    /**
     * Check if key has been added.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->values[strtolower($key)]);
    }

    /**
     * Fetch an object.
     *
     * @param $key
     *
     * @return bool|mixed
     */
    public function fetch($key)
    {
        $tuple = $this->fetchTuple($key);

        return is_array($tuple) ? array_shift($tuple) : $tuple;
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    private function fetchTuple($key)
    {
        return array_key_exists_v(strtolower($key), $this->values);
    }

    /**
     * Make an object of type of added key.
     *
     * @param string $key
     * @param array  $params
     *
     * @return object
     *
     * @throws \InvalidArgumentException throws if the key does not exist in container
     */
    public function make($key, $params = array())
    {
        $className = $this->getClassName($key);
        $class = new \ReflectionClass($className);
        $class_constructor = $class->getConstructor();
        if ($class_constructor && $class_constructor->getNumberOfParameters()) {
            $invokeParams = $this->getInvokeParameters($class_constructor);
            $obj = $class->newInstanceArgs(array_merge($params, $invokeParams));
        } else {
            $obj = new $className();
        }

        return $obj;
    }

    /**
     * Instantiate a Container.
     *
     * @static
     *
     * @return Container
     */
    public static function instance()
    {
        if (!isset(self::$instance) && empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $key
     * @return string
     */
    private function getClassName($key)
    {
        $class = $this->fetchTuple($key);
        if ('object' == $class[1]) {
            $className = get_class($class[0]);
        } elseif ('class' == $class[1]) {
            $className = $class[0];
        } else {
            $className = $key;
        }
        return $className;
    }

    /**
     * @param $class_constructor
     * @return array
     */
    private function getInvokeParameters($class_constructor)
    {
        $methodParams = $class_constructor->getParameters();
        $invokeParams = array();
        foreach ($methodParams as $mParam) {
            $pValue = $this->fetchTuple($mParam->getName());
            if (!$pValue) {
                $param_class = $mParam->getClass();
                if ($param_class) {
                    $pValue = $this->fetchTuple($param_class->getName());
                } else {
                    continue;
                }
            }
            $invokeParams[] = $this->getInvokeParam($pValue);
        }
        return $invokeParams;
    }

    /**
     * @param $pValue
     * @return array
     */
    private function getInvokeParam($pValue)
    {
        if ('class' == $pValue[1]) {
            $value = $this->make($pValue[0]);
        } else {
            $value = $pValue[0];
        }
        return $value;
    }

}

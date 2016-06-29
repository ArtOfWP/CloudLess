<?php
namespace CLMVC\Core;

/**
 * Class Container
 * This is a Inversion of Control Container.
 */
class Container
{
    private static $instance;
    private $values;

    /**
     * Create a container.
     */
    public function __construct()
    {
        $this->values = [];
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
     * Remove an object.
     *
     * @param string $key
     */
    public function remove($key)
    {
        if (array_key_exists(strtolower($key), $this->values)) {
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
        return array_key_exists(strtolower($key), $this->values);
    }

    /**
     * Fetches based on the class name if it exits or tries to instantiate based on it.
     * @param string $className
     * @param array $params
     * @return mixed|object
     */
    public function fetchOrMake($className, $params = [])
    {
        $obj = $this->fetch($className);
        if ($obj) {
            return $obj;
        }
        $obj = $this->make($className, $params);
        $this->add($className, $obj);
        return $obj;
    }

    /**
     * Fetch an object.
     *
     * @param $key
     *
     * @return mixed|null Returns null if not found.
     */
    public function fetch($key)
    {
        $tuple = $this->fetchTuple($key);

        return is_array($tuple) ? array_shift($tuple) : $tuple;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    private function fetchTuple($key)
    {
        return array_key_exists_v(strtolower($key), $this->values, null);
    }

    /**
     * Make an object of type of added key.
     *
     * @param string $key
     * @param array $params
     *
     * @return object
     */
    public function make($key, $params = [])
    {
        $className = $this->getClassName($key);
        $class = new \ReflectionClass($className);

        if (!$class->isInterface()) {
            $class_constructor = $class->getConstructor();
            if ($class_constructor && $class_constructor->getNumberOfParameters()) {
                $invokeParams = $this->getInvokeParameters($class_constructor);
                $args = $params + $invokeParams;
                $constructor_params = [];
                for ($i=0; $i<sizeof($args); $i++) {
                    $constructor_params[] = $args[$i];
                }
                return $class->newInstanceArgs($constructor_params);
            } else {
                return $class->newInstanceWithoutConstructor();
            }
        } else {
            return $this->fetch($key);
        }
    }

    /**
     * @param $key
     * @return string
     */
    private function getClassName($key)
    {
        $class = $this->fetchTuple($key);
        if ('object' === $class[1]) {
            $className = get_class($class[0]);
        } elseif ('class' === $class[1]) {
            $className = $class[0];
        } else {
            $className = $key;
        }
        return $className;
    }

    /**
     * @param \ReflectionMethod $class_constructor
     * @return array
     */
    private function getInvokeParameters($class_constructor)
    {
        $methodParams = $class_constructor->getParameters();
        $invokeParams = [];
        foreach ($methodParams as $mParam) {
            $class = $mParam->getClass();
            if ($class) {
                $name = $class->getName();
            } else {
                $name = $mParam->getName();
            }
            $pValue = $this->fetchTuple($name);
            if (!$pValue) {
                $param_class = $mParam->getClass();
                if ($param_class) {
                    $name = '\\' . $param_class->getName();
                    $pValue = $this->make($name);
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
        if (!is_array($pValue)) {
            return $pValue;
        }
        if ('class' === $pValue[1]) {
            return $this->make($pValue[0]);
        }
        return $pValue[0];
    }

    /**
     * Add an object to be handle by container.
     *
     * @param string $key unique value for object
     * @param mixed $object
     * @param string $type
     *
     * @throws \InvalidArgumentException thrown if key is not unique, i.e already added
     */
    public function add($key, $object, $type = 'object')
    {
        if (array_key_exists(strtolower($key), $this->values)) {
            throw new \InvalidArgumentException('The key is not unique');
        }
        $this->values[strtolower($key)] = [$object, $type];
    }
}

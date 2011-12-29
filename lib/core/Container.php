<?php
/**
 * User: andreas
 * Date: 2011-12-21
 * Time: 16:14
 */
class Container
{
    private $values;
    private static $instance;
    function __construct()
    {
        $this->values = array();
    }

    public function add($key, $object,$type='object')
    {
        $this->values[strtolower($key)] = array($object,$type);
    }

    public function fetch($key)
    {
        $tuple= $this->fetchTuple($key);

        return is_array($tuple)?array_shift($tuple):$tuple;
    }
    private function fetchTuple($key){
        return array_key_exists_v(strtolower($key),$this->values);
    }
    public function make($key,$params=array())
    {
        $class = $this->fetchTuple($key);
        if ('object'==$class[1]) {
            $className = get_class($class[0]);
        } else if('class'==$class[1])
            $className = $class[0];
        else
            $className = $key;
        $rclass = new ReflectionClass($className);
        $rclassCstr = $rclass->getConstructor();
        if ($rclassCstr) {
            if ($rclassCstr->getNumberOfParameters()) {
                $methodParams = $rclassCstr->getParameters();
                $invokeParams = array();
                foreach ($methodParams as $mParam) {
                    $pValue = $this->fetchTuple($mParam->getName());
                    if(!$pValue)
                        if($mParam->getClass())
                            $pValue=$this->fetchTuple($mParam->getClass()->getName());
                        else
                            continue;
                    if ($pValue[1]=='object') {
                        $invokeParams[] = $pValue[0];
                    }else if($pValue[1]=='class'){
                        $invokeParams[]=$this->make($pValue[0]);
                    }else
                        $invokeParams[] = $pValue[0];
                }
                $obj = $rclass->newInstanceArgs($invokeParams);

            } else
                $obj = new $className();
        }else
            $obj = new $className();
        return $obj;
    }
    /**
     * @static
     * @return Container
     */
    public static function instance(){
        if(!isset(self::$instance) && empty(self::$instance))
            self::$instance=new Container();
        return self::$instance;
    }
}
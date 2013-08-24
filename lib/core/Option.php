<?php

/**
 * Class Option
 *
 */
class Option{
    private $key;
    private $value;
    private $defaultValue;
    private $type;

    /**
     * @param $name
     * @return WpOption
     */
    static function create($name){
        return ViewEngine::createOption($name);
    }

    /**
     * @param bool $key
     * @param bool $value
     * @param string $type
     */
    public function __construct($key=false,$value=false,$type='string'){
        if($key){
            $this->setKey($key);
            $this->setDefaultValue($value);
            $this->type=$type;
        }
    }

    /**
     * @param $key
     */
    public function setKey($key)
    {
        $this->key=$key;
    }

    /**
     * @param $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        if(!isset($this->value))
            $this->value=$defaultValue;
        $this->defaultValue=$defaultValue;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value=$value;
    }

    /**
     * @param $property
     * @param $value
     */
    function __set($property,$value){
        $this->$property=$value;
    }

    /**
     * @param $property
     * @return mixed
     */
    function __get($property) {
        return $this->$property;
    }

    /**
     * @return $this
     */
    function reset() {
        $this->setValue($this->getDefaultValue());
        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type) {
        $this->type = $type;
    }
}
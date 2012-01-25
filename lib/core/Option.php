<?php
class Option{
    private $key;
    private $value;
    private $defaultValue;
    private $type;
    static function create($name){
        return ViewEngine::createOption($name);
    }
    public function __construct($key=false,$value=false,$type='string'){
        if($key){
            $this->setKey($key);
            $this->setDefaultValue($value);
            $this->type=$type;
        }
    }
    public function setKey($key)
    {
        $this->key=$key;
    }

    public function setDefaultValue($defaultValue)
    {
        if(!isset($this->value))
            $this->value=$defaultValue;
        $this->defaultValue=$defaultValue;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setValue($value)
    {
        $this->value=$value;
    }
    function __set($property,$value){
        $this->$property=$value;
    }
    function __get($property){
        return $this->$property;
    }
    function reset(){
        $this->setValue($this->getDefaultValue());
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}
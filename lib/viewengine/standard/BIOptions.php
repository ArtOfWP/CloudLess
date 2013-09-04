<?php
use CLMVC\Interfaces\IOptions;

class BIOptions implements IOptions
{
    /**
     * @var $pairs Option[]
     */
    private $pairs=array();
    /**
     * @var $namespace string
     */
    private $namespace;
    public function __construct($namespace){
        $this->namespace=$namespace;
    }
    public function setValue($key, $value){
        $exists=false;
        if(!isset($this->pairs[$key])){
            $this->pairs[$key]=new Option($key,$value);
            $exists= true;
        }
        return $exists;
    }

    public function getValue($key){
        /**
         * @var Option $option
         */
        if(!isset($this->pairs[$key]))
            return false;
        $option=$this->pairs[$key];
        return $option->getValue();
    }

    public function exists($key)
    {
        return isset($this->pairs[$key]) && !empty($this->pairs[$key]);
    }

    public function delete($key)
    {
        $exists=false;
        if(isset($this->pairs[$key])){
            unset($this->pairs[$key]);
            $exists=true;
        }
        return $exists;
    }

    public function reset()
    {
        /**
         * @var $key string
         * @var $option Option
         */
        foreach($this->pairs as $key => $option){
            $this->pairs[$key]=$option->reset();
        }
    }

    public function add(Option $option)
    {
        if(!isset($this->pairs[$option->getKey()])){
            $this->pairs[$option->getKey()]=$option;
            return true;
        }
        return false;
    }

    public function updateValue($key, $value)
    {
       if(isset($this->pairs[$key])){
           $this->pairs[$key]->setValue($value);
           return true;
       }
        return false;

    }
    public function load($namespace){
    }

    public function save($namespace,$options)
    {
        // TODO: Implement save() method.
    }
}

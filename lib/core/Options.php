<?php
/**
 * User: andreas
 * Date: 2011-12-30
 * Time: 15:13
 */
class Options
{
    /**
     * @var $pairs Option[]
     */
    private $pairs=array();
    /**
     * @var $namespace string
     */
    private $namespace;
    private $ioptions;
    private $initialized=false;
    public function __construct($namespace,IOptions $ioptions=NULL){
        $this->namespace=$namespace;
        if($ioptions)
            $this->ioptions=$ioptions;
        else
            $this->ioptions=Container::instance()->make('IOptions',array($namespace));
    }
    public function add(Option $option)
    {
        if($this->initialized){
            $old=$this->pairs[$option->getKey()];
            $option->setValue($old->getValue());
            $this->pairs[$option->getKey()]=$option;
        }else
            $this->pairs[$option->getKey()]=$option;
    }
    public function get($key)
    {
        /**
        * @var Option $option
        */
        if(!isset($this->pairs[$key]))
            return false;
        return $this->pairs[$key];
    }
    public function setValue($key, $value)
    {
        $exists=false;
        if(!isset($this->pairs[$key])){
            $this->pairs[$key]=new Option($key,$value);
            $exists= true;
        }
        return $exists;
    }

    public function getValue($key)
    {
        if(!isset($this->pairs[$key]))
            return false;
        return $this->pairs[$key]->getValue();
    }

    public function updateValue($key, $value)
    {
        if(isset($this->pairs[$key])){
            $this->pairs[$key]->setValue($value);
            return true;
        }
         return false;
    }

    public function exists($key)
    {
        return isset($this->pairs[$key]);
    }

    public function remove($key)
    {
        $exists=false;
        if(isset($this->pairs[$key])){
            unset($this->pairs[$key]);
            $exists=true;
        }
        return $exists;
    }
    public function delete(){
        $this->ioptions->delete($this->namespace);
        unset($this->pairs);
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
    public function save(){
        $this->init();
        $options=array();
        /**
         * @var Option $option
         */
        foreach($this->pairs as $option)
            $options[$option->getKey()]=$option->getValue();
        $this->ioptions->save($this->namespace,$options);
    }
    public function init(){
        if(!$this->initialized){
            $options=$this->ioptions->load($this->namespace);
            if(!empty($options))
            foreach($options as $key => $value){
                if(isset($this->pairs[$key]))
                    $this->pairs[$key]->setValue($value);
                else
                    $this->pairs[$key]=new Option($key,$value);
            }
            $this->initialized=true;
        }
    }
    public function __get($key){
        if(isset($this->pairs[$key]))
            return $this->pairs[$key]->getValue();
        else
            trigger_error("$key key does not exist",E_USER_WARNING );
    }
    public function __set($key,$value){

    }
    /**
     * @deprecated
     */
    public function isEmpty(){
        return sizeof($this->pairs)==0;
    }
    public function getArray(){
        $options=array();
        foreach($this->pairs as $key => $option)
            $options[$key]=$option->getValue();
        return $options;
    }
}

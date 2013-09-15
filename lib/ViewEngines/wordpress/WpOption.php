<?php
namespace CLMVC\ViewEngines\WordPress;
use CLMVC\Core\Debug;
use CLMVC\Interfaces\IOption;

/**
 * Class WpOption
 */
class WpOption implements IOption{
	private $name;
	private $options=array();
	private $initiated=false;

    /**
     */
    function WpOption(){
		$this->init();
	}

    /**
     *
     */
    function init(){
		$this->options=get_option($this->name);
        $this->initiated = true;
		if($this->isEmpty())
			$this->options=array();	
	}

    /**
     * @return bool
     */
    function isEmpty(){
		return empty($this->options);
	}

    /**
     *
     */
    function save(){
		Debug::Value('Saving options',$this->name);
		if(get_option($this->name))
			update_option($this->name,$this->options);
		else
			add_option($this->name,$this->options);			
	}

    /**
     *
     */
    function delete(){
		delete_option($this->name);
	}

    /**
     * @param $option
     * @return null
     */
    public function __get($option){
		if(isset($this->options[$option]))
		return $this->options[$option];
		return NULL;
	}

    /**
     * @param $option
     * @param $value
     */
    public function __set($option,$value){
		$this->options[$option]=$value;
	}

    /**
     * @param $option
     * @return bool
     */
    public function __isset($option){
		return isset($this->options[$option]);
	}

    /**
     * @return array
     */
    public function getArray(){
		return $this->options;
	}

    /**
     * @return string
     */
    public function __ToString(){
		return "Options for $this->name";
	}
}
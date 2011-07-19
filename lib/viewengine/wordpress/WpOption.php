<?php
class WpOption implements IOption{
	private $name;
	private $options=array();
	private $initiated=false;
	function WpOption($name){
		$this->name=$name;
		$this->init();
	}
	function init(){
		$this->options=get_option($this->name);
		if($this->isEmpty())
			$this->options=array();	
	}
	function isEmpty(){
		return empty($this->options);
	}
	function save(){
		Debug::Value('Saving options',$this->name);
		if(get_option($this->name))
			update_option($this->name,$this->options);
		else
			add_option($this->name,$this->options);			
	}
	function delete(){
		delete_option($this->name);
	}
	public function __get($option){
		if(isset($this->options[$option]))
		return $this->options[$option];
		return NULL;
	}
	public function __set($option,$value){
		$this->options[$option]=$value;
	}
	public function __isset($option){
		return isset($this->options[$option]);
	}
	public function getArray(){
		return $this->options;
	}
	public function __ToString(){
		return "Options for $this->name";
	}
}
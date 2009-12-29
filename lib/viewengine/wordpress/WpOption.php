<?php
class WpOption{
	private $application;
	private $options=array();
	function WpOption($application){
		$this->application=$application;
		$this->options=get_option($application);
		if($this->isEmpty()){
			$this->options=array();
			$this->save();
		}
	}
	function isEmpty(){
		if(!isset($this->options) || empty($this->options) || $this->options==NULL)
			return true;
		return false;
	}
	function save(){
		Debug::Value('Saving options',$this->application);
		Debug::Value('Options',$this->options);
		update_option($this->application,$this->options);
	}
	function delete(){
		delete_option($this->application);
	}
	public function __get($option){
//		if(isset($this->options[$option]))
			return $this->options[$option];
//		die("WpOptions: There is no option with key: $option in the options for $this->application");
	}
	public function __set($option,$value){
		$this->options[$option]=$value;
	}
	public function getArray(){
		return $this->options;
	}
}
?>
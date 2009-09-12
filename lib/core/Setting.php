<?php
class Setting extends ActiveRecordBase{
	private $id;
	private $application;
	private $key;
	private $value;
	
	function getId(){
		return $this->id;
	}
	function getApplication(){
		return $this->application;
	}
	function getKey(){
		return $this->key;
	}
	function getValue(){
		return $this->value;
	}
	function setId($id){
		$this->id=$id;
	}
	function setApplication($application){
		$this->application=$application;
	}
	function setKey($key){
		$this->key=$key;
	}
	function setValue($value){
		$this->value=$value;
	}
}
?>